<?php

namespace App\Http\Controllers;

use App\Http\Resources\ShippingRateResource;
use App\Models\ShippingRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingRatesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return ShippingRateResource::formatCollectionToApiResponse(
            ShippingRate::query()
                ->withTrashed()
                ->where(function ($q) use ($request) {
                    $q->whereLike('code', "%{$request->get('q')}%")
                        ->orWhereLike('identifier', "%{$request->get('q')}%")
                        ->orWhereLike('title', "%{$request->get('q')}%")
                        ->orWhereLike('price', "%{$request->get('q')}%");
                })
                ->paginate(),
            null
        );
    }
}
