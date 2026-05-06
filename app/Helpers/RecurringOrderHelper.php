<?php

namespace App\Helpers;

use Carbon\Carbon;

class RecurringOrderHelper
{
    private const DELIVERY_DAYS_AFTER_CHARGE = 3;

    public static function calculateNextChargeDateByFrequency(
        Carbon $previousChargeDate,
        int $frequency
    ): Carbon {
        $newDate = $previousChargeDate->startOfDay()
            ->copy()
            ->addDays($frequency);
        $today = Carbon::now()->startOfDay();
        if ($newDate->lessThanOrEqualTo($today)) {
            return $today->copy()->addDays($frequency);
        }

        return $newDate;
    }

    public static function calculateNextDeliveryDate(Carbon $nextDeliveryDate): ?string
    {
        return $nextDeliveryDate->startOfDay()
        ->addDays(self::DELIVERY_DAYS_AFTER_CHARGE)
        ->setTimezone(config('app.timezone'))
        ->format('Y-m-d H:i:s');

    }
}
