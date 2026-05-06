<?php

namespace App\Models\PaymentGateway\Response;

readonly class ExternalServiceResponse
{
    public function __construct(
        private string $message,
        private CustomStatus $customStatus,
        private bool $needExtraValidation,
        private array $details
    ) {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function needExtraValidation(): bool
    {
        return $this->needExtraValidation;
    }

    public function getCustomStatus(): CustomStatus
    {
        return $this->customStatus;
    }
}
