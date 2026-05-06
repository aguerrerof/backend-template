<?php

namespace App\Rules;

use App\Models\LogisticProvider;
use Illuminate\Contracts\Validation\Rule;

class ProviderWeightLimit implements Rule
{
    public function __construct(private readonly float $totalWeight)
    {
    }

    public function passes($attribute, $value): bool
    {
        $provider = LogisticProvider::query()->find($value);
        if (!$provider) {
            return true;
        }
        $limit = $provider->max_total_weight_grams;
        if (!$limit || $this->totalWeight <= $limit) {
            return true;
        }

        $this->providerName = $provider->name;
        $this->limit = $limit;
        return false;
    }

    public function message(): string
    {
        $limit = $this->limit ?? 0;
        $provider = $this->providerName ?? __('custom.provider');
        $kg = $limit / 1000;
        $formatted = number_format($kg, 2, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');
        return __('custom.fulfillment_weight_exceeds_limit', [
            'limit' => $formatted,
            'provider' => $provider,
        ]);
    }

    private ?string $providerName = null;
    private ?int $limit = null;
}
