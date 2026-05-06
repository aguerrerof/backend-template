<?php

namespace App\Http\Controllers;

use App\Http\Resources\CityResource;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CitiesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return CityResource::formatCollectionToApiResponse(
            City::query()
                ->whereLike('name', "%{$request->get('q')}%")
                ->paginate(),
            null
        );
    }
}
