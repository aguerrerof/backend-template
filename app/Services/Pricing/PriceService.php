<?php

namespace App\Services\Pricing;

use App\Helpers\CustomLog;
use App\Helpers\ServiceResponse;
use App\Models\ShoppingCart;
use App\Models\UsedDiscount;
use App\Services\Shop\Helper;
use App\Services\Shop\ShopService;
use Illuminate\Http\Request;

class PriceService implements PricingService
{
    public function __construct(
        private readonly ShopService $shopService,
    ) {
    }

    public function generateSubtotal(Request $request): array
    {
        try {

            $products = $request->input('products');
            $uid = $request->input('uid');

            if (is_null($products)) {
                return ServiceResponse::error('No se encontraron productos', 'No products found');
            }

            $tax = Helper::getSettingByKey('iva', 0);
            $initialRecurrenceDiscount = Helper::getSettingByKey('initial_recurrence_code', null);
            $recurrenceDiscount = Helper::getSettingByKey('recurrence_code', null);
            $fixedDiscount = Helper::getSettingByKey('fixed_discount', null);
            $minAmountFreeDelivery = Helper::getSettingByKey('min_amount_free_delivery', null);

            $fixedDiscountData = Helper::getDiscountByCode($fixedDiscount);
            $promoInitialRecurrence = Helper::getDiscountByCode($initialRecurrenceDiscount);
            $promoRecurrence = Helper::getDiscountByCode($recurrenceDiscount);
            $isInitialRecurrenceUsed = is_null($uid) ? false : UsedDiscount::findByUserAndCode($uid, $promoInitialRecurrence->id);
            $discountData = isset($isInitialRecurrenceUsed) ? $promoRecurrence : $promoInitialRecurrence;

            $items = Helper::calculateDiscountInProducts($products, $discountData, $fixedDiscountData, $tax);
            $subtotal = 0.0;
            foreach ($items as $item) {
                $price = isset($item['appliedDiscount'])
                    ? Helper::formatDecimal($item['price'] - ($item['appliedDiscount']['amount'] ?? 0))
                    : Helper::formatDecimal($item['price']);
                $subtotal += ($price * $item['quantity']);
            }

            return ServiceResponse::success([
                    'subtotal' => round($subtotal, 2),
                    'minAmountFreeDelivery' => $minAmountFreeDelivery,
                    'remainingAmount' => $subtotal >= $minAmountFreeDelivery
                        ? 0
                        : ($minAmountFreeDelivery - $subtotal),
                    'discounts' => [
                        'initial_recurrence' => $promoInitialRecurrence->value,
                        'recurrence' => $promoRecurrence->value,
                    ],
                ], 'Totales obtenidos.');
        } catch (\Exception $e) {

            CustomLog::saveLog(
                'ERROR',
                'Fallo en el proceso de obtener los totales',
                [
                    'error_message' => $e->getMessage(),
                ]
            );
            return ServiceResponse::error('Ocurrió un problema obtener los totales.', $e->getMessage());
        }
    }

    public function generateCheckout(?string $shippingCode, string $shopifyCustomerId): array
    {
        try {
            $tax = Helper::getSettingByKey('iva', 0);
            $initialRecurrenceDiscount = Helper::getSettingByKey('initial_recurrence_code', null);
            $recurrenceDiscount = Helper::getSettingByKey('recurrence_code', null);
            $fixedDiscount = Helper::getSettingByKey('fixed_discount', null);
            $shippingRate = Helper::getShippingRate($shippingCode, null);

            $fixedDiscountData = Helper::getDiscountByCode($fixedDiscount);
            $promoInitialRecurrence = Helper::getDiscountByCode($initialRecurrenceDiscount);
            $promoRecurrence = Helper::getDiscountByCode($recurrenceDiscount);
            $isInitialRecurrenceUsed = isset($promoInitialRecurrence)
                ? UsedDiscount::findByUserAndCode($shopifyCustomerId, $promoInitialRecurrence->id)
                : null;

            $discountData = isset($isInitialRecurrenceUsed)
                ? $promoRecurrence
                : $promoInitialRecurrence;
            $cartItems = ShoppingCart::getItemsByUserId($shopifyCustomerId);
            $ids = $cartItems->pluck('variant_id')->toArray();
            $responseVariants = $this->shopService->getVariants($ids);
            if (is_null($responseVariants['data'])) {
                throw new \Exception($responseVariants['devError'] ?? 'No se encontraron variantes en el carrito');
            }
            $variants = $responseVariants['data']['nodes'];
            $updatedCartItems = $cartItems->map(function ($cartItem) use ($variants) {
                $variantId = $cartItem->variant_id;
                $variant = collect($variants)->firstWhere('id', $variantId);

                if ($variant) {
                    return array_merge(
                        $cartItem->toArray(),
                        [
                            'price'   => $variant['price'],
                            'apply_tax' => $variant['taxable'],
                        ]
                    );
                }
                return $cartItem->toArray();
            });

            $items = Helper::calculateDiscountInProducts(
                $updatedCartItems->toArray(),
                $discountData,
                $fixedDiscountData,
                $tax
            );
            $subtotal = 0.0;
            $subtotalIVA = 0.0;
            $totalTax = 0.0;
            $totalQuantity = 0;
            $totalDiscount = 0.0;

            foreach ($items as &$item) {
                $totalDiscount += $item['appliedDiscount']['total_amount'] ?? 0.0;

                if (isset($item['applyTax']) && $item['applyTax']) {
                    $subtotalIVA += $item['subtotal'] ?? 0.0;
                } else {
                    $subtotal += $item['subtotal'] ?? 0.0;
                }

                $totalTax += $item['tax'] ?? 0.0;
                $totalQuantity += $item['quantity'] ?? 0;
                $item['stock'] = $this->shopService
                    ->getInventoryAvailableByVariant($item['variantId'])
                    ->getData()['total'] ?? 0;
            }

            $totalItemsSubtotal = $subtotal + $subtotalIVA;
            $deliveryCharge = isset($shippingRate['price']) ? floatval($shippingRate['price']) : 0.0;
            $delivery = $totalItemsSubtotal >= 100.0
                ? 0.0
                : $deliveryCharge;
            $deliveryCostIVA = $delivery * ($tax / 100);
            $totalTax += $deliveryCostIVA;

            $subtotalWithDiscount = $totalItemsSubtotal - $totalDiscount + $delivery;
            $totalWithDeliveryAndDiscount = $subtotalWithDiscount + $totalTax;

            return ServiceResponse::success([
                'items' => $items,
                'totalQuantity' => $totalQuantity,
                'subtotal' => round($totalItemsSubtotal, 2),
                'subtotal0' => round($subtotal, 2),
                'subtotalIVA' => round($subtotalIVA, 2),
                'discount' => round($totalDiscount, 2),
                'totalTax' => round($totalTax, 2),
                'deliveryCost' => $delivery,
                'subtotalWithDiscount' => round($subtotalWithDiscount, 2),
                'total' => round($totalWithDeliveryAndDiscount, 2),
            ], 'checkout generado.');

        } catch (\Exception $e) {
            CustomLog::saveLog(
                'ERROR',
                'Error al generar el checkout',
                [
                    'error_message' => $e->getMessage(),
                ]
            );
            return ServiceResponse::error('Fallo al obtener las variantes', $e->getMessage());
        }
    }

}
