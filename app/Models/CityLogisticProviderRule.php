<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CityLogisticProviderRule extends Model
{
    private const EXCLUDED_CITIES_FOR_INITIAL_LOAD = ['Guayaquil'];

    protected $fillable = ['city', 'logistic_provider_id', 'is_default'];

    public static function preloadDefaults()
    {
        return self::query()
            ->where('is_default', true)
            ->whereNotIn('city', self::EXCLUDED_CITIES_FOR_INITIAL_LOAD)
            ->value('logistic_provider_id');
    }

    public function logisticProvider(): BelongsTo
    {
        return $this->belongsTo(LogisticProvider::class);
    }
}
