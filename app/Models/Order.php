<?php

namespace App\Models;

use App\Models\Enums\FulfillmentStatus;
use App\Models\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $order
 * @property string $user_id
 */
class Order extends Model
{
    use SoftDeletes;

    public const STATUS_PAID = 'paid';
    public const STATUS_PENDING = 'pending';
    protected $table = 'orders';
    protected $fillable = [
        'user_id',
        'shopify_order_id',
        'source',
        'user_card_id',
        'recurring_id',
        'notes',
        'created_at_shopify',
        'order',
    ];

    protected $casts = [
        'order' => 'array',
    ];

    public static function createOrder(array $data): self
    {
        return self::create([
            'user_id' => $data['user_id'],
            'shopify_order_id' => $data['shopify_order_id'],
            'source' => $data['source'] ?? null,
            'user_card_id' => $data['user_card_id'] ?? null,
            'recurring_id' => $data['recurring_id'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_at_shopify' => $data['created_at_shopify'] ?? null,
            'order' => $data['order'],
        ]);
    }

    public function getOrderAttribute()
    {
        return isset($this->attributes['order'])
            ? json_decode($this->attributes['order'])
            : null;
    }

    public function card(): HasOne
    {
        return $this->hasOne(UserCard::class, 'id', 'user_card_id')->withTrashed();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class, 'order_id', 'id');
    }

    public function getLineItems(): array
    {
        if (!isset($this->order->line_items)) {
            return [];
        }
        return array_map(fn ($item) => (array)$item, $this->order->line_items);
    }

    public function fulfillments(): HasMany
    {
        return $this->hasMany(Fulfillment::class);
    }

    public function getActiveFulfillmentAttribute()
    {
        return $this
            ->fulfillments()
            ->where('status', '<>', FulfillmentStatus::CANCELLED)
            ->latest()
            ->first();
    }

    public function getWeightTotalsAttribute(): array
    {
        $totalWeight = 0;
        $totalItems = 0;
        if (isset($this->order->line_items)) {
            foreach ($this->order->line_items as $lineItem) {
                if (!isset($lineItem->grams) || !isset($lineItem->quantity)) {
                    continue;
                }
                $totalWeight += ((float)$lineItem->grams * $lineItem->quantity);
                $totalItems += (int)$lineItem->quantity;
            }
        }
        return ['weight' => $totalWeight, 'quantity' => $totalItems];
    }

    public function getStatusAttribute(): ?OrderStatus
    {
        return isset($this->order->financial_status)
            ? OrderStatus::tryFrom($this->order->financial_status)
            : null;
    }

    public function setTotalsAttribute(array $totals): self
    {
        $order = $this->order;
        $order->totals = $totals;
        $this->order = $order;
        return $this;
    }

    public function setStatus(string $status): self
    {
        $order = $this->order;
        $order->financial_status = $status;
        $this->order = $order;
        return $this;
    }
}
