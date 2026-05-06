<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsedDiscount extends Model
{
    protected $table = 'used_discount_codes';
    protected $fillable = [
        'user_id',
        'discount_id',
        'used_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'used_at' => 'datetime',
    ];

    public static function findByUserAndCode(string $userId, int $discountCodeId): ?self
    {
        return self::where('user_id', $userId)
            ->where('discount_id', $discountCodeId)
            ->first();
    }

    public static function createUsage(string $userId, int $discountCodeId, array $metadata = []): self
    {
        return self::create([
            'user_id' => $userId,
            'discount_id' => $discountCodeId,
            'used_at' => now(),
            'metadata' => $metadata,
        ]);
    }
}
