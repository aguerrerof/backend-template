<?php

namespace App\Http\Resources;

use App\Helpers\RecurringOrderHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RecurringOrderResource extends BaseJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $unpaidOrder = $this->getUnpaidOrder();
        return [
            'id' => $this->id,
            'email' => $this->email,
            'frequency' => $this->frequency?->name,
            'line_items' => $this->line_items,
            'next_charge_date' => is_null($unpaidOrder)
                ? $this->next_charge_date
                : null ,
            'next_delivery_date' => is_null($unpaidOrder)
                ? RecurringOrderHelper::calculateNextDeliveryDate(
                    Carbon::parse($this->next_charge_date)
                )
                : null ,
            'notes' => $this->notes,
            'payment_method_id' => $this->payment_method_id,
            'shipping_address' => $this->shipping_address,
            'shopify_customer_id' => $this->shopify_customer_id,
            'start_date' => $this->start_date,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'card' => !is_null($this->card)
                ? $this->card->toArray($request)
                : null,
            'has_unpaid_order' => !is_null($unpaidOrder) ? true : false,
            'unpaid_order_id' => $unpaidOrder?->id
        ];
    }
}
