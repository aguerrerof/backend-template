<?php

namespace App\Models\Shopify;

readonly class Address
{
    public function __construct(
        private string $firstName,
        private string $addressLine1,
        private ?string $addressLine2,
        private string $city,
        private ?string $provinceCode,
        private ?string $phone,
        private string $countryCode,
        private bool $default,
        private ?string $id
    ) {
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getAddressLine1(): string
    {
        return $this->addressLine1;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getProvinceCode(): string
    {
        return $this->provinceCode ?? '';
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}
