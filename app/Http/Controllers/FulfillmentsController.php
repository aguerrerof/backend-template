<?php

namespace App\Http\Controllers;

use App\Http\Resources\FulfillmentResource;
use App\Models\Fulfillment;
use Illuminate\Http\JsonResponse;

class FulfillmentsController extends Controller
{
    public function getByOrder(int $id): JsonResponse
    {
        return FulfillmentResource::formatCollectionToApiResponse(
            Fulfillment::query()
                ->with(['logisticProvider'])
                ->where('order_id', '=', $id)
                ->paginate(),
            'Ok'
        );
    }
    public function show(string $id): FulfillmentResource
    {
        return new FulfillmentResource(
            Fulfillment::query()
                ->with(['logisticProvider'])
                ->findOrFail($id)
        );
    }
}
