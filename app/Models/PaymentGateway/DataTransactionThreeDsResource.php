<?php

namespace App\Models\PaymentGateway;

readonly class DataTransactionThreeDsResource
{
    public function __construct(private string $pti, private string $pcc, private string $ptk, private string $prc)
    {
    }

    public function getPti(): string
    {
        return $this->pti;
    }

    public function getPcc(): string
    {
        return $this->pcc;
    }

    public function getPtk(): string
    {
        return $this->ptk;
    }

    public function getPrc(): string
    {
        return $this->prc;
    }
}
