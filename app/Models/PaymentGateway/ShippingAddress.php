<?php

namespace App\Models\PaymentGateway;

final readonly class ShippingAddress
{
    public function __construct(
        private string $country,
        private string $city,
        private string $street,
        private string $number,
    ) {
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getNumber(): string
    {
        return $this->number;
    }
}
