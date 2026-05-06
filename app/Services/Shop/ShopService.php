<?php

namespace App\Services\Shop;

use App\Models\Domain\ProductIds;
use App\Models\Domain\VariantIds;
use App\Models\ShippingRate;
use App\Models\Shopify\ShopifyResponse;
use Illuminate\Http\JsonResponse;

interface ShopService
{
    public function query(
        string $query,
        array $variables = [],
        array $body = [],
        string $path = 'graphql',
        bool $useAdminApi = false,
        ?string $key = null,
    ): ?ShopifyResponse;

    public function updateOrderFinancialStatus(string $orderId, string $financialStatus): ?JsonResponse;

    public function getCollectionById(string $collectionId, int $depth): JsonResponse;

    public function createOrder(
        array $products,
        array $address,
        string $firebaseUid,
        string $email,
        string $shopifyCustomerId,
        ?int $userCardId,
        ?string $shippingCode,
        ?string $recurringId,
        ?string $source,
    ): array;

    public function getAllDiscounts(): array;

    public function getVariants(array $ids): array;

    public function getProductsInformation(ProductIds $productIds): ShopifyResponse;

    public function searchProduct(string $q, ?string $after): ShopifyResponse;

    public function getProductVariantsByID(string $productId): ShopifyResponse;

    public function getVariantsInformation(VariantIds $variantIds): ShopifyResponse;

    public function createOrderInShopify(
        string $customerId,
        array $lineItems,
        array $shippingAddress,
        string $email,
        ?ShippingRate $deliveryCost,
        float $tax,
        string $status,
    ): ?array;

    public function getProductOfferRecurrence(string $id, int $quantity): array;

    public function getInventoryAvailableByProduct(string $productId): ShopifyResponse;

    public function getProductsFromCollection(string $collectionId, int $first, ?string $after = null): ShopifyResponse;

    public function getProductsImages(ProductIds $productIds): array;

    public function getInventoryAvailableByVariant(string $variantId): ShopifyResponse;

    public function getVariantInformation(string $variantId): ShopifyResponse;
}
