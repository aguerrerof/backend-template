<?php

namespace App\Http\Resources;

use App\Models\Enums\FulfillmentStatus;
use App\Models\Enums\OrderStatus;
use Illuminate\Http\Request;

class OrderResource extends BaseJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activeFulfillment = $this->active_fulfillment;
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'shopify_order_id' => $this->shopify_order_id,
            'source' => $this->source,
            'recurring_id' => $this->recurring_id,
            'notes' => $this->notes,
            'created_at_shopify' => $this->created_at_shopify,
            'order' => json_encode($this->order, JSON_PRETTY_PRINT),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'card' => !is_null($this->card)
                ? $this->card->toArray($request)
                : null,
            'fulfillment' => !is_null($activeFulfillment)
                ? new FulfillmentResource($activeFulfillment)
                : $this->getDefault(),
            'status' => $this->status?->value,
            'extra' => [
                'message' => !is_null($this->status)
                    ? $this->getCustomMessageByStatus($this->status)
                    : null,
            ],
        ];
    }

    private function getCustomMessageByStatus(OrderStatus $status): ?string
    {
        return match ($status) {
            OrderStatus::PENDING_STATUS => __('order-errors.payment_could_not_be_processed'),
            default => null,
        };
    }

    private function getDefault(): array
    {
        return [
            'steps' => collect(FulfillmentStatus::getSteps())
                ->map(fn ($status, $index)
                    => [
                    'position' => $index,
                    'step' => __('fulfillment-status.' . $status->value),
                ])
                ->values(),
            'actual_step' => FulfillmentStatus::getDefaultStep(),
            'status' => FulfillmentStatus::getDefault(),
        ];
    }
}
