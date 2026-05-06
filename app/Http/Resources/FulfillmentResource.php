<?php

namespace App\Http\Resources;

use App\Models\Enums\FulfillmentStatus;
use Illuminate\Http\Request;

class FulfillmentResource extends BaseJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statusTranslated = config(
            sprintf('notifications.fulfillment.messages.%s', $this->status),
        );
        return [
            'id' => $this->id,
            'tracking_number' => $this->tracking_number,
            'tracking_url' => $this->tracking_url,
            'tracking_info' => $this->tracking_info['tracking'] ?? $this->tracking_info ?? null,
            'dispatched_at' => $this->dispatched_at,
            'delivered_at' => $this->delivered_at,
            'status' => $this->status,
            'status_translated' => $statusTranslated['body'] ?? null,
            'events' => $this->tracking_info['novedades'] ?? [],
            'progress' => $statusTranslated['progress'] ?? null,
            'steps' => collect(FulfillmentStatus::getSteps())
                ->map(fn ($status, $index)
                    => [
                    'position' => $index,
                    'step' => __('fulfillment-status.' . $status->value),
                ])
                ->values(),
            'actual_step' => FulfillmentStatus::getActualStepByStatus($this->status),
            'logistic_provider' => (new LogisticProviderResource($this->logisticProvider)),
        ];
    }
}
