<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetOrdersByUserRequest;
use App\Models\Domain\ProductIds;
use App\Models\Order;
use App\Models\RecurringOrder;
use App\Services\Shop\ShopService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function __construct(private readonly ShopService $shopService)
    {
    }

    public function index(GetOrdersByUserRequest $request): RedirectResponse|view
    {
        $perPage = (int)$request->query('perPage', 5);
        $builder = Order::query()
            ->with('card')
            ->where('order->financial_status', '=', Order::STATUS_PAID)
            ->orderBy('created_at_shopify', 'desc');

        if ($search = trim($request->query('q', ''))) {
            $term = "%{$search}%";
            $builder->where(function ($query) use ($term) {
                $query
                    ->whereRaw('"order"->>\'financial_status\' ILIKE ?', [$term])
                    ->orWhereRaw('"order"->\'customer\'->>\'first_name\' ILIKE ?', [$term])
                    ->orWhereRaw('"order"->\'customer\'->>\'last_name\' ILIKE ?', [$term])
                    ->orWhereRaw('"order"->>\'order_number\' ILIKE ?', [$term])
                    ->orWhereRaw('"shopify_order_id"::text ILIKE ?', [$term]);
            });
        }

        if ($request->filled(['from', 'to'])) {
            try {
                $from = Carbon::parse($request->query('from'))->startOfDay();
                $to = Carbon::parse($request->query('to'))->endOfDay();
                if ($from->lte($to)) {
                    $builder->whereBetween('created_at_shopify', [$from, $to]);
                }
            } catch (Exception $e) {
                $errors = ['date_range' => __('custom.invalid-date-range-selected')];
            }
        }
        $orders = $builder
            ->paginate(
                in_array($perPage, [5, 10])
                    ? $perPage
                    : 5,
            )
            ->withQueryString();
        return view('orders.list', compact('orders', 'perPage'))
            ->withErrors($errors ?? null);
    }

    public function show(int $id, Request $request): View
    {
        $queryParams = $request->query();
        $order = Order::query()->with('card')->findOrFail($id);
        $productIdsArray = array_unique(
            array_map(fn ($item) => (string)$item->product_id, $order->order->line_items),
        );
        $productImages = $this->shopService->getProductsImages(new ProductIds(...$productIdsArray));
        $lineItems = $order->getLineItems();
        $fulfillments = $order
            ->fulfillments()
            ->latest()
            ->paginate(3)
            ->withQueryString();
        return view(
            'orders.show',
            compact(
                'order',
                'productImages',
                'queryParams',
                'fulfillments',
                'lineItems',
            ),
        );
    }

    public function listRecurringOrders(GetOrdersByUserRequest $request): RedirectResponse|view
    {
        $perPage = (int)$request->query('perPage', 5);
        $builder = RecurringOrder::query()
            ->with('card')
            ->orderBy('next_charge_date');

        if ($search = trim($request->query('q', ''))) {
            $term = "%{$search}%";
            $builder->where(function ($query) use ($term) {
                $query
                    ->whereRaw('"shipping_address"->>\'address1\' ILIKE ?', [$term])
                    ->orWhereRaw('"shipping_address"->>\'address2\' ILIKE ?', [$term])
                    ->orWhereRaw('"shipping_address"->>\'city\' ILIKE ?', [$term])
                    ->orWhereRaw('"shipping_address"->>\'country\' ILIKE ?', [$term])
                    ->orWhereRaw('"shipping_address"->>\'first_name\' ILIKE ?', [$term])
                    ->orWhereRaw('"shipping_address"->>\'last_name\' ILIKE ?', [$term])
                    ->orWhereRaw('"shipping_address"->>\'phone\' ILIKE ?', [$term])
                    ->orWhereRaw('"shipping_address"->>\'province\' ILIKE ?', [$term])
                    ->orWhereRaw('"shipping_address"->>\'zip\' ILIKE ?', [$term])
                    ->orWhereRaw('"email"::text ILIKE ?', [$term]);
            });
        }

        if ($request->filled(['from', 'to'])) {
            try {
                $from = Carbon::parse($request->query('from'))->startOfDay();
                $to = Carbon::parse($request->query('to'))->endOfDay();
                if ($from->lte($to)) {
                    $builder->whereBetween('next_charge_date', [$from, $to]);
                }
            } catch (Exception $e) {
                $errors = ['date_range' => __('custom.invalid-date-range-selected')];
            }
        }
        $recurringOrders = $builder
            ->paginate(
                in_array($perPage, [5, 10])
                    ? $perPage
                    : 5,
            )
            ->withQueryString();
        return view('orders.recurring.list', compact('recurringOrders', 'perPage'))
            ->withErrors($errors ?? null);
    }

    public function showRecurringOrder(int $id, Request $request): View
    {
        $queryParams = $request->query();
        $recurringOrder = RecurringOrder::query()->with('card')->findOrFail($id);
        $orders = $recurringOrder->orders()->paginate(3);
        return view('orders.recurring.show', compact('recurringOrder', 'orders', 'queryParams'));
    }
}
