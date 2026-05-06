<?php

namespace App\Helpers;

use App\Models\CustomerBillingInformation;
use App\Models\Order;
use App\Models\RecurrenceFrequency;
use App\Models\RecurringOrder;
use Carbon\Carbon;
use Throwable;

class OrderHelper
{
    public static function handleRecurringOrders(
        array $items,
        array $address,
        int $userCardId,
        string $email,
        string $shopifyCustomerId,
    ): array {
        $recurringIds = [];
        $today = Carbon::now();

        try {
            $groupedItems = collect($items)->groupBy(function ($item) {
                return $item['isRecurrence'] ? $item['frequency'] : null;
            })->filter();

            foreach ($groupedItems as $freq => $itemsRecurrence) {
                $frequencyData = RecurrenceFrequency::getFrequency($freq);

                if (is_null($frequencyData)) {
                    CustomLog::saveLog(
                        'ERROR',
                        'Frecuencia no encontrada para recurrencia: ' . $freq,
                        [],
                    );
                    continue;
                }

                $nextChargeDate = $today->copy()->addDays((int)$frequencyData->value);

                $recurringLineItems = collect($itemsRecurrence)->map(function ($item) {
                    $price = isset($item['appliedDiscount'])
                        ? number_format($item['price'] - $item['appliedDiscount']['amount'], 2, '.', '')
                        : number_format($item['price'], 2, '.', '');

                    return [
                        'variantId' => $item['variantId'],
                        'quantity' => $item['quantity'],
                        'requiresShipping' => true,
                        'taxable' => $item['applyTax'] ?? false,
                        'price' => $price,
                        'title' => $item['title'],
                        'applyDiscount' => true,
                        'imageUrl' => $item['imageUrl'] ?? null,
                    ];
                })->toArray();

                $recurringIds = array_merge(
                    $recurringIds,
                    array_map(fn ($i) => (string)$i['variantId'], $recurringLineItems),
                );

                RecurringOrder::createRecurringOrder(
                    $shopifyCustomerId,
                    $frequencyData->id,
                    $nextChargeDate,
                    $recurringLineItems,
                    $address,
                    $userCardId,
                    'recurrence from app',
                    $email,
                    Carbon::now()
                );
            }
        } catch (Throwable $e) {
            CustomLog::saveLog(
                'ERROR',
                'Error al crear órdenes recurrentes',
                [
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'trace' => $e->getTraceAsString(),
                ],
            );
            throw $e;
        }

        return $recurringIds;
    }

    public static function getDefaultCustomerBillingInformation(Order $order): ?CustomerBillingInformation
    {
        return CustomerBillingInformation::query()
                 ->where('user_id', '=', (string) $order->order->customer->admin_graphql_api_id)
                 ->where('is_default', '=', true)
                 ->first();
    }
}
