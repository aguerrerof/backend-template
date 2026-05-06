<?php

namespace App\Services\Eloquent;

use Illuminate\Support\Collection;

interface OrderService
{
    public function getReorderableProducts(string $userId): Collection;
}
