<?php

namespace App\Console\Commands;

use App\Exceptions\CardInsufficientFundsException;
use App\Helpers\CustomLog;
use App\Helpers\NotificationHelper;
use App\Helpers\RecurringOrderHelper;
use App\Models\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\PaymentGateway\CreatePayment;
use App\Models\PaymentGateway\Response\CustomStatus;
use App\Models\RecurringOrder;
use App\Models\ShippingRate;
use App\Services\PaymentGateways\PaymentGatewayService;
use App\Services\Shop\Helper;
use App\Services\Shop\ShopService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessRecurringCharges extends Command
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const DESCRIPTION_PAYMENT = 'Recurring payment';
    public const FINANCIAL_STATUS_FOR_PAID_ORDERS = 'paid';
    public const ORDER_CREATED_STATUS = 'order_created';
    public const INITIAL_STATUS = 'pending';
    public const SOURCE = 'Cron process recurring charges';
    public $tries = 3;
    public $backoff = [10, 30, 60];
    public $timeout = 120;
    protected $signature = 'app:process-recurring-charges {--date=}';
    protected $description = 'Processes scheduled recurring charges for the day.';

    public function __construct(
        private readonly PaymentGatewayService $paymentGatewayService,
        private readonly ShopService           $shopService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dateArgument = $this->option('date');
        $targetDate = $dateArgument
            ? Carbon::parse($dateArgument)
            : Carbon::now('UTC');

        $startOfDay = $targetDate->copy()->startOfDay();
        $endOfDay = $targetDate->copy()->addDay()->startOfDay();

        $this->info(
            sprintf(
                'Looking for recurring charges scheduled between %s and %s (America/Lima timezone).',
                $startOfDay->toDateTimeString(),
                $endOfDay->toDateTimeString(),
            ),
        );
        $recurringOrders = RecurringOrder::query()
            ->whereBetween('next_charge_date', [$startOfDay, $endOfDay])
            ->get();

        if ($recurringOrders->isEmpty()) {
            $this->info('No recurring charges scheduled for today.');
            return 0;
        }

        $processedCount = 0;
        $failedCount = 0;
        /** @var RecurringOrder $recurringOrder */
        foreach ($recurringOrders as $recurringOrder) {
            $shopifyOrderId = $recurringOrder->id;
            try {
                if ($this->hasPendingOrder($recurringOrder)) {
                    $this->info(
                        sprintf(
                            'Recurring order %s has an order with pending status, skipping.',
                            $recurringOrder->id,
                        ),
                    );
                    continue;
                }
                if (is_null($recurringOrder->shipping_address)) {
                    $this->info(
                        sprintf(
                            'Recurring order does not have shipping address info %s',
                            $recurringOrder->id,
                        ),
                    );
                    continue;
                }
                $shippingRate = Helper::getShippingRate();
                [, , , , , $subtotal, $subtotalIVA, $lineItems] = $this->getListProductsWithActualPriceInformation(
                    $recurringOrder,
                    $shippingRate,
                );
                $tax = Helper::getSettingByKey('iva', 0);
                $responseCreationOrder = $this->shopService->createOrderInShopify(
                    $recurringOrder->user_id,
                    $lineItems,
                    (array)$recurringOrder->shipping_address,
                    $recurringOrder->email,
                    $shippingRate,
                    $tax,
                    self::INITIAL_STATUS,
                );
                if (is_null($responseCreationOrder)) {
                    $this->info(
                        sprintf(
                            'Error trying to create order with recurring order %s, response from method %s',
                            $recurringOrder->id,
                            json_encode($responseCreationOrder),
                        ),
                    );
                    continue;
                }
                $orderEloquentModel = $this->registerOrder(
                    $recurringOrder,
                    $responseCreationOrder,
                );
                $orderEloquentModel->setTotalsAttribute([
                    'subtotal' => $subtotal,
                    'subtotal_with_tax' => $subtotalIVA,
                ])->saveOrFail();
                $shopifyOrderId = $responseCreationOrder['id'];
                $responseFromPaymentGateway = $this->paymentGatewayService->processPaymentWithToken(
                    new CreatePayment(
                        $subtotal,
                        $subtotalIVA,
                        $recurringOrder->card->token,
                        self::DESCRIPTION_PAYMENT,
                        false,
                        false,
                        0,
                        '',
                    ),
                );
                if ($responseFromPaymentGateway->getCustomStatus() === CustomStatus::SUCCESS) {
                    OrderPayment::create([
                        'order_id' => $orderEloquentModel->id,
                        'due_date' => Carbon::now('UTC'),
                        'status' => $responseFromPaymentGateway->getCustomStatus()->name,
                        'message' => $responseFromPaymentGateway->getMessage(),
                        'details' => $responseFromPaymentGateway->getDetails(),
                        'processed_at' => \Carbon\Carbon::now('UTC'),
                    ]);
                } else {
                    NotificationHelper::sendToUserDevices(
                        $recurringOrder->user_id,
                        __('recurring-orders.error_trying_to_process_recurring_order_title'),
                        $responseFromPaymentGateway->getMessage(),
                        [
                            'type' => 'ORDER_DETAIL',
                            'route' => 'orderDetail',
                            'id' => (string)$orderEloquentModel->id,
                        ],
                    );
                    $this->info(
                        sprintf(
                            'There was error from payment gateway trying to process payment, order %s, message %s , response %s',
                            $recurringOrder->id,
                            $responseFromPaymentGateway->getMessage(),
                            json_encode($responseFromPaymentGateway->getDetails()),
                        ),
                    );
                    continue;
                }
                $this->shopService->updateOrderFinancialStatus(
                    $shopifyOrderId,
                    self::FINANCIAL_STATUS_FOR_PAID_ORDERS,
                );
                $orderEloquentModel
                    ->setStatus(OrderStatus::PAID_STATUS->value)
                    ->saveOrFail();
                OrderPayment::create([
                    'order_id' => $orderEloquentModel->id,
                    'status' => $responseFromPaymentGateway->getCustomStatus()->name,
                    'message' => $responseFromPaymentGateway->getMessage(),
                    'details' => $responseFromPaymentGateway->getDetails(),
                    'processed_at' => Carbon::now('UTC'),
                ]);

                $recurringOrder->previous_charge_date = $recurringOrder->next_charge_date;

                $nextChargeDateByFrequency = RecurringOrderHelper::calculateNextChargeDateByFrequency(
                    Carbon::parse($recurringOrder->next_charge_date),
                    (int)$recurringOrder->frequency->value
                );
                $recurringOrder->next_charge_date = $nextChargeDateByFrequency;
                $recurringOrder->saveOrFail();

                $this->info(
                    sprintf(
                        'Successfully processed recurring charge for order: %s. Next charge date set to %s.',
                        $shopifyOrderId,
                        $nextChargeDateByFrequency->toDateTimeString(),
                    ),
                );
                $processedCount++;
            } catch (CardInsufficientFundsException $exception) {
                Log::error($exception->getUserMessage(), $exception->getTrace());
                NotificationHelper::sendToUserDevices(
                    $recurringOrder->user_id,
                    __('recurring-orders.error_trying_to_process_recurring_order_title'),
                    __('recurring-orders.error_trying_to_process_recurring_order_message'),
                    [
                        'type' => 'ORDER_DETAIL',
                        'route' => 'orderDetail',
                        'id' => (string)$orderEloquentModel->id ?? $recurringOrder->id,
                    ],
                );
                $failedCount++;
            } catch (Throwable $e) {
                Log::error($e->getMessage(), $e->getTrace());
                $this->error(
                    sprintf(
                        'Failed to process recurring charge for order %s: %s %s',
                        $shopifyOrderId,
                        $e->getMessage(),
                        $e->getLine(),
                    ),
                );
                $failedCount++;
            }
        }

        $this->info(
            sprintf(
                'Finished processing recurring charges. Processed: %d, Failed: %d.',
                $processedCount,
                $failedCount,
            ),
        );
        return $failedCount > 0 ? 1 : 0;
    }

    private function getProductsInformationFromShopifyAPI(RecurringOrder $recurringOrder): array
    {
        if (empty($recurringOrder->line_items)) {
            return [];
        }

        $variantIds = array_map(fn ($lineItem) => $lineItem->variantId, $recurringOrder->line_items);
        $variantIds = array_filter($variantIds);
        $variantIds = array_unique($variantIds);

        if (empty($variantIds)) {
            return [];
        }

        try {
            $response = $this->shopService->query(
                'query getProductVariantsByIds($ids: [ID!]!) {
                  nodes(ids: $ids) {
                    ... on ProductVariant {
                      id
                      price {
                        amount
                        currencyCode
                      }
                      taxable
                      compareAtPrice {
                        amount
                        currencyCode
                      }
                    }
                  }
                }',
                [
                    'ids' => $variantIds,
                ],
            );

            if (!$response->isSuccess()) {
                CustomLog::saveLog('ERROR', 'Shopify API response missing or invalid "nodes" data.', [
                    'document_id' => $recurringOrder->id,
                    'response' => $response,
                ]);
                throw new Exception('Invalid response structure from Shopify API.');
            }
            CustomLog::saveLog('INFO', 'Response from Shopify API response', [
                'ids' => $variantIds,
                'response' => $response->getData(),
            ]);
            return $response->getData()['nodes'];
        } catch (Throwable $e) {
            CustomLog::saveLog('ERROR', 'Error fetching product information from Shopify API.', [
                'document_id' => $recurringOrder->id,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception('Failed to fetch product information from Shopify API.', 0, $e);
        }
    }

    public function getListProductsFromShopify(RecurringOrder $recurringOrder): array
    {
        $productsFromShopifyAPI = $this->getProductsInformationFromShopifyAPI($recurringOrder);
        $shopifyProductsMap = [];
        foreach ($productsFromShopifyAPI as $product) {
            $shopifyProductsMap[$product['id']] = $product;
        }
        return $shopifyProductsMap;
    }

    private function getListProductsWithActualPriceInformation(
        RecurringOrder $recurringOrder,
        ?ShippingRate  $shippingRate,
    ): array {
        $currentLineItems = $recurringOrder->line_items;
        $productsFromShopify = $this->getListProductsFromShopify($recurringOrder);
        $updatedLineItems = [];
        foreach ($currentLineItems as $lineItem) {
            $lineItem = (array)$lineItem;
            $newInformationLineItem = $lineItem;
            $price = (float)($lineItem['price'] ?? 0.0);
            $taxable = $lineItem['taxable'] ?? false;

            if (isset($lineItem['variantId']) && isset($productsFromShopify[$lineItem['variantId']])) {
                $productInfo = $productsFromShopify[$lineItem['variantId']];
                if (isset($productInfo['price']['amount'])) {
                    $price = (float)$productInfo['price']['amount'];
                }
                if (isset($productInfo['taxable'])) {
                    $taxable = (bool)$productInfo['taxable'];
                }
            }
            $newInformationLineItem['price'] = $price;
            $newInformationLineItem['applyTax'] = $taxable;
            $newInformationLineItem['applyDiscount'] = true;
            $updatedLineItems[] = $newInformationLineItem;
        }
        return $this->shopService->preparePayloadToCreateOrder(
            $shippingRate->code,
            $recurringOrder->user_id,
            $updatedLineItems,
        );
    }

    private function hasPendingOrder(RecurringOrder $recurringOrder): bool
    {
        if ($recurringOrder
            ->orders()
            ->where('order->financial_status', '=', Order::STATUS_PENDING)
            ->exists()
        ) {
            return true;
        }
        return false;
    }

    private function registerOrder(
        RecurringOrder $recurringOrder,
        array          $responseCreationOrder
    ): Order {
        $orderEloquentModel = $this->shopService->createOrderInDatabase(
            $recurringOrder->user_id,
            $responseCreationOrder['id'],
            self::SOURCE,
            $recurringOrder->id,
            $recurringOrder->user_card_id,
            $responseCreationOrder,
        );
        $title = __('orders.new_order_created_from_recurring_order_title');
        $body = __('orders.new_order_created_from_recurring_order_body');
        NotificationHelper::sendToUserDevices(
            $orderEloquentModel->user_id,
            $title,
            $body,
            [
                    'type' => 'ORDER_DETAIL',
                    'route' => 'orderDetail',
                    'id' => (string)$orderEloquentModel->id,
                ]
        );
        return $orderEloquentModel;
    }
}
