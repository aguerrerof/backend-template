<?php

namespace App\Http\Controllers;

use App\Http\Formatters\ApiResponseFormatter;
use App\Services\ShoppingCart\ShoppingCartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShoppingCartController extends Controller
{
    public function __construct(private readonly ShoppingCartService $shoppingCart)
    {
    }

    public function add(Request $request): JsonResponse
    {
        $uid = $request->attributes->get('shopify_uid');
        return $this->shoppingCart->addProduct($uid, $request->all());
    }

    public function delete(string $id): JsonResponse
    {
        return $this->shoppingCart->deleteProduct($id);
    }

    public function modify(string $id, Request $request): JsonResponse
    {
        $uid = $request->attributes->get('shopify_uid');
        return $this->shoppingCart->modifyProduct($uid, $id, $request->all());
    }

    public function getCart(Request $request): JsonResponse
    {
        $uid = $request->attributes->get('shopify_uid');
        return $this->shoppingCart->getUserCart($uid);
    }

    public function clearCart(Request $request): JsonResponse
    {
        try {
            $this->shoppingCart->clearCart(
                (string)$request->attributes->get('shopify_uid', ''),
            );
            return ApiResponseFormatter::formatSuccess(
                [],
                __('custom.cart_was_cleared_successfully')
            );
        } catch (\Exception $exception) {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                $exception->getMessage(),
                Response::HTTP_CONFLICT,
            );
        }
    }
}
