<?php

namespace App\Helpers;

class WeightHelper
{
    public static function kgToGrams(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int)round(((float)$value) * 1000);
    }
}
