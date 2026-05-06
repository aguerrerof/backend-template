<?php

namespace App\Services\Logistics;

use App\Exceptions\FulfillmentAlreadyAssignedException;
use App\Models\Enums\FulfillmentStatus;
use App\Models\Fulfillment;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FulfillmentCreationService
{
    public function __construct(
        private readonly FulfillmentShipmentService $shipmentService,
    ) {
    }

    /**
     * Create the fulfillment and the shipment in a single transaction.
     *
     * @param array<string, mixed> $validatedPayload
     * @param array<int, mixed> $rawLineItems
     * @return array{fulfillment: Fulfillment, provider_response: array<array-key, mixed>}
     */
    public function createWithShipment(array $validatedPayload, int $userId, array $rawLineItems, float $totalWeight): array
    {
        $orderId = $validatedPayload['order_id'] ?? null;
        if (!$orderId) {
            throw new RuntimeException('order_id missing from payload.');
        }

        $order = Order::query()->findOrFail((int)$orderId);
        $hasActiveFulfillment = $order
            ->fulfillments()
            ->where('status', '<>', FulfillmentStatus::CANCELLED)
            ->count() > 1 ? true : false;

        if ($hasActiveFulfillment) {
            throw new FulfillmentAlreadyAssignedException(__('custom.fulfillment_already_assigned'));
        }

        $validatedPayload['user_id'] = $userId;
        $validatedPayload['line_items'] = array_map(
            static fn ($item) => is_string($item) ? json_decode($item, true) : $item,
            $rawLineItems,
        );
        $validatedPayload['total_weight'] = $totalWeight;

        return DB::transaction(function () use ($validatedPayload) {
            $fulfillment = Fulfillment::query()->create($validatedPayload);
            $providerResponse = $this->shipmentService->createShipment($fulfillment);

            return [
                'fulfillment' => $fulfillment,
                'provider_response' => $providerResponse,
            ];
        });
    }
}
