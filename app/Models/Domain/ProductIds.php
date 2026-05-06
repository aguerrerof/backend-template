<?php

namespace App\Models\Domain;

class ProductIds
{
    /**
     * @var array<string>
     */
    private array $ids;

    public function __construct(string ...$ids)
    {
        $this->ids = $ids;
    }

    public function add(string $id): self
    {
        $this->ids[] = $id;
        return $this;
    }

    public function all(): array
    {
        return $this->ids;
    }

    public function toArray(): array
    {
        return $this->ids;
    }

    public function __toString(): string
    {
        return implode(',', $this->ids);
    }
}
