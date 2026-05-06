<?php

namespace App\Services\Logistics;

use App\Services\Logistics\Providers\LAARProvider;
use App\Services\Logistics\Providers\ProviderInterface;
use App\Services\Logistics\Providers\UrbanoProvider;

class LogisticProviderResolver
{
    public static function resolve(string $code): ?ProviderInterface
    {
        return match ($code) {
            'LAAR' => new LAARProvider(),
            'UHD' => new UrbanoProvider(),
            default => null,
        };
    }
}
