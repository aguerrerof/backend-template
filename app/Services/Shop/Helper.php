<?php

namespace App\Services\Shop;

use App\Models\Discount;
use App\Models\Setting;
use App\Models\ShippingRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Helper
{
    public static function getSettingByKey(string $key, $default = null)
    {
        return Cache::remember("setting_{$key}", 86400, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();
            return $setting ? $setting->cast_value : $default;
        });
    }

    public static function inferMetafieldType(string $namespace, string $key, $value): array
    {
        $inferredType = 'unknown';
        $parsedValue = $value;

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (is_array($decoded)) {
                    $inferredType = 'json_list';
                    $parsedValue = $decoded;
                } else {
                    $inferredType = 'string';
                }
            } else {
                $inferredType = 'string';
                Log::info("Parsing JSON para metafield {$namespace}.{$key}: " . json_last_error_msg());
            }
        } elseif (is_int($value)) {
            $inferredType = 'integer';
        }

        return [
            'inferredType' => $inferredType,
            'parsedValue' => $parsedValue,
        ];
    }

    public static function getShippingRate(?string $shippingCode = null): ?ShippingRate
    {
        if (is_null($shippingCode)) {
            return ShippingRate::getDefault();
        }
        return ShippingRate::query()->where('code', $shippingCode)->first();
    }

    public static function getDiscountByCode(string $code): ?Discount
    {
        return Discount::query()->where('code', '=', $code)->first();
    }

    public static function formatDecimal($value, $decimals = 2): string
    {
        return number_format((float)$value, $decimals, '.', '');
    }

    public static function exceedsMaxValue($cartItem, $promoData, $maxVal): bool
    {
        if (!$promoData || ($promoData['value_type'] ?? null) !== 'percentage' || !isset($promoData['value']) || $maxVal == 0) {
            return false;
        }

        $quantity = isset($cartItem['quantity']) ? floatval($cartItem['quantity']) : 0;
        $price = isset($cartItem['price']) ? floatval($cartItem['price']) : 0;
        $itemSubtotal = $price * $quantity;

        $discount = $itemSubtotal * (($promoData['value'] / 100) * -1);

        return $discount > $maxVal;
    }

    public static function calculateDiscountInProducts(
        array $products,
        Discount $discountData,
        Discount $fixedDiscountData,
        string $tax,
    ): array {
        $fixedAmount = isset($fixedDiscountData['value'])
            ? (floatval($fixedDiscountData['value']) * -1)
            : 0;

        $items = [];

        foreach ($products as $cartItem) {
            $quantity = $cartItem['quantity'] ?? 0;
            if ($quantity <= 0) {
                continue;
            }

            $price = $cartItem['price'] ?? 0;
            $applyDiscount = $cartItem['apply_discount'] ?? $cartItem['applyDiscount'] ?? false;
            $applyTax = $cartItem['apply_tax'] ?? $cartItem['applyTax'] ?? false;

            $itemSubtotal = $price * $quantity;
            $itemSubtotalWithDiscount = $itemSubtotal;
            $itemTax = 0;
            $discountAmount = 0;
            $appliedDiscount = null;

            if ($applyDiscount) {
                if (self::exceedsMaxValue($cartItem, $discountData, $fixedAmount)) {
                    $itemSubtotalWithDiscount = $itemSubtotal - $fixedAmount;
                    $appliedDiscount = [
                        'description' => $fixedDiscountData['code'] ?? null,
                        'value' => $fixedDiscountData['value'] ?? null,
                        'value_type' => $fixedDiscountData['value_type'] ?? null,
                        'amount' => self::formatDecimal($fixedAmount / $quantity),
                        'total_amount' => self::formatDecimal($fixedAmount),
                        'title' => $fixedDiscountData['title'] ?? null,
                    ];
                } else {
                    if (($discountData['value_type'] ?? null) === 'percentage') {
                        $discountAmount = $itemSubtotal * (($discountData['value'] ?? 0) / 100 * -1);
                        $itemSubtotalWithDiscount = $itemSubtotal - $discountAmount;
                    } elseif (($discountData['value_type'] ?? null) === 'fixed_amount') {
                        $discountAmount = floatval($discountData['value'] ?? 0) * -1;
                        $itemSubtotalWithDiscount = $itemSubtotal - $discountAmount;
                    }

                    $appliedDiscount = [
                        'description' => $discountData['code'] ?? null,
                        'value' => $discountData['value'] ?? null,
                        'value_type' => $discountData['value_type'] ?? null,
                        'amount' => Helper::formatDecimal($discountAmount / $quantity),
                        'total_amount' => Helper::formatDecimal($discountAmount),
                        'title' => $discountData['title'] ?? null,
                    ];
                }
            }

            if ($applyTax) {
                $itemTax = $itemSubtotalWithDiscount * ($tax / 100);
            }

            $items[] = [
                'id' => $cartItem['id'] ?? null,
                'title' => $cartItem['title'] ?? null,
                'price' => $price,
                'quantity' => $quantity,
                'applyTax' => $applyTax,
                'imageUrl' => $cartItem['imageUrl'] ?? $cartItem['image_url'] ?? null,
                'addedAt' => $cartItem['addedAt'] ?? $cartItem['added_at'] ?? null,
                'variantId' => $cartItem['variantId'] ?? $cartItem['variant_id'] ?? null,
                'flavor' => $cartItem['flavor'] ?? null,
                'size' => $cartItem['size'] ?? null,
                'subtotal' => $itemSubtotal,
                'tax' => $itemTax,
                'subtotalWithDiscount' => $itemSubtotalWithDiscount,
                'isRecurrence' => $cartItem['isRecurrence'] ?? $cartItem['is_recurrence'] ?? false,
                'appliedDiscount' => $appliedDiscount,
                'applyDiscount' => $applyDiscount,
                'frequency' => $cartItem['frequency'] ?? null,
                'available_recurrence' => $cartItem['available_recurrence'] ?? false,
            ];
        }

        return $items;
    }

    public static function extractNumericId(?string $gid): ?int
    {
        if (!$gid) {
            return null;
        }

        $parts = explode('/', $gid);
        $lastPart = end($parts);

        return is_numeric($lastPart) ? (int)$lastPart : null;
    }

    public static function getCardMask(?string $str): string
    {
        if (!is_string($str)) {
            return '';
        }
        return strlen($str) >= 4 ? substr($str, -4) : $str;
    }

}
