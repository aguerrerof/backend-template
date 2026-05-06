<?php

namespace App\Services\Finance\Billing;

use App\Models\OrderPayment;

interface BillingProviderInterface
{
    public function createInvoice(OrderPayment $payment): array;
    public function authorizeInvoice(string $id): array;
    public function sendInvoice(string $id): array;
}
