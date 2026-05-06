<?php

namespace App\Http\Controllers;

use App\Http\Formatters\ApiResponseFormatter;
use App\Services\Shop\ShopService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class CollectionsController extends Controller
{
    public function __construct(private readonly ShopService $shopService)
    {
    }

    public function fetchCollection(Request $request): JsonResponse
    {
        try {
            $id = $request->query('id', config('services.shopify.id_home_screen'));
            $depth = $request->query('depth', 2);
            $cacheKey = sprintf('shopify_collection_%s_%s', $id, $depth);
            $cached = Cache::get($cacheKey);
            if ($cached) {
                $cachedArray = json_decode(json_encode($cached), true);
                return ApiResponseFormatter::formatSuccess($cachedArray, 'Datos obtenidos exitosamente.');
            }

            $response = $this->shopService->getCollectionById($id, $depth);
            $data = $response->getData();
            if (!is_null($data->data)) {
                Cache::put($cacheKey, $data->data, 86400);
            }

            return $response;
        } catch (\Exception  $e) {
            return ApiResponseFormatter::formatError(
                'No se pudo obtener los datos del servidor.',
                $e->getMessage(),
                Response::HTTP_CONFLICT,
            );
        }
    }

    public function getProductsCollection(string $id, Request $request): JsonResponse
    {
        try {
            $response = $this->shopService->getProductsFromCollection(
                $id,
                $request->get('first', 10),
                $request->get('after'),
            );
            if ($response->isSuccess()) {
                return ApiResponseFormatter::formatSuccess(
                    $response->getData(),
                    'Ok',
                );
            }

            return ApiResponseFormatter::formatError(
                'No se pudo obtener los datos del servidor.',
                $response->getFullErrorMessage(),
                Response::HTTP_NOT_FOUND,
            );
        } catch (\Exception  $e) {
            return ApiResponseFormatter::formatError(
                'No se pudo obtener los datos del servidor.',
                $e->getMessage(),
                Response::HTTP_CONFLICT,
            );
        }
    }
}
