<?php

namespace App\Services\ShoppingCart;

use Illuminate\Http\JsonResponse;

interface ShoppingCartService
{
    public function addProduct(string $id, array $data): JsonResponse;
    public function deleteProduct(int $id): JsonResponse;
    public function modifyProduct(string $uid, int $id, array $data): JsonResponse;
    public function getUserCart(string $uid): JsonResponse;
    public function clearCart(string $userId): void;
}
