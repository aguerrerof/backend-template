<?php

namespace App\Models\PaymentGateway;

final readonly class BuyerInformation
{
    public function __construct(
        private string $documentNumber,
        private string $firstName,
        private string $lastName,
        private string $phoneNumber,
        private string $email,
    ) {
    }

    public function getDocumentNumber(): string
    {
        return $this->documentNumber;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
