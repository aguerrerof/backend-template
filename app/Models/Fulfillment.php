<?php

namespace App\Models;

use App\Models\Enums\FulfillmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $order_id
 * @property string $shopify_fulfillment_id
 * @property string $status
 * @property string $tracking_number
 * @property string $tracking_url
 * @property string $logistic_provider_id
 * @property string $user_id
 * @property string $location_name
 * @property ?string $dispatched_at
 * @property ?string $delivered_at
 * @property string $delivery_date
 * @property float $total_weight
 * @property array $tracking_info
 * @property array $line_items
 */
class Fulfillment extends Model
{
    protected $fillable = [
        'order_id',
        'shopify_fulfillment_id',
        'status',
        'tracking_number',
        'tracking_url',
        'logistic_provider_id',
        'user_id',
        'location_name',
        'dispatched_at',
        'delivered_at',
        'tracking_info',
        'line_items',
        'delivery_date',
        'total_weight',
    ];

    protected $casts = [
        'tracking_info' => 'array',
        'line_items' => 'array',
        'dispatched_at' => 'datetime',
        'delivered_at' => 'datetime',
        'delivery_date' => 'datetime',
        'total_weight' => 'float',
    ];

    protected $attributes = [
        'status' => FulfillmentStatus::BOOKED->value,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function logisticProvider(): BelongsTo
    {
        return $this->belongsTo(LogisticProvider::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getLineItems(): array
    {
        if (!isset($this->line_items)) {
            return [];
        }
        return array_map(fn ($item) => (array)$item, $this->line_items);
    }

    public function scopeTrackingNumber(Builder $query, string $trackingNumber): Builder
    {
        return $query->where('tracking_number', '=', $trackingNumber);
    }
}
