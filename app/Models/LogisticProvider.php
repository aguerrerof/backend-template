<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $api_url
 * @property array $credentials
 * @property array $config
 * @property bool $can_cancel_orders
 */
class LogisticProvider extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'contact_email',
        'contact_phone',
        'api_url',
        'credentials',
        'config',
        'max_total_weight_grams',
        'can_cancel_orders',
    ];

    protected $casts = [
        'credentials' => 'array',
        'config' => 'array',
        'max_total_weight_grams' => 'integer',
        'can_cancel_orders' => 'boolean',
    ];

    public function cityRules(): HasMany
    {
        return $this->hasMany(CityLogisticProviderRule::class);
    }

}
