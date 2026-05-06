<?php

namespace App\Http\Controllers;

use App\Http\Resources\DiscountResource;
use App\Models\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $records = Discount::query()
                    ->withTrashed()
                    ->where(function ($q) use ($request) {
                        $q
                            ->whereLike('code', "%{$request->get('q')}%")
                            ->orWhereLike('title', "%{$request->get('q')}%")
                            ->orWhereLike('usage_count', "%{$request->get('q')}%")
                            ->orWhereLike('usage_limit', "%{$request->get('q')}%")
                            ->orWhereLike('value', "%{$request->get('q')}%")
                            ->orWhereLike('value_type', "%{$request->get('q')}%");
                    })
                    ->paginate();
        return DiscountResource::formatCollectionToApiResponse(
            $records,
            'Descuentos obtenidos exitosamente'
        );
    }

}
