<?php

namespace App\Services\Logistics\Providers;

use App\Models\Fulfillment;

interface ProviderInterface
{
    public function createShipment(Fulfillment $fulfillment): array;
    public function cancelShipment(Fulfillment $fulfillment): array;
}
