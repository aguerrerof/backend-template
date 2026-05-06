<?php

namespace App\Helpers;

use App\Models\LogisticProvider;
use Illuminate\Support\Facades\Cache;

class LogisticProviderHelper
{
    public static function getApiKey(string $providerCode): ?string
    {
        return Cache::remember("provider_api_key_{$providerCode}", 60 * 24, function () use ($providerCode) {
            $provider = LogisticProvider::where('code', '=', $providerCode)->first();
            if (!$provider) {
                return null;
            };
            return $provider->credentials['api-key'] ?? null;
        });
    }
}
