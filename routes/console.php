<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:process-recurring-charges')
    ->dailyAt('08:00')
    ->timezone('America/Guayaquil');

Schedule::command('app:sync-discounts-from-shopify')
    ->dailyAt('08:00')
    ->timezone('America/Guayaquil');

Schedule::command('sync:shipping-catalogs-laar')
    ->dailyAt('08:00')
    ->timezone('America/Guayaquil');

Schedule::command('app:check-unpaid-orders')
    ->twiceDaily(8, 20)
    ->timezone('America/Guayaquil');
