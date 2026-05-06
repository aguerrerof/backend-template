<?php

namespace App\Models;

use App\Helpers\ShopifyHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read int $id,
 * @property string $email,
 * @property string $recurrence_frequency_id,
 * @property array $line_items,
 * @property string $next_charge_date,
 * @property string $previous_charge_date,
 * @property string $notes,
 * @property array $shipping_address,
 * @property string $start_date,
 * @property string $status,
 * @property string $user_id,
 * @property int $user_card_id,
 * @property string $created_at,
 * @property string $updated_at,
 */
class RecurringOrder extends Model
{
    use SoftDeletes;

    protected $table = 'recurring_orders';
    protected $fillable = [
        'email',
        'recurrence_frequency_id',
        'line_items',
        'next_charge_date',
        'previous_charge_date',
        'notes',
        'user_card_id',
        'shipping_address',
        'start_date',
        'status',
        'user_id',
    ];
    protected $casts = [
        'line_items' => 'array',
        'shipping_address' => 'array',
        'next_charge_date' => 'datetime',
        'previous_charge_date' => 'datetime',
        'start_date' => 'datetime',
    ];
    protected $appends = ['user_url'];
    public static function createRecurringOrder(
        string $userId,
        int $frequencyId,
        Carbon $nextChargeDate,
        array $lineItems,
        array $shippingAddress,
        ?int $userCardId,
        ?string $notes,
        string $email,
        ?Carbon $previousChargeDate,
    ) {
        return self::create([
            'user_id' => $userId,
            'status' => 'active',
            'recurrence_frequency_id' => $frequencyId,
            'next_charge_date' => Carbon::parse($nextChargeDate),
            'start_date' => Carbon::now(),
            'line_items' => $lineItems,
            'shipping_address' => $shippingAddress,
            'user_card_id' => $userCardId,
            'previous_charge_date' => $previousChargeDate,
            'notes' => $notes ?? '',
            'email' => $email
        ]);
    }

    public function card(): HasOne
    {
        return $this->hasOne(UserCard::class, 'id', 'user_card_id');
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class, 'recurring_id', 'id');
    }
    public function getShippingAddressAttribute()
    {
        return isset($this->attributes['shipping_address'])
            ? json_decode($this->attributes['shipping_address'])
            : null;
    }

    public function getLineItemsAttribute()
    {
        return isset($this->attributes['line_items'])
            ? json_decode($this->attributes['line_items'])
            : null;
    }

    public function getUserUrlAttribute(): ?string
    {
        return ShopifyHelper::gidToAdminUrl($this->user_id);
    }

    public function getNextChargeDateAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        return Carbon::parse($value)
            ->setTimezone(config('app.timezone'))
            ->format('Y-m-d H:i:s');
    }

    public function getStartDateAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        return Carbon::parse($value)
            ->setTimezone(config('app.timezone'))
            ->format('Y-m-d H:i:s');
    }

    public function frequency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(RecurrenceFrequency::class, 'recurrence_frequency_id');
    }

    public function getUnpaidOrder(): ?Order
    {
        return $this->hasOne(Order::class, 'recurring_id', 'id')
                    ->where('order->financial_status', '<>', Order::STATUS_PAID)
                    ->first();
    }
}
