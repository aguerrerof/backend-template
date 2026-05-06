<?php

namespace App\Observers;

use App\Jobs\ProcessInvoice;
use App\Models\OrderPayment;

class OrderPaymentObserver
{
    /**
     * Handle the OrderPayment "created" event.
     */
    public function created(OrderPayment $orderPayment): void
    {
        ProcessInvoice::dispatch($orderPayment)
        ->delay(now()->addMinutes(10));
    }
}
