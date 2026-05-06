<?php

namespace App\Services\Pricing;

use Illuminate\Http\Request;

interface PricingService
{
    public function generateSubtotal(Request $request): array;

    public function generateCheckout(
        ?string $shippingCode,
        string $shopifyCustomerId,
    ): array;
}
