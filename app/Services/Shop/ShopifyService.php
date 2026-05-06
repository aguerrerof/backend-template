<?php

namespace App\Services\Shop;

use App\Exceptions\ProductInventoryNotFoundException;
use App\Exceptions\ProductVariantNotFoundException;
use App\Helpers\CustomLog;
use App\Helpers\OrderHelper;
use App\Helpers\ServiceResponse;
use App\Http\Formatters\ApiResponseFormatter;
use App\Models\Domain\ProductIds;
use App\Models\Domain\VariantIds;
use App\Models\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\PaymentGateway\CreatePayment;
use App\Models\PaymentGateway\Response\CustomStatus;
use App\Models\ShippingRate;
use App\Models\Shopify\ShopifyResponse;
use App\Models\UsedDiscount;
use App\Models\UserCard;
use App\Services\PaymentGateways\PaymentGatewayService;
use Carbon\Carbon;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ShopifyService implements ShopService
{
    public const DESCRIPTION_PAYMENT_GATEWAY = 'Order payment';

    public function __construct(
        protected readonly Client $client,
        protected readonly PaymentGatewayService $paymentGatewayService,
        protected readonly string $accessToken,
        protected readonly string $apiVersion,
        protected readonly string $storeFrontToken,
    ) {
    }

    public function query(
        string $query = '',
        array $variables = [],
        array $body = [],
        string $path = 'graphql',
        bool $useAdminApi = false,
        ?string $key = null,
    ): ?ShopifyResponse {
        try {
            $finalHeaders = ['Content-Type' => 'application/json',];
            $prefix = '';
            if ($useAdminApi) {
                $finalHeaders['X-Shopify-Access-Token'] = $this->accessToken;
                $prefix = '/admin';
            } else {
                $finalHeaders['X-Shopify-Storefront-Access-Token'] = $this->storeFrontToken;
            }

            $endpoint = $prefix . "/api/{$this->apiVersion }/{$path}.json";

            if (empty($body)) {
                $body = [
                    'query' => $query,
                ];
                if (!empty($variables)) {
                    $body['variables'] = $variables;
                }
            }
            $response = $this->client->post($endpoint, [
                'headers' => $finalHeaders,
                'json' => $body,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode >= 200 && $statusCode < 300) {
                $responseFromShopify = json_decode($response->getBody()->getContents(), true);
                $generalResponse['data'] = $responseFromShopify[$key]
                    ?? $responseFromShopify['data'][$key]
                    ?? $responseFromShopify['data']
                    ?? $responseFromShopify;

                return new ShopifyResponse($generalResponse);
            } else {
                throw new Exception("Error en la solicitud a Shopify: HTTP $statusCode");
            }
        } catch (Exception $e) {
            CustomLog::saveLog(
                'ERROR',
                'Shopify error',
                [
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'trace' => $e->getTraceAsString(),
                ],
            );
            throw $e;
        }
    }

    public function updateOrderFinancialStatus(string $orderId, string $financialStatus): ?JsonResponse
    {
        $mutation = '
            mutation orderUpdate($input: OrderInput!) {
                orderUpdate(input: $input) {
                    order {
                        id
                        financialStatus
                        processedAt
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        ';
        try {
            $response = $this->query($mutation, [
                'input' => [
                    'id' => sprintf('gid://shopify/Order/%s', $orderId),
                    'financialStatus' => $financialStatus,
                ],
            ]);

            if ($response->hasErrors()) {
                CustomLog::saveLog(
                    'WARNING',
                    'Failed to update order financial status.',
                    [
                        'order_id' => $orderId,
                        'status_attempted' => $financialStatus,
                        'error_message' => $response->getFullErrorMessage(),
                    ],
                );
                return ApiResponseFormatter::formatError(
                    'Existio un error al actualizar la orden',
                    $response->getFullErrorMessage(),
                );
            }

            return ApiResponseFormatter::formatSuccess($response->getData(), 'Orden actualizada exitosamente');
        } catch (Exception $e) {
            CustomLog::saveLog(
                'ERROR',
                'Shopify order financial status update failed',
                [
                    'order_id' => $orderId,
                    'status_attempted' => $financialStatus,
                    'error_message' => $e->getMessage(),
                ],
            );
            throw $e;
        }
    }

    private function getMetafields(array $collectionData, int $depth): array
    {
        $metafields = [];

        $edges = $collectionData['metafields']['edges'] ?? [];

        foreach ($edges as $metafieldEdge) {
            try {
                $m = $metafieldEdge['node'];
                $inferred = Helper::inferMetafieldType($m['namespace'], $m['key'], $m['value']);
                $inferredType = $inferred['inferredType'];
                $parsedValue = $inferred['parsedValue'];
                error_log($inferredType);
                error_log($m['key']);

                if ($inferredType === 'json_list') {
                    $gids = $parsedValue;
                    if (empty($gids)) {
                        $metafields[] = [
                            'namespace' => $m['namespace'],
                            'key' => $m['key'],
                            'value' => [],
                            'inferredType' => $inferredType,
                        ];
                        continue;
                    }

                    if ($m['key'] === 'image_list') {
                        $urls = $this->getMediaImageUrls($gids);
                        $metafields[] = [
                            'namespace' => $m['namespace'],
                            'key' => $m['key'],
                            'value' => $urls,
                            'inferredType' => $inferredType,
                        ];
                        continue;
                    } elseif ($m['key'] === 'subcategories') {
                        if ($depth === 0) {
                            $metafields[] = [
                                'namespace' => $m['namespace'],
                                'key' => $m['key'],
                                'value' => $gids,
                                'inferredType' => $inferredType,
                            ];
                            continue;
                        }

                        $collections = [];
                        foreach ($gids as $gid) {
                            try {
                                $newDepth = max($depth - 1, 0);
                                $response = $this->getCollectionById($gid, $newDepth);
                                $data = $response->getData();
                                if (isset($data->data)) {
                                    $collections[] = $data->data;
                                }
                            } catch (Exception $e) {
                                CustomLog::saveLog(
                                    'ERROR',
                                    "Error en fetch recursivo para gid {$gid}",
                                    [
                                        'error_message' => $e->getMessage(),
                                    ],
                                );
                            }
                        }

                        $metafields[] = [
                            'namespace' => $m['namespace'],
                            'key' => $m['key'],
                            'value' => $collections,
                            'inferredType' => $inferredType,
                        ];
                        continue;
                    }
                }

                if ($m['key'] === 'collectionimage' || $m['key'] === 'image_header') {
                    $collectionImage = $this->getMediaImageUrls([$parsedValue]);
                    $metafields[] = [
                        'namespace' => $m['namespace'],
                        'key' => $m['key'],
                        'value' => $collectionImage[0] ?? $parsedValue,
                        'inferredType' => $inferredType,
                    ];
                    continue;
                }

                $metafields[] = [
                    'namespace' => $m['namespace'],
                    'key' => $m['key'],
                    'value' => $parsedValue,
                    'inferredType' => $inferredType,
                ];
            } catch (Exception $e) {
                CustomLog::saveLog(
                    'ERROR',
                    "Error en getMetafields para node {$m['key']}",
                    [
                        'error_message' => $e->getMessage(),
                    ],
                );
            }
        }

        return $metafields;
    }

    /**
     * Obtiene una colección por ID de Shopify
     */
    public function getCollectionById(string $collectionId, int $depth): JsonResponse
    {
        $query = 'query collection($id: ID!) {
        collection(id: $id) {
          id
          title
          description
          image {
            url
          }
          products(first: 20) {
            edges {
              node {
                id
                title
                description
                vendor
                variants(first: 50) {
                  edges {
                    node {
                      id
                      price
                      compareAtPrice
                      selectedOptions {
                        name
                        value
                      }
                      image {
                        url
                      }
                      taxable
                    }
                  }
                }
                media(first: 10) {
            edges {
              node {
                mediaContentType
                alt
                ... on MediaImage {
                  image {
                    url
                  }
                }
                ... on Video {
                  sources {
                    url
                    format
                    mimeType
                  }
                }
                ... on ExternalVideo {
                  embeddedUrl
                  host
                }
                ... on Model3d {
                  sources {
                    url
                    format
                    mimeType
                  }
                }
              }
            }
          }
              }
              cursor
            }
            pageInfo {
              hasNextPage
            }
          }
          metafields(first: 10) {
            edges {
              node {
                namespace
                key
                value
              }
            }
          }
        }
      }';

        try {
            $response = $this->query($query, [
                'id' => $collectionId,
            ], useAdminApi: true);

            if ($response->hasErrors()) {
                throw new Exception('GraphQL errors: ' . $response->getFullErrorMessage());
            }
            $collection = $response->getData()['collection'] ?? [];
            $metafields = $this->getmetafields($collection, $depth);

            $data = [
                'id' => $collection['id'],
                'title' => $collection['title'],
                'description' => $collection['description'],
                'imageUrl' => $collection['image']['url'] ?? '',
                'products' => $collection['products']['edges'] ?? [],
                'metafields' => $metafields,
            ];

            return ApiResponseFormatter::formatSuccess($data, 'Datos obtenidos correctamente');
        } catch (Exception $e) {
            CustomLog::saveLog(
                'ERROR',
                "Error obteniendo colección {$collectionId}",
                [
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'trace' => $e->getTraceAsString(),
                ],
            );
            return ApiResponseFormatter::formatError('Hubo un error obteniendo los datos', $e->getMessage());
        }
    }

    public function getMediaImageUrls(array $mediaImageGids): array
    {
        try {
            $mediaIds = array_map(fn ($gid) => sprintf('"%s"', $gid), $mediaImageGids);
            $mediaIdsString = implode(',', $mediaIds);
            $query =
                'query {
                    nodes(ids: [' . $mediaIdsString . ']) {
                        ... on MediaImage {
                            id
                            image {
                                url
                            }
                        }
                    }
                }';

            $response = $this->query($query, useAdminApi: true);

            if ($response->hasErrors()) {
                CustomLog::saveLog(
                    'ERROR',
                    'GraphQL Error fetching media URLs',
                    [
                        'error_message' => $response->getFullErrorMessage(),
                    ],
                );
                return [];
            }

            return $response->getData()['nodes'] ?? [];
        } catch (Exception $e) {
            CustomLog::saveLog(
                'ERROR',
                'Unexpected error fetching media URLs',
                [
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'trace' => $e->getTraceAsString(),
                ],
            );
            return [];
        }
    }

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
    ): array {
        try {
            $variants = array_map(fn ($lineItem) => $lineItem['variantId'], $products);
            $variants = array_filter($variants);
            $variants = array_unique($variants);
            $variantIds = new VariantIds(...$variants);
            $productsFromShopify = [];
            $variantInformation = $this->getVariantsInformation($variantIds);
            foreach ($variantInformation->getData()['nodes'] as $productFromShopify) {
                $productsFromShopify[$productFromShopify['id']] = $productFromShopify;
            }
            $updatedProducts = [];
            foreach ($products as $lineItem) {
                $lineItem = (array)$lineItem;
                $newInformationLineItem = $lineItem;

                $price = (float)($lineItem['price'] ?? 0.0);
                $taxable = $lineItem['taxable'] ?? false;

                if (isset($lineItem['variantId']) && isset($productsFromShopify[$lineItem['variantId']])) {
                    $productInfo = $productsFromShopify[$lineItem['variantId']];
                    if (isset($productInfo['price'])) {
                        $price = (float)$productInfo['price'];
                    }
                    if (isset($productInfo['taxable'])) {
                        $taxable = (bool)$productInfo['taxable'];
                    }
                }
                $newInformationLineItem['price'] = $price;
                $newInformationLineItem['applyTax'] = $taxable;
                $newInformationLineItem['applyDiscount'] = true;
                $updatedProducts[] = $newInformationLineItem;
            }

            [
                $tax,
                $shippingRate,
                $isInitialRecurrenceUsed,
                $discountData,
                $items,
                $subtotal,
                $subtotalIVA,
                $lineItems,
            ] = $this->preparePayloadToCreateOrder($shippingCode, $shopifyCustomerId, $updatedProducts);

            $delivery = isset($shippingRate['price'])
                ? floatval($shippingRate['price'])
                : 0.0;
            $deliveryCostIVA = $delivery * ($tax / 100);

            $subtotalIVA += ($delivery + $deliveryCostIVA);
            $paymentCard = null;
            $status = OrderStatus::PENDING_STATUS->value;
            $responseFromPaymentGateway = null;
            if (isset($userCardId) && ($source ?? '') !== 'API-Gateway') {
                $paymentCard = UserCard::getByUserAndId($shopifyCustomerId, $userCardId);
                if ($paymentCard) {
                    [$responseFromPaymentGateway,$status] = $this->processPaymentForOrder(
                        [
                            'base_amount_0' => round($subtotal, 2),
                            'tax_amount' => round($subtotalIVA, 2),
                            'token' => $paymentCard->token,
                            'description' => self::DESCRIPTION_PAYMENT_GATEWAY,
                            'has_grace_period' => false,
                            'apply_interest' => false,
                            'installments' => 0,
                        ],
                        $shopifyCustomerId,
                        $items,
                    );
                }
            }
            $responseFromCreationOrder = $this->createOrderInShopify(
                $shopifyCustomerId,
                $lineItems,
                $address,
                $email,
                $shippingRate,
                $tax,
                $status,
            );

            if (is_null($responseFromCreationOrder)) {
                throw new Exception(
                    sprintf('Errores al crear orden: %s', json_encode($responseFromCreationOrder['errors'])),
                );
            }
            /** @var Order $orderEloquentModel */
            $orderEloquentModel = $this->createOrderInDatabase(
                $shopifyCustomerId,
                $responseFromCreationOrder['id'],
                $source,
                $recurringId,
                $userCardId,
                $responseFromCreationOrder,
            );
            OrderPayment::create([
                'order_id' => $orderEloquentModel->id,
                'status' => $responseFromPaymentGateway->getCustomStatus()->name,
                'message' => $responseFromPaymentGateway->getMessage(),
                'details' => $responseFromPaymentGateway->getDetails(),
                'processed_at' => Carbon::now('UTC'),
            ]);
            $orderEloquentModel->setTotalsAttribute([
                'subtotal' => $subtotal,
                'subtotal_with_tax' => $subtotalIVA,
            ])->saveOrFail();
            $recurringIds = OrderHelper::handleRecurringOrders(
                $items,
                $address,
                $userCardId,
                $email,
                $shopifyCustomerId,
            );
            if (!empty($recurringIds) && !isset($isInitialRecurrenceUsed)) {
                UsedDiscount::createUsage($shopifyCustomerId, $discountData->id);
            }
            return array_merge(
                $responseFromCreationOrder ?? [],
                [
                    'payment_info' => ($paymentCard['cardIssuer'] ?? '') . ' ' . (Helper::getCardMask(
                        $paymentCard['cardInfo'] ?? '',
                    ) ?? ''),
                    'recurring_ids' => implode(',', $recurringIds),
                    'shopify_order_id' => $orderEloquentModel->shopify_order_id,
                    'order' => $orderEloquentModel->toArray(),
                    'base_amount_0' => $subtotal,
                    'base_amount_iva' => $subtotalIVA,
                ],
            );
        } catch (Exception $e) {
            CustomLog::saveLog(
                'ERROR',
                'Error al crear la orden',
                [
                    'error_message' => $e->getMessage(),
                    'error' => $e->getTraceAsString(),
                ],
            );
            throw $e;
        }
    }

    public function createOrderInShopify(
        string $customerId,
        array $lineItems,
        array $shippingAddress,
        string $email,
        ?ShippingRate $deliveryCost,
        float $tax,
        string $status,
        bool $isRecurringOrder = false,
    ): ?array {
        try {
            $delivery = isset($deliveryCost['price'])
                ? (float)$deliveryCost['price']
                : 0.0;

            $deliveryCostIVA = $delivery * ($tax / 100);

            $orderData = [
                'order' => [
                    'customer' => [
                        'id' => Helper::extractNumericId($customerId),
                    ],
                    'line_items' => $lineItems,
                    'shipping_address' => $shippingAddress,
                    'financial_status' => $status,
                    'shipping_lines' => $deliveryCost === null ? [] : [
                        [
                            'title' => $deliveryCost['title'],
                            'price' => $deliveryCost['price'],
                            'code' => $deliveryCost['code'],
                            'source' => 'shopify',
                            'tax_lines' => [
                                [
                                    'price' => $deliveryCostIVA,
                                    'rate' => $tax / 100,
                                    'title' => 'IVA',
                                ],
                            ],
                        ],
                    ],
                ],
                'send_receipt' => true,
                'inventory_behaviour' => 'bypass',
            ];
            if (!$isRecurringOrder) {
                $orderData['email'] = $email;
            }
            $response = $this->query('', [], $orderData, 'orders', true, 'order');
            if ($response->hasErrors()) {
                throw new Exception($response->getFullErrorMessage());
            }

            return $response->getData();
        } catch (Exception $e) {
            CustomLog::saveLog(
                'ERROR',
                'Error al crear la orden en shopify ',
                [
                    'error_message' => $e->getMessage(),
                ],
            );
            throw $e;
        }
    }

    public function getAllDiscounts(): array
    {
        $baseUrl = sprintf(
            '%s/admin/api/%s',
            config('services.shopify.store_url'),
            config('services.shopify.api_version'),
        );
        $priceRules = $this->getPriceRules($baseUrl);
        $discounts = [];
        foreach ($priceRules as $priceRule) {
            $uri = sprintf(
                '%s/price_rules/%s/discount_codes.json',
                $baseUrl,
                $priceRule['id'],
            );
            $response = $this->client->get(
                $uri,
                [
                    'headers' => [
                        'X-Shopify-Access-Token' => $this->accessToken,
                        'Content-Type' => 'application/json',
                    ],
                ],
            );
            $statusCode = $response->getStatusCode();
            if ($statusCode >= 200 && $statusCode < 300) {
                $discountCodes = json_decode($response->getBody()->getContents(), true)['discount_codes'];
                foreach ($discountCodes as $discount) {
                    $discounts[] = [
                        'code' => $discount['code'],
                        'rule_id' => $priceRule['id'],
                        'title' => $priceRule['title'],
                        'value' => $priceRule['value'],
                        'value_type' => $priceRule['value_type'],
                        'starts_at' => $priceRule['starts_at'],
                        'ends_at' => $priceRule['ends_at'],
                        'usage_limit' => $priceRule['usage_limit'] ?? null,
                        'usage_count' => $priceRule['usage_count'] ?? null,
                        'enabled' => !$priceRule['ends_at'] || new DateTime(
                            $priceRule['ends_at'],
                        ) > new DateTime(),
                    ];
                }
            } else {
                throw new Exception(
                    sprintf('Error en la solicitud a Shopify: %s', $uri),
                );
            }
        }
        return $discounts;
    }

    public function getPriceRules($baseUrl): array
    {
        $uri = sprintf(
            '%s/price_rules.json',
            $baseUrl,
        );
        $response = $this->client->get(
            $uri,
            [
                'headers' => [
                    'X-Shopify-Access-Token' => $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
            ],
        );
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 200 && $statusCode < 300) {
            $body = $response->getBody()->getContents();
            return json_decode($body, true)['price_rules'];
        } else {
            throw new Exception(
                sprintf('Error en la solicitud a Shopify: %s', $uri),
            );
        }
    }

    public function getVariants(array $ids): array
    {
        $query = '
                    query getProductVariantsByIds($ids: [ID!]!) {
                    nodes(ids: $ids) {
                      ... on ProductVariant {
                        id
                        price
                        compareAtPrice
                        selectedOptions {
                          name
                          value
                        }
                        image {
                          url
                        }
                        taxable
                        product {
                          id
                          title
                        }
                      }
                    }
                  }
                ';

        try {
            $response = $this->query($query, [
                'ids' => $ids,
            ], useAdminApi: true);

            if ($response->hasErrors()) {
                throw new Exception($response->getFullErrorMessage(), );
            }

            return ServiceResponse::success($response->getData());
        } catch (Exception $e) {
            CustomLog::saveLog(
                'ERROR',
                "Error obteniendo las variabtes {$ids}",
                [
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'trace' => $e->getTraceAsString(),
                ],
            );
            return ServiceResponse::error('Hubo un error obteniendo los datos', devError: $e->getMessage());
        }
    }

    public function getProductsInformation(ProductIds $productIds): ShopifyResponse
    {
        return $this->query(
            'query getProducts($ids: [ID!]!) {
              nodes(ids: $ids) {
                ... on Product {
                  id
                  title
                  totalInventory
                  description
                  productType
                  vendor
                  tags
                  metafields(first: 20) {
                    edges {
                      node {
                        namespace
                        key
                        value
                        type
                      }
                    }
                  }
                  variants(first: 1) {
                    edges {
                      node {
                        id
                        price
                        selectedOptions {
                          name
                          value
                        }
                        taxable
                        image {
                          url
                        }
                         inventoryItem {
                            id
                            inventoryLevels(first: 5) {
                                edges {
                                    node {
                                        location {
                                            id
                                            name
                                        }
                                        quantities(names: ["available"]) {
                                            name
                                            quantity
                                        }
                                    }
                                }
                            }
                        }
                      }
                    }
                  }
                    media(first: 10) {
                    edges {
                      node {
                        mediaContentType
                        alt
                        ... on MediaImage {
                          image {
                            url
                          }
                        }
                        ... on Video {
                          sources {
                            url
                            format
                            mimeType
                          }
                        }
                        ... on ExternalVideo {
                          embeddedUrl
                          host
                        }
                        ... on Model3d {
                          sources {
                            url
                            format
                            mimeType
                          }
                        }
                      }
                    }
                  }
                }
              }
            }',
            [
                'ids' => $productIds->toArray(),
            ],
            useAdminApi: true,
        );
    }

    public function searchProduct(string $q, ?string $after): ShopifyResponse
    {
        return $this->query(
            'query searchProducts($query: String!, $after: String) {
        products(first: 20, query: $query, after: $after) {
          edges {
            node {
              id
              title
              totalInventory
              description
              productType
              vendor
              tags
              metafields(first: 20) {
                    edges {
                      node {
                        namespace
                        key
                        value
                        type
                      }
                    }
                  }
              variants(first: 1) {
                  edges {
                    node {
                      id
                      price
                      selectedOptions {
                        name
                        value
                      }
                      taxable
                      image {
                        url
                      }
                    }
                  }
                }
                media(first: 10) {
                            edges {
                              node {
                                mediaContentType
                                alt
                                ... on MediaImage {
                                  image {
                                    url
                                  }
                                }
                                ... on Video {
                                  sources {
                                    url
                                    format
                                    mimeType
                                  }
                                }
                                ... on ExternalVideo {
                                  embeddedUrl
                                  host
                                }
                                ... on Model3d {
                                  sources {
                                    url
                                    format
                                    mimeType
                                  }
                                }
                              }
                            }
                          }
            }
            cursor
          }
          pageInfo {
            hasNextPage
            endCursor
          }
        }
      }',
            [
                'query' => sprintf('%s OR vendor:%s OR variant_title:%s', $q, $q, $q),
                'after' => $after,
            ],
            useAdminApi: true,
        );
    }

    public function getProductVariantsByID(string $productId): ShopifyResponse
    {
        return $this->query(
            'query getProductVariants($id: ID!) {
                product(id: $id) {
                  id
                  title
                  metafields(first: 20) {
                    edges {
                      node {
                        namespace
                        key
                        value
                      }
                    }
                  }
                  variants(first: 50) {
                    edges {
                      node {
                        id
                        price

                        compareAtPrice
                      availableForSale
                        selectedOptions {
                          name
                          value
                        }
                        image {
                          url
                        }
                        taxable
                        inventoryItem {
                            id
                            inventoryLevels(first: 5) {
                              edges {
                                node {
                                  location {
                                    id
                                    name
                                  }
                                  quantities(names: ["available"]) {
                                    name
                                    quantity
                                  }
                                }
                              }
                            }
                        }
                      }
                    }
                  }
                }
              }
            ',
            [
                'id' => $productId,
            ],
            useAdminApi: true,
        );
    }

    public function getVariantsInformation(VariantIds $variantIds): ShopifyResponse
    {
        return $this->query(
            'query getProductVariantsByIds($ids: [ID!]!) {
                  nodes(ids: $ids) {
                    ... on ProductVariant {
                      id
                      price
                      compareAtPrice
                      availableForSale
                      selectedOptions {
                        name
                        value
                      }
                      image {
                        url
                      }
                      taxable
                      product {
                        id
                        title
                      }
                      inventoryItem {
                        id
                        inventoryLevels(first: 5) {
                          edges {
                            node {
                              location {
                                id
                                name
                              }
                              quantities(names: ["available"]) {
                                name
                                quantity
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
            ',
            [
                'ids' => $variantIds->toArray(),
            ],
            useAdminApi: true,
        );
    }

    public function getVariantInformation(string $variantId): ShopifyResponse
    {
        return $this->query(
            'query getProductVariantById($id: ID!) {
            productVariant(id: $id) {
                id
                price
                compareAtPrice
                availableForSale
                selectedOptions {
                    name
                    value
                }
                image {
                    url
                }
                taxable
                product {
                    id
                    title
                }
                inventoryItem {
                    id
                    inventoryLevels(first: 5) {
                        edges {
                            node {
                                location {
                                    id
                                    name
                                }
                                quantities(names: ["available"]) {
                                    name
                                    quantity
                                }
                            }
                        }
                    }
                }
            }
        }',
            [
                'id' => $variantId,
            ],
            useAdminApi: true,
        );
    }

    /**
     * @param string|null $shippingCode
     * @param string $uid
     * @param array $products
     * @return array
     */
    public function preparePayloadToCreateOrder(?string $shippingCode, string $uid, array $products): array
    {
        $tax = Helper::getSettingByKey('iva', 0);
        $initialRecurrenceDiscount = Helper::getSettingByKey('initial_recurrence_code', null);
        $recurrenceDiscount = Helper::getSettingByKey('recurrence_code', null);
        $fixedDiscount = Helper::getSettingByKey('fixed_discount', null);
        $minAmountFreeDelivery = Helper::getSettingByKey('min_amount_free_delivery');

        $fixedDiscountData = Helper::getDiscountByCode($fixedDiscount);
        $promoInitialRecurrence = Helper::getDiscountByCode($initialRecurrenceDiscount);
        $promoRecurrence = Helper::getDiscountByCode($recurrenceDiscount);
        $isInitialRecurrenceUsed = UsedDiscount::findByUserAndCode(
            $uid,
            $promoInitialRecurrence->id,
        );

        $discountData = isset($isInitialRecurrenceUsed)
            ? $promoRecurrence
            : $promoInitialRecurrence;

        $items = Helper::calculateDiscountInProducts(
            $products,
            $discountData,
            $fixedDiscountData,
            $tax,
        );

        /*
         * Calculo de totales
         */
        $subtotal = 0.0;
        $subtotalIVA = 0.0;
        $lineItems = [];

        foreach ($items as $item) {
            $price = isset($item['appliedDiscount'])
                ? Helper::formatDecimal($item['price'] - ($item['appliedDiscount']['amount'] ?? 0))
                : Helper::formatDecimal($item['price']);

            $discount = $item['appliedDiscount']['total_amount'] ?? 0.0;

            if (!empty($item['applyTax'])) {
                $subtotalIVA += ($item['subtotal'] - $discount) + ($item['tax'] ?? 0);
            } else {
                $subtotal += ($item['subtotal'] - $discount);
            }

            $lineItems[] = [
                'variant_id' => Helper::extractNumericId($item['variantId'] ?? null),
                'quantity' => $item['quantity'] ?? 0,
                'requiresShipping' => true,
                'taxable' => $item['applyTax'] ?? false,
                'price' => $price,
                'tax_lines' => !empty($item['applyTax']) ? [
                    [
                        'price' => $item['tax'] ?? 0,
                        'rate' => ($tax ?? 0) / 100,
                        'title' => 'IVA',
                    ],
                ] : null,
            ];
        }
        if (((bool)Helper::getSettingByKey('free_delivery_available')) && ($subtotal >= $minAmountFreeDelivery)) {
            $shippingRate = Helper::getShippingRate(ShippingRate::FREE_SHIPPING_CODE);
        } else {
            $shippingRate = Helper::getShippingRate($shippingCode);
        }
        return [
            $tax,
            $shippingRate,
            $isInitialRecurrenceUsed,
            $discountData,
            $items,
            $subtotal,
            $subtotalIVA,
            $lineItems,
        ];
    }

    /**
     * @param string $userId
     * @param string $shopifyOrderId
     * @param string|null $source
     * @param string|null $recurringId
     * @param int|null $userCardId
     * @param array $order
     * @return Order
     */
    public function createOrderInDatabase(
        string $userId,
        string $shopifyOrderId,
        ?string $source,
        ?string $recurringId,
        ?int $userCardId,
        array $order,
    ): Order {
        return Order::createOrder([
            'user_id' => $userId,
            'shopify_order_id' => $shopifyOrderId,
            'source' => $source ?? 'App',
            'notes' => $order['note'] ?? null,
            'recurring_id' => $recurringId ?? null,
            'user_card_id' => $userCardId ?? null,
            'order' => $order,
            'created_at_shopify' => $order['created_at'] ?? null,
            'created_at' => Carbon::now(),
        ]);
    }

    public function getProductOfferRecurrence(string $id, int $quantity): array
    {
        $variantId = new VariantIds($id);
        $response = $this->getVariantsInformation($variantId);
        if (!$response->isSuccess()) {
            throw new ProductVariantNotFoundException(
                $response->getFullErrorMessage(),
                0,
                null,
                __('custom.error_trying_to_get_product_offer_recurrence'),
            );
        }

        $data = $response->getData()['nodes'] ?? [];
        $variant = reset($data);
        if (!$variant) {
            throw new ProductVariantNotFoundException(
                '',
                0,
                null,
                'Variante de producto no encontrada',
            );
        }

        $product = [
            'quantity' => $quantity,
            'price' => $variant['price'] ?? 0,
            'apply_discount' => true,
            'apply_tax' => $variant['taxable'] ?? false,
        ];

        $tax = Helper::getSettingByKey('iva', 0);
        $initialRecurrenceDiscount = Helper::getSettingByKey('initial_recurrence_code', null);
        $recurrenceDiscount = Helper::getSettingByKey('recurrence_code', null);
        $fixedDiscount = Helper::getSettingByKey('fixed_discount', null);

        $fixedDiscountData = Helper::getDiscountByCode($fixedDiscount);
        $promoInitialRecurrence = Helper::getDiscountByCode($initialRecurrenceDiscount);
        $promoRecurrence = Helper::getDiscountByCode($recurrenceDiscount);

        $itemsPromoInitial = Helper::calculateDiscountInProducts(
            [$product],
            $promoInitialRecurrence,
            $fixedDiscountData,
            $tax,
        );
        $itemsPromo = Helper::calculateDiscountInProducts([$product], $promoRecurrence, $fixedDiscountData, $tax);

        $dataPromoInitial = reset($itemsPromoInitial);
        $dataPromo = reset($itemsPromo);

        $data = [
            'initial_discount' => Helper::formatDecimal($dataPromoInitial['appliedDiscount']['total_amount'] ?? 0),
            'initial_value' => Helper::formatDecimal($dataPromoInitial['appliedDiscount']['total_amount'] ?? 0),
            'initial_discount_value' => $dataPromoInitial['appliedDiscount']['value'] ?? 0,
            'initial_subtotal' => Helper::formatDecimal($dataPromoInitial['subtotalWithDiscount'] ?? 0),
            'discount' => Helper::formatDecimal($dataPromo['appliedDiscount']['total_amount'] ?? 0),
            'discount_value' => $dataPromo['appliedDiscount']['value'] ?? 0,
            'discount_type' => $dataPromo['appliedDiscount']['value_type'] ?? 0,
            'subtotal' => Helper::formatDecimal($dataPromo['subtotalWithDiscount'] ?? 0),

        ];

        return ServiceResponse::success($data, 'Oferta calculada');
    }

    private function processPaymentForOrder(array $payload, string $userId, array $items): array
    {
        $responseFromPaymentGateway = $this->paymentGatewayService->processPaymentWithToken(
            CreatePayment::fromArray($payload),
        );
        if ($responseFromPaymentGateway->getCustomStatus() !== CustomStatus::SUCCESS) {
            throw new Exception(
                $responseFromPaymentGateway->getMessage() ?? __('custom.payment_order_could_not_be_processed'),
            );
        }
        return [$responseFromPaymentGateway, 'paid'];
    }

    public function getInventoryAvailableByProduct(string $productId): ShopifyResponse
    {
        $responseFromGetProductVariants = $this->getProductVariantsByID($productId);
        if ($responseFromGetProductVariants->hasErrors()) {
            throw new ProductInventoryNotFoundException(
                $responseFromGetProductVariants->getFullErrorMessage(),
                0,
                null,
                __('custom.error_trying_to_get_product_inventory'),
            );
        }
        $inventoryItemIds = collect($responseFromGetProductVariants->getData()['product']['variants']['edges'])
            ->map(function ($edge) {
                return $edge['node']['inventoryItem']['id'] ?? null;
            })->filter()->toArray();
        try {
            return new ShopifyResponse(['data' => ['total' => $this->getInventoryTotal($inventoryItemIds)]]);
        } catch (Exception $e) {
            CustomLog::saveLog(
                'ERROR',
                'Failed to get inventory of product.',
                [
                    'product_id' => $productId,
                    'error_message' => $e->getMessage(),
                ],
            );
            throw $e;
        }
    }

    private function getInventoryTotal(array $inventoryItemIds): int
    {
        if (empty($inventoryItemIds)) {
            return 0;
        }
        $total = 0;
        $chunks = array_chunk($inventoryItemIds, 50);
        foreach ($chunks as $chunk) {
            $idsString = implode(',', array_map(fn ($id) => '"' . $id . '"', $chunk));
            $query = "
                query {
                    nodes(ids: [$idsString]) {
                        ... on InventoryItem {
                            id
                            inventoryLevels(first: 10) {
                                edges {
                                    node {
                                        quantities(names: [\"available\"]) {
                                            name
                                            quantity
                                        }
                                        location {
                                            id
                                            name
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            ";

            try {
                $response = $this->query($query, useAdminApi: true, key: 'nodes');
                $nodes = $response->getData() ?? [];
                foreach ($nodes as $node) {
                    $total += $this->calculateStockByInventoryLevels($node['inventoryLevels']);
                }
            } catch (Exception $e) {
                CustomLog::saveLog(
                    'ERROR',
                    'Failed to get inventory via GraphQL',
                    [
                        'ids' => $chunk,
                        'error_message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ],
                );
            }
        }

        return $total;
    }

    public function getProductsFromCollection(string $collectionId, int $first, ?string $after = null): ShopifyResponse
    {
        $query = 'query getCollectionProducts($id: ID!, $first: Int!, $after: String) {
                  collection(id: $id) {
                    id
                    title
                    products(first: $first, after: $after) {
                      edges {
                        cursor
                        node {
                          id
                          title
                          description
                          totalInventory
                          metafields(first: 20) {
                            edges {
                              node {
                                namespace
                                key
                                value
                                type
                              }
                            }
                          }
                          variants(first: 10) {
                            edges {
                              node {
                                id
                                price
                                compareAtPrice
                                availableForSale
                                inventoryQuantity
                                selectedOptions {
                                  name
                                  value
                                }
                                image {
                                  url
                                }
                                taxable
                              }
                            }
                          }

                        media(first: 10) {
                                    edges {
                                      node {
                                        mediaContentType
                                        alt
                                        ... on MediaImage {
                                          image {
                                            url
                                          }
                                        }
                                        ... on Video {
                                          sources {
                                            url
                                            format
                                            mimeType
                                          }
                                        }
                                        ... on ExternalVideo {
                                          embeddedUrl
                                          host
                                        }
                                        ... on Model3d {
                                          sources {
                                            url
                                            format
                                            mimeType
                                          }
                                        }
                                      }
                                    }
                                  }
                        }
                      }
                      pageInfo {
                        hasNextPage
                        endCursor
                      }
                    }
                  }
                }
                ';

        try {
            return $this->query($query, [
                'id' => $collectionId,
                'first' => $first,
                'after' => $after,
            ], useAdminApi: true);
        } catch (Exception $e) {
            CustomLog::saveLog(
                'ERROR',
                sprintf('Error obteniendo productos de collection %s', $collectionId),
                [
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'trace' => $e->getTraceAsString(),
                ],
            );
            throw $e;
        }
    }

    public function getInventoryAvailableByVariant(string $variantId): ShopifyResponse
    {
        $response = $this->getVariantInformation($variantId);
        if ($response->hasErrors()) {
            throw new ProductInventoryNotFoundException(
                $response->getFullErrorMessage(),
                0,
                null,
                __('custom.error_trying_to_get_product_inventory'),
            );
        }
        try {
            $inventoryLevels = $response->getData()['productVariant']['inventoryItem']['inventoryLevels'] ?? null;
            $total = $this->calculateStockByInventoryLevels($inventoryLevels);
            return new ShopifyResponse([
                'data' => [
                    'total' => $total,
                ],
            ]);
        } catch (Exception $e) {
            CustomLog::saveLog(
                'ERROR',
                'Failed to get inventory of product.',
                [
                    'variant_id' => $variantId,
                    'error_message' => $e->getMessage(),
                ],
            );
            throw $e;
        }
    }

    /**
     * @param array $inventoryLevels
     * @return int
     */
    private function calculateStockByInventoryLevels(array $inventoryLevels): int
    {
        $total = 0;
        foreach ($inventoryLevels['edges'] ?? [] as $edge) {
            foreach ($edge['node']['quantities'] ?? [] as $quantity) {
                if ($quantity['name'] === 'available') {
                    $total += intval($quantity['quantity']);
                }
            }
        }
        return $total;
    }

    public function getProductsImages(ProductIds $productIds): array
    {
        $cacheKey = sprintf('shopify_product_images_%s', implode('_', $productIds->toArray()));

        return Cache::remember($cacheKey, 3600, function () use ($productIds) {
            $shop = config('services.shopify.store_url');
            $accessToken = config('services.shopify.access_token');
            $version = config('services.shopify.api_version', '2023-07');

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
                'Accept' => 'application/json',
            ])->get("{$shop}/admin/api/{$version}/products.json", [
                'ids' => implode(',', $productIds->toArray()),
            ]);

            $images = [];
            if ($response->ok()) {
                $products = $response->json('products');
                foreach ($products as $product) {
                    $images[$product['id']] = $product['images'][0]['src'] ?? null;
                }
            }

            return $images;
        });
    }
}
