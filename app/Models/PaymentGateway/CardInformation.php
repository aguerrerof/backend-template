<?php

namespace App\Models\PaymentGateway;

final readonly class CardInformation
{
    public function __construct(
        public string $number,
        public string $expirationYear,
        public string $expirationMonth,
        public string $cvv,
    ) {
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getExpirationYear(): string
    {
        return $this->expirationYear;
    }

    public function getExpirationMonth(): string
    {
        return $this->expirationMonth;
    }

    public function getCvv(): string
    {
        return $this->cvv;
    }
}
