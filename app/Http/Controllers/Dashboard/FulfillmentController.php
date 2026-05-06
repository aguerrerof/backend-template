<?php

namespace App\Http\Controllers\Dashboard;

use App\Events\FulfillmentCreated;
use App\Exceptions\FulfillmentAlreadyAssignedException;
use App\Helpers\CustomLog;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateFulfillmentRequest;
use App\Http\Requests\UpdateFulfillmentRequest;
use App\Models\Domain\ProductIds;
use App\Models\Enums\FulfillmentStatus;
use App\Models\Fulfillment;
use App\Models\Order;
use App\Services\Logistics\FulfillmentCreationService;
use App\Services\Logistics\FulfillmentShipmentService;
use App\Services\Logistics\LogisticProviderResolver;
use App\Services\Logistics\Providers\LAARProvider;
use App\Services\Shop\ShopService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

class FulfillmentController extends Controller
{
    public function __construct(
        private readonly ShopService $shopService,
        private readonly FulfillmentShipmentService $shipmentService,
        private readonly FulfillmentCreationService $fulfillmentCreationService,
    ) {
    }

    public function create(int $orderId): View
    {
        $order = Order::query()->with('card')->findOrFail($orderId);
        $productIdsArray = array_unique(
            array_map(fn ($item) => (string)$item->product_id, $order->order->line_items),
        );
        $productImages = $this->shopService->getProductsImages(new ProductIds(...$productIdsArray));
        $lineItems = $order->getLineItems();

        $shippingCity = $order->order->shipping_address->city ?? null;
        $factory = view('fulfillment.create', [
            'order' => $order,
            'productImages' => $productImages,
            'lineItems' => $lineItems,
            'shippingCity' => $shippingCity,
            'canSave' => $this->existsPendingFulfillment($order->id),
        ]);
        if ($this->existsPendingFulfillment($order->id)) {
            $factory->withErrors([
                'fulfillment' => __('custom.fulfillment_already_assigned'),
            ]);
        }
        return $factory;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateFulfillmentRequest $request): RedirectResponse|JsonResponse
    {
        $payload = $request->validated();
        $providerResponse = [];
        try {
            $result = $this->fulfillmentCreationService->createWithShipment(
                $payload,
                (int)Auth::user()->id,
                (array)$request->input('line_items', []),
                (float)$request->getTotalWeight(),
            );
        } catch (FulfillmentAlreadyAssignedException $exception) {
            $errorMessage = $exception->getMessage() ?: __('custom.fulfillment_already_assigned');
            if ($request->expectsJson()) {
                return response()->json(
                    ['message' => $errorMessage],
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                );
            }
            return back()->withErrors([
                'fulfillment' => $errorMessage,
            ]);
        } catch (Throwable $exception) {
            $message = $exception->getMessage() ?: __('custom.error_trying_to_process_request');
            if ($exception instanceof ConnectionException || Str::contains($message, ['Maximum execution time', 'cURL error 28', 'timed out', 'Timeout'])) {
                $message = 'No hubo respuesta del proveedor (tiempo de espera agotado). No se guardo ningun dato; intenta nuevamente.';
            }
            if ($request->expectsJson()) {
                return response()->json(
                    [
                        'message' => $message,
                        'provider_error' => $message,
                    ],
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }
            return back()->withErrors(['fulfillment' => $message]);
        }

        $fulfillment = $result['fulfillment'];
        $providerResponse = $result['provider_response'] ?? [];

        event(new FulfillmentCreated($fulfillment));

        $successMessage = __('custom.fulfillment_created_success');
        $orderRouteParams = array_merge(
            ['id' => $payload['order_id']],
            $request->query(),
        );
        $orderRoute = route('orders.show', $orderRouteParams);

        if ($request->expectsJson()) {
            return response()->json(
                [
                    'message' => $successMessage,
                    'order_route' => $orderRoute,
                    'provider_response' => $providerResponse ?? [],
                ],
                Response::HTTP_CREATED,
            );
        }

        return redirect()
            ->route('orders.show', $orderRouteParams)
            ->with('success', $successMessage);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): view
    {
        $productImages = [];
        $fulfillment = Fulfillment::query()
            ->with(['logisticProvider', 'user', 'order'])
            ->findOrFail($id);
        if ($fulfillment->getLineItems()) {
            $productIdsArray = array_unique(
                array_map(fn ($item) => (string)$item['product_id'], $fulfillment->getLineItems()),
            );
            $productImages = $this->shopService->getProductsImages(new ProductIds(...$productIdsArray));
        }
        return view('fulfillment.show', compact('fulfillment', 'productImages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFulfillmentRequest $request, string $id): RedirectResponse
    {
        $fulfillment = Fulfillment::query()->findOrFail($id);

        if ($fulfillment->status === FulfillmentStatus::DELIVERED->value) {
            return back()->withErrors([
                'fulfillment' => 'No se puede actualizar un despacho cuando ya fue entregado.',
            ]);
        }

        $informationToUpdate = $request->validated();
        switch ($fulfillment->status) {
            case FulfillmentStatus::CANCELLED->value:
                if (Carbon::parse($fulfillment->delivery_date)->subDay()->lessThan(Carbon::now())) {
                    return back()->withErrors([
                        'fulfillment' => __('custom.only_can_cancel_fulfillment_one_day_before_delivery_date'),
                    ]);
                }
                break;
        }
        $fulfillment->update($informationToUpdate);
        return redirect()->route('orders.show', ['id' => $fulfillment->order->id]);
    }

    public function printDeliveryNote(int $id): View|Response
    {
        $fulfillment = Fulfillment::query()
            ->with(['logisticProvider', 'user', 'order'])
            ->findOrFail($id);
        if ($fulfillment->status === FulfillmentStatus::CANCELLED->value) {
            abort(403);
        }

        if (($fulfillment->logisticProvider?->code ?? null) === 'LAAR') {
            try {
                $provider = LogisticProviderResolver::resolve('LAAR');
                if ($provider instanceof LAARProvider) {
                    return $provider->downloadDeliveryNote($fulfillment);
                }
            } catch (Throwable $e) {
                CustomLog::saveLog(
                    'ERROR',
                    'No se pudo descargar la guia desde LAAR.',
                    [$e->getTraceAsString()]
                );
            }
        }

        $order = $fulfillment->order;
        return view(
            'fulfillment.delivery-note',
            [
                'shipper_name' => config('app.name', 'Application'),
                'tracking_number' => $fulfillment->tracking_number,
                'dispatched_at' => $fulfillment->dispatched_at,
                'recipient_name' => sprintf(
                    '%s %s',
                    $order->order->shipping_address->first_name
                    ?? $order->order->customer->first_name
                    ?? null,
                    $order->order->shipping_address->last_name
                    ?? $order->order->customer->last_name
                    ?? null,
                ),
                'recipient_address' => $order->order->shipping_address->address1
                    ?? $order->order->shipping_address->address2
                        ?? $order->order->default_address->address1
                        ?? $order->order->default_address->address2
                        ?? $order->order->billing_address
                        ?? null,
                'recipient_phone' => $order->order->phone
                    ?? $order->order->shipping_address->phone
                        ?? $order->order->default_address->phone
                        ?? $order->order->customer->phone
                        ?? null,
                'notes' => $order->notes,
                'line_items' => $fulfillment->getLineItems(),
            ],
        );
    }

    private function existsPendingFulfillment(int $orderId): bool
    {
        return Order::findOrFail($orderId)
            ->fulfillments()
            ->where('status', '<>', FulfillmentStatus::CANCELLED)
            ->count() > 1 ? true : false;
    }

    public function cancel(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $fulfillment = Fulfillment::query()
            ->with('logisticProvider', 'order')
            ->findOrFail($id);

        if ($fulfillment->status === FulfillmentStatus::DELIVERED->value) {
            $message = 'No se puede cancelar un despacho cuando ya fue entregado.';
            if ($request->expectsJson()) {
                return response()->json(
                    ['message' => $message],
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                );
            }
            return back()->withErrors(['fulfillment' => $message]);
        }

        $orderRouteParams = array_merge(
            ['id' => $fulfillment->order->id],
            $request->query(),
        );
        $orderRoute = route('orders.show', $orderRouteParams);

        try {
            $providerResponse = null;
            $provider = $fulfillment->logisticProvider;
            if ($provider?->can_cancel_orders ?? false) {
                $resolvedProvider = LogisticProviderResolver::resolve($provider->code ?? '');
                if ($resolvedProvider !== null) {
                    $providerResponse = $resolvedProvider->cancelShipment($fulfillment);
                }
            }

            $fulfillment->status = FulfillmentStatus::CANCELLED->value;
            $fulfillment->saveOrFail();
        } catch (Throwable $exception) {
            $message = __('custom.error_trying_to_process_request');
            if ($request->expectsJson()) {
                return response()->json(
                    ['message' => $message,'error' => $exception->getMessage()],
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }
            return back()->withErrors(['fulfillment' => $message]);
        }

        $successMessage = __('custom.fulfillment_cancelled_success');
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $successMessage,
                'order_route' => $orderRoute,
                'provider_response' => $providerResponse,
            ]);
        }

        return redirect()
            ->route('orders.show', $orderRouteParams)
            ->with('success', $successMessage);
    }
}
