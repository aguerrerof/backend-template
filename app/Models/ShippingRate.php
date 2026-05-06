<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $code
 */
class ShippingRate extends Model
{
    use SoftDeletes;

    public const FREE_SHIPPING_CODE = 'FREE-SHIPPING';
    public const DEFAULT_SHIPPING_CODE = 'STANDARD';
    protected $table = 'shipping_rates';
    protected $fillable = [
        'code',
        'identifier',
        'title',
        'price',
    ];

    public static function getDefault(): ?ShippingRate
    {
        return self::query()->where('code', '=', self::DEFAULT_SHIPPING_CODE)->first();
    }

}
