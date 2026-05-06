<?php

namespace App\Http\Controllers;

use App\Exceptions\ProductInventoryNotFoundException;
use App\Exceptions\ProductVariantNotFoundException;
use App\Http\Formatters\ApiResponseFormatter;
use App\Http\Requests\GetMultipleProductsInformationRequest;
use App\Http\Requests\GetProductOfferRequest;
use App\Http\Requests\GetProductsVariantsInformationRequest;
use App\Services\Eloquent\OrderService;
use App\Services\Shop\ShopService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery\Exception;

class ProductsController extends Controller
{
    public function __construct(
        private readonly ShopService $shopService,
        private readonly OrderService $orderService,
    ) {
    }

    public function getProductsInformation(GetMultipleProductsInformationRequest $request): JsonResponse
    {
        $response = $this->shopService->getProductsInformation($request->getProductIds());
        if ($response->isSuccess()) {
            $data = $response->getData();
            $nodes = $data['nodes'];
            return ApiResponseFormatter::formatSuccess($nodes, 'Ok');
        } else {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                $response->getFullErrorMessage(),
            );
        }
    }

    public function searchProduct(Request $request): JsonResponse
    {
        $q = $request->query('textSearch', '');
        $after = $request->query('after');

        $response = $this->shopService->searchProduct($q, $after);
        if ($response->isSuccess()) {
            $data = $response->getData();
            $products = $data['products'] ?? [];
            return ApiResponseFormatter::formatSuccess($products, 'Ok');
        } else {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                $response->getFullErrorMessage(),
            );
        }
    }

    public function getProductVariants(string $id): JsonResponse
    {
        $response = $this->shopService->getProductVariantsByID($id);
        if ($response->isSuccess()) {
            $data = $response->getData();
            $product = $data['product'] ?? [];
            return ApiResponseFormatter::formatSuccess($product, 'Ok');
        } else {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                $response->getFullErrorMessage(),
            );
        }
    }

    public function getProductVariantsInformation(GetProductsVariantsInformationRequest $request): JsonResponse
    {
        $response = $this->shopService->getVariantsInformation($request->getVariantsIds());
        if (!$response->isSuccess()) {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                $response->getFullErrorMessage(),
            );
        } else {
            return ApiResponseFormatter::formatSuccess($response->getData(), 'Ok');
        }
    }

    public function getProductOfferRecurrence(GetProductOfferRequest $request): JsonResponse
    {
        try {
            $response = $this->shopService->getProductOfferRecurrence(
                $request->getVariantId(),
                $request->getQuantity(),
            );
            if (is_null($response['error'])) {
                return ApiResponseFormatter::formatSuccess($response['data'] ?? [], $response['message']);
            } else {
                return ApiResponseFormatter::formatError($response['error'], $response['devError'] ?? null);
            }
        } catch (ProductVariantNotFoundException $exception) {
            return ApiResponseFormatter::formatError(
                $exception->getUserMessage(),
                $exception->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        } catch (Exception $exception) {
            return ApiResponseFormatter::formatError(
                $exception->getUserMessage() ?? __('custom.error_trying_to_process_request'),
                $exception->getMessage(),
                Response::HTTP_CONFLICT,
            );
        }
    }

    public function getInventoryAvailable(string $productId): JsonResponse
    {
        try {
            $response = $this->shopService->getInventoryAvailableByProduct($productId);
            return ApiResponseFormatter::formatSuccess(
                $response->getData(),
                'Ok',
            );
        } catch (ProductInventoryNotFoundException $exception) {
            return ApiResponseFormatter::formatError(
                $exception->getUserMessage(),
                $exception->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        } catch (Exception $exception) {
            return ApiResponseFormatter::formatError(
                $exception->getUserMessage() ?? __('custom.error_trying_to_process_request'),
                $exception->getMessage(),
                Response::HTTP_CONFLICT,
            );
        }
    }

    public function getReorderableProducts(Request $request): JsonResponse
    {
        try {
            $collection = $this->orderService->getReorderableProducts(
                $request->attributes->get('shopify_uid') ?? '',
            );
        } catch (\Exception $exception) {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                $exception->getMessage(),
                Response::HTTP_CONFLICT,
            );
        }
        return ApiResponseFormatter::formatSuccess(
            $collection->toArray(),
            'Ok',
        );
    }

}
