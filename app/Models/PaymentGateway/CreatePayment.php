<?php

namespace App\Models\PaymentGateway;

readonly class CreatePayment
{
    public function __construct(
        private float $baseAmount0,
        private float $taxAmount,
        private string $token,
        private string $description,
        private bool $hasGracePeriod,
        private bool $applyInterest,
        private int $installments,
        private string $additionalDetails
    ) {
    }

    public function getBaseAmount0(): float
    {
        return $this->baseAmount0;
    }

    public function getTaxAmount(): float
    {
        return $this->taxAmount;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function hasGracePeriod(): bool
    {
        return $this->hasGracePeriod;
    }

    public function applyInterest(): bool
    {
        return $this->applyInterest;
    }

    public function getInstallments(): int
    {
        return $this->installments;
    }

    public function getAdditionalDetails(): string
    {
        return $this->additionalDetails;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            baseAmount0: $data['base_amount_0'],
            taxAmount: $data['tax_amount'],
            token: $data['token'],
            description: $data['description'],
            hasGracePeriod: $data['has_grace_period'],
            applyInterest: $data['apply_interest'],
            installments: $data['installments'],
            additionalDetails: $data['additional_details'] ?? 'N/A'
        );
    }
}
