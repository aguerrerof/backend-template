<?php

namespace App\Services\Logistics;

use App\Models\Fulfillment;
use App\Services\Logistics\Providers\ProviderInterface;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FulfillmentShipmentService
{
    /**
     * Create the shipment with the provider and return the raw response.
     *
     * @return array<array-key, mixed>
     */
    public function createShipment(Fulfillment $fulfillment): array
    {
        $provider = $fulfillment->logisticProvider;
        if (!$provider) {
            throw new RuntimeException('No logistic provider assigned to the fulfillment.');
        }

        $providerCode = $provider->code ?? $fulfillment->provider_code ?? null;
        if ($providerCode === null || $providerCode === '') {
            throw new RuntimeException(sprintf(
                'Provider code missing for fulfillment %s',
                $fulfillment->id,
            ));
        }

        $resolvedProvider = LogisticProviderResolver::resolve($providerCode);
        if (!$resolvedProvider instanceof ProviderInterface) {
            throw new RuntimeException(sprintf(
                'Unable to resolve logistics provider %s for fulfillment %s',
                $providerCode,
                $fulfillment->id,
            ));
        }

        try {
            $response = $resolvedProvider->createShipment($fulfillment);
            Log::info("Shipment created for fulfillment {$fulfillment->id}", ['response' => $response]);
            return $response;
        } catch (\Throwable $exception) {
            Log::error("Error creating shipment for fulfillment {$fulfillment->id}: " . $exception->getMessage(), [
                'exception' => $exception,
            ]);
            throw $exception;
        }
    }
}
