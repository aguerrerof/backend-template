<?php

namespace App\Services\Eloquent;

use App\Models\Domain\ProductIds;
use App\Models\Domain\VariantIds;
use App\Models\Order;
use App\Services\Shop\ShopService;
use Illuminate\Support\Collection;

class OrderEloquentService implements OrderService
{
    public function __construct(private readonly ShopService $shopService)
    {
    }

    public function getReorderableProducts(string $userId): Collection
    {
        $orders = Order::where('user_id', '=', $userId)
            ->get()
            ->flatMap(function (Order $order) {
                if (!isset($order->order->line_items) || !is_array($order->order->line_items)) {
                    return [];
                }

                return $order->order->line_items;
            })
            ->unique('variant_id')
            ->map(function ($item) {
                return [
                    'variant_gid' => sprintf('gid://shopify/ProductVariant/%s', $item->variant_id),
                    'variant_id' => $item->variant_id,
                    'product_id' => sprintf('gid://shopify/Product/%s', $item->product_id),
                ];
            })
            ->groupBy('product_id')
            ->values();
        return $this->getProductsInformation($orders);
    }

    private function filterByStock(VariantIds $variantIds): array
    {
        $nodes = $this->shopService
            ->getVariantsInformation($variantIds)
            ->getData()['nodes'] ?? [];
        return array_filter(
            array_map(function ($node) {
                $total = 0;
                foreach ($node['inventoryItem']['inventoryLevels']['edges'] ?? [] as $edge) {
                    foreach ($edge['node']['quantities'] ?? [] as $quantity) {
                        if (($quantity['name'] ?? null) === 'available') {
                            $total += intval($quantity['quantity']);
                        }
                    }
                }
                $node['total_stock'] = $total;

                return $node;
            }, $nodes),
            fn ($node) => $node['total_stock'] > 1
        );

    }

    private function getProductsInformation(Collection $groupOrders): Collection
    {
        $products = new Collection();
        $groupOrders->each(function (Collection $order) use (&$products) {
            $productInformation = $this->shopService->getProductsInformation(
                new ProductIds($order->first()['product_id'])
            )->getData()['nodes'][0] ?? [];
            $variantIds = $order->pluck('variant_gid')->values();
            $variants = $this->filterByStock(new VariantIds(...$variantIds->toArray()));
            foreach ($variants as &$variant) {
                $variant['discount'] = $this->shopService->getProductOfferRecurrence(
                    $variant['id'],
                    $variant['total_stock'],
                )['data'] ?? [];
            }
            $productInformation['variants']['purchased'] = $variants;
            if (!empty($variants)) {
                $products->add($productInformation);
            }

        });
        return $products;
    }

}
