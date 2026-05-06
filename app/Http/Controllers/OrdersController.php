<?php

namespace App\Http\Controllers;

use App\Exceptions\CardInsufficientFundsException;
use App\Http\Formatters\ApiResponseFormatter;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\GetOrdersByUserRequest;
use App\Http\Requests\GetRecurringOrdersByUserRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Requests\UpdateRecurringOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\RecurringOrderResource;
use App\Models\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\PaymentGateway\CreatePayment;
use App\Models\PaymentGateway\Response\CustomStatus;
use App\Models\RecurringOrder;
use App\Services\Eloquent\RecurringOrderService;
use App\Services\PaymentGateways\PaymentGatewayService;
use App\Services\Shop\ShopifyService;
use App\Services\Shop\ShopService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class OrdersController extends Controller
{
    public function __construct(
        private readonly RecurringOrderService $recurringOrderService,
        private readonly ShopService $shopService,
        private readonly PaymentGatewayService $paymentGatewayService,
    ) {
    }

    public function createOrder(CreateOrderRequest $request): JsonResponse
    {
        try {
            return ApiResponseFormatter::formatSuccess(
                $this->shopService->createOrder(
                    $request->validated()['products'],
                    $request->validated()['address'],
                    $request->attributes->get('firebase_uid'),
                    $request->attributes->get('firebase_email'),
                    $request->attributes->get('shopify_uid'),
                    $request->validated()['user_card_id'],
                    $request->validated()['shipping_code'],
                    $request->validated()['recurring_id'] ?? null,
                    $request->validated()['source'],
                ),
                __('custom.order_registered_successfully'),
                Response::HTTP_CREATED,
            );
        } catch (CardInsufficientFundsException $exception) {
            return ApiResponseFormatter::formatError(
                $exception->getUserMessage(),
                $exception->getMessage(),
                Response::HTTP_CONFLICT,
            );
        } catch (Exception $exception) {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_get_registering_order'),
                $exception->getMessage(),
                Response::HTTP_CONFLICT,
            );
        }
    }

    public function index(GetOrdersByUserRequest $request): JsonResponse
    {
        return OrderResource::formatCollectionToApiResponse(
            Order::query()
                ->where('user_id', '=', $request->getUserId())
                ->orderByDesc('created_at')
                ->paginate(),
            'Ordenes obtenidas exitosamente',
        );
    }

    public function show(string $id): JsonResponse
    {
        $order = Order::query()->findOrFail($id);
        return (new OrderResource($order))
            ->toApiResponse(
                'Orden obtenida',
                Response::HTTP_OK
            );
    }

    public function getRecurringOrdersByUser(GetRecurringOrdersByUserRequest $request): JsonResponse
    {
        return RecurringOrderResource::formatCollectionToApiResponse(
            RecurringOrder::query()
                ->where('user_id', '=', $request->getUserId())
                ->orderByDesc('created_at')
                ->paginate(),
            'Ordenes recurrentes obtenidas exitosamente',
        );
    }

    public function deleteRecurringOrder(int $id): JsonResponse
    {
        try {
            RecurringOrder::query()->findOrFail($id)->delete();
            return ApiResponseFormatter::formatSuccess([], 'Se ha eliminado su orden recurrente.');
        } catch (ModelNotFoundException $exception) {
            return ApiResponseFormatter::formatError(
                'No fue posible eliminar su orden recurrente.',
                $exception->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
    }

    public function updateRecurringOrder(int $id, UpdateRecurringOrderRequest $request): JsonResponse|JsonResource
    {
        try {
            $this->recurringOrderService->updateRecurringOrder($request->getUpdateRecurringOrder());
            return new RecurringOrderResource(RecurringOrder::find($id));
        } catch (ModelNotFoundException $exception) {
            return ApiResponseFormatter::formatError(
                'Error attempting to update recurring order',
                $exception->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        } catch (Exception $exception) {
            return ApiResponseFormatter::formatError(
                'Error attempting to update recurring order',
                $exception->getMessage(),
                Response::HTTP_CONFLICT,
            );
        }
    }

    public function updateOrder(int $id, UpdateOrderRequest $request): OrderResource
    {
        try {
            $order = Order::query()->findOrFail($id);
            $order->update($request->validated());
            return new OrderResource($order);
        } catch (ModelNotFoundException $exception) {
            return ApiResponseFormatter::formatError(
                'No se encontró la orden',
                $exception->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
    }

    public function retryPayment(int $id): JsonResponse
    {
        $order = Order::query()
            ->with(['card'])
            ->findOrFail($id);

        if (!isset($order->order->totals)) {
            return ApiResponseFormatter::formatSuccess(
                [],
                __('order-errors.order_could_not_be_processed'),
                Response::HTTP_FORBIDDEN
            );
        }
        if ($order->status === OrderStatus::PAID_STATUS) {
            return ApiResponseFormatter::formatSuccess(
                [],
                __('order-errors.order_already_paid'),
                Response::HTTP_FORBIDDEN
            );
        }
        try {
            $responseFromPaymentGateway = $this->paymentGatewayService->processPaymentWithToken(
                new CreatePayment(
                    $order->order->totals->subtotal,
                    $order->order->totals->subtotal_with_tax,
                    $order->card->token,
                    ShopifyService::DESCRIPTION_PAYMENT_GATEWAY,
                    false,
                    false,
                    0,
                    '',
                ),
            );
        } catch (Exception $exception) {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_get_registering_order'),
                $exception->getMessage(),
                Response::HTTP_CONFLICT,
            );
        }
        if ($responseFromPaymentGateway->getCustomStatus() !== CustomStatus::SUCCESS) {
            return ApiResponseFormatter::formatError(
                $responseFromPaymentGateway->getMessage(),
                $responseFromPaymentGateway->getMessage(),
                Response::HTTP_CONFLICT,
            );
        }
        $this->shopService->updateOrderFinancialStatus(
            $order->shopify_order_id,
            OrderStatus::PAID_STATUS->value
        );
        OrderPayment::create([
            'order_id' => $order->id,
            'status' => $responseFromPaymentGateway->getCustomStatus()->name,
            'message' => $responseFromPaymentGateway->getMessage(),
            'details' => $responseFromPaymentGateway->getDetails(),
            'processed_at' => Carbon::now('UTC'),
        ]);
        $order->setStatus(OrderStatus::PAID_STATUS->value)->saveOrFail();
        return ApiResponseFormatter::formatSuccess([], 'Ok', Response::HTTP_CREATED);
    }

}
