<?php

namespace App\Http\Controllers;

use App\Http\Formatters\ApiResponseFormatter;
use App\Services\Pricing\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function __construct(private readonly PricingService $pricingService)
    {
    }

    public function generateSubtotals(Request $request): JsonResponse
    {
        $response = $this->pricingService->generateSubtotal($request);
        if (is_null($response['error'])) {
            return ApiResponseFormatter::formatSuccess($response['data'], $response['message']);
        } else {
            return ApiResponseFormatter::formatError($response['error'], $response['devError'] ?? null);
        }
    }

    public function generateCheckout(Request $request): JsonResponse
    {
        $response = $this->pricingService->generateCheckout(
            $request->input('shippingCode'),
            $request->attributes->get('shopify_uid')
        );
        if (is_null($response['error'])) {
            return ApiResponseFormatter::formatSuccess($response['data'], $response['message']);
        } else {
            return ApiResponseFormatter::formatError($response['error'], $response['devError'] ?? null);
        }
    }

}
