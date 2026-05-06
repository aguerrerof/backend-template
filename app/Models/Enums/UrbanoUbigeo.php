<?php

namespace App\Models\Enums;

enum UrbanoUbigeo: string
{
    case AZUAY_CUENCA_CUENCA = '010150';
    case GUAYAS_GUAYAQUIL_GUAYAQUIL = '090150';
    case PICHINCHA_QUITO_QUITO = '170150';

    public static function fromLocation(?string $province, ?string $canton, ?string $locality): ?self
    {
        $key = self::normalize($province) . '|' . self::normalize($canton) . '|' . self::normalize($locality);

        return match ($key) {
            'AZUAY|CUENCA|CUENCA' => self::AZUAY_CUENCA_CUENCA,
            'GUAYAS|GUAYAQUIL|GUAYAQUIL' => self::GUAYAS_GUAYAQUIL_GUAYAQUIL,
            'PICHINCHA|QUITO|QUITO' => self::PICHINCHA_QUITO_QUITO,
            default => null,
        };
    }

    public static function fromCity(?string $city): ?self
    {
        return match (self::normalize($city)) {
            'CUENCA' => self::AZUAY_CUENCA_CUENCA,
            'GUAYAQUIL' => self::GUAYAS_GUAYAQUIL_GUAYAQUIL,
            'QUITO' => self::PICHINCHA_QUITO_QUITO,
            default => null,
        };
    }

    private static function normalize(?string $value): string
    {
        return strtoupper(trim((string)$value));
    }
}
