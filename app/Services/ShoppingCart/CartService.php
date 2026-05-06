<?php

namespace App\Services\ShoppingCart;

use App\Http\Formatters\ApiResponseFormatter;
use App\Models\ShoppingCart;
use App\Services\Shop\ShopService;
use Illuminate\Http\JsonResponse;

class CartService implements ShoppingCartService
{
    public function __construct(private readonly ShopService $shopService)
    {
    }
    /**
     * Agregar un producto al carrito
     */
    public function addProduct(string $id, array $data): JsonResponse
    {
        try {

            $data['added_at'] = now();
            $data['user_id'] = $id;
            $data['available_recurrence'] = $data['available_recurrence'] ?? false;
            $item = ShoppingCart::updateOrCreateItem($data);

            return ApiResponseFormatter::formatSuccess($item->toArray(), 'Producto agregado al carrito');
        } catch (\Exception $e) {
            return ApiResponseFormatter::formatError('Error al agregar producto', $e->getMessage());
        }
    }

    /**
     * Eliminar un producto del carrito
     */
    public function deleteProduct(int $id): JsonResponse
    {
        try {
            $deleted = ShoppingCart::removeItem($id);

            if ($deleted) {
                return ApiResponseFormatter::formatSuccess([], 'Producto eliminado del carrito');
            }
            return ApiResponseFormatter::formatError('El producto no existe', null);
        } catch (\Exception $e) {
            return ApiResponseFormatter::formatError('Error al eliminar producto', $e->getMessage());
        }
    }

    /**
     * Modificar un producto del carrito
     */
    public function modifyProduct(string $uid, int $id, array $data): JsonResponse
    {
        try {
            $allowed = collect($data)->only(
                ['quantity', 'frequency', 'apply_discount', 'is_recurrence']
            )->toArray();

            $updated = ShoppingCart::updateItem($uid, $id, $data);

            if ($updated) {
                $item = ShoppingCart::getItem($id);
                return ApiResponseFormatter::formatSuccess($item->toArray(), 'Producto actualizado en el carrito');
            }

            return ApiResponseFormatter::formatError('El producto no existe o no se pudo actualizar', null);
        } catch (\Exception $e) {
            return ApiResponseFormatter::formatError('Error al actualizar producto', $e->getMessage());
        }
    }

    /**
     * Obtener todos los items del carrito de un usuario
     */
    public function getUserCart(string $uid): JsonResponse
    {
        try {
            $products = ShoppingCart::getItemsByUserId($uid)->toArray();
            array_walk($products, function (&$item) {
                $item['stock'] = $this->shopService->getInventoryAvailableByVariant($item['variant_id'])
                    ->getData()['total'] ?? 0;
            });
            return ApiResponseFormatter::formatSuccess($products, 'Carrito obtenido correctamente');
        } catch (\Exception $e) {
            return ApiResponseFormatter::formatError('Error al obtener el carrito', $e->getMessage());
        }
    }

    public function clearCart(string $userId): void
    {
        ShoppingCart::query()
            ->where('user_id', '=', $userId)
            ->forceDelete();
    }
}
