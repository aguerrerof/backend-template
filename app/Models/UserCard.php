<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class UserCard extends Model
{
    use SoftDeletes;

    protected $table = 'user_cards';

    protected $fillable = [
        'user_id',
        'token',
        'status',
        'extra_information',
    ];
    protected $appends = [
        'is_expired',
        'card_issuer',
        'card_info',
        'client_name',
    ];

    protected $casts = [
        'extra_information' => 'array',
    ];

    public static function getByUserAndId(string $userId, int $cardId): ?self
    {
        return self::where('user_id', $userId)
            ->where('id', $cardId)
            ->first();
    }

    public function getIsExpiredAttribute(): bool
    {
        $extraInformation = $this->getExtraInformation();
        if (
            isset($extraInformation['informacionTarjeta']['anioExpiracion'])
            && isset($extraInformation['informacionTarjeta']['mesExpiracion'])
        ) {
            $expirationDate = Carbon::createFromDate(
                $extraInformation['informacionTarjeta']['anioExpiracion'],
                $extraInformation['informacionTarjeta']['mesExpiracion'],
                1,
            )->endOfMonth();
            return $expirationDate->lt(Carbon::now());
        }
        return false;
    }

    public function getCardIssuerAttribute(): ?string
    {
        return $this->extra_information['cardIssuer'] ?? null;
    }

    public function getCardInfoAttribute(): ?string
    {
        return $this->extra_information['cardInfo'] ?? null;
    }

    public function getClientNameAttribute(): ?string
    {
        return $this->extra_information['clientName'] ?? null;
    }

    public function getExtraInformation(): ?array
    {
        $attribute = $this->getAttribute('extra_information');
        if (is_null($attribute)) {
            return [];
        }
        if (is_array($attribute)) {
            return $attribute;
        }
        return json_decode($attribute, true);
    }
}
