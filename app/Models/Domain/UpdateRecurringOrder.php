<?php

namespace App\Models\Domain;

readonly class UpdateRecurringOrder
{
    public function __construct(
        private int $id,
        private ?string $frequency,
        private ?array $lineItems,
        private ?array $shippingAddress,
        private ?int $userCardId,
        private ?string $notes,
        private ?string $email,
    ) {
    }

    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    public function getLineItems(): ?array
    {
        return $this->lineItems;
    }

    public function getShippingAddress(): ?array
    {
        return $this->shippingAddress;
    }

    public function getUserCardId(): ?int
    {
        return $this->userCardId;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            frequency: $data['frequency'] ?? null,
            lineItems: $data['line_items'] ?? null,
            shippingAddress: $data['shipping_address'] ?? null,
            userCardId: $data['user_card_id'] ?? $data['card_id'] ?? null,
            notes: $data['notes'] ?? null,
            email: $data['email'] ?? null,
        );
    }

}
