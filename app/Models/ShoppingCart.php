<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ShoppingCart extends Model
{
    protected $table = 'user_cart_items';

    protected $fillable = [
        'user_id',
        'product_id',
        'variant_id',
        'title',
        'flavor',
        'size',
        'image_url',
        'apply_discount',
        'apply_tax',
        'is_recurrence',
        'frequency',
        'quantity',
        'price',
        'available_recurrence',
        'stock',
        'added_at',
    ];

    protected $casts = [
        'apply_discount' => 'boolean',
        'apply_tax'      => 'boolean',
        'is_recurrence'  => 'boolean',
        'quantity'       => 'integer',
        'price'          => 'decimal:2',
        'added_at'       => 'datetime',
    ];

    /**
     * Agregar item al carrito
     */
    public static function addItem(array $data): self
    {
        $data['added_at'] = now();
        return self::create($data);
    }

    public static function updateOrCreateItem(array $data): self
    {
        $data['added_at'] = now();

        $item = self::where('user_id', $data['user_id'])
            ->where('variant_id', $data['variant_id'])
            ->first();

        if ($item) {
            $item->increment('quantity', $data['quantity'] ?? 1);
            return $item;
        } else {
            return self::create($data);
        }
    }

    /**
     * Eliminar item del carrito
     */
    public static function removeItem(int $itemId): bool
    {
        return self::where('id', $itemId)
                ->delete() > 0;
    }

    /**
     * Editar item del carrito (quantity, frequency, apply_discount)
     */
    public static function updateItem(string $userId, int $itemId, array $data): bool
    {
        $allowed = collect($data)->only(['quantity', 'frequency', 'apply_discount', 'is_recurrence'])->toArray();

        return self::where('user_id', $userId)
                ->where('id', $itemId)
                ->update($allowed) > 0;
    }

    /**
     * Editar item del carrito (quantity, frequency, apply_discount)
     */
    public static function getItem(int $itemId): self
    {
        return self::find($itemId);
    }

    /**
     * obtener todos los items de un usuario
     */
    public static function getItemsByUserId(string $userId): Collection
    {
        return self::where('user_id', $userId)->get();
    }
}
