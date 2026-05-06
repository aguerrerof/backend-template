<?php

namespace App\Http\Resources;

use App\Http\Formatters\ApiResponseFormatter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseJsonResource extends JsonResource
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
    }

    public function toApiResponse(string $message, int $statusCode): JsonResponse
    {
        return ApiResponseFormatter::formatSuccess($this->toArray(request()), $message, $statusCode);
    }

    public static function formatCollectionToApiResponse(
        LengthAwarePaginator|Collection $collection,
        ?string $message,
    ): JsonResponse {
        if ($collection instanceof LengthAwarePaginator) {
            $data = static::collection($collection->items())->toArray(request());
            $pagination = [
                'current_page' => $collection->currentPage(),
                'last_page' => $collection->lastPage(),
                'per_page' => $collection->perPage(),
                'total' => $collection->total(),
            ];

            return ApiResponseFormatter::formatSuccess(
                $data,
                $message ?? 'Ok',
                paginator: $pagination,
            );
        }
        return ApiResponseFormatter::formatSuccess(
            static::collection($collection)->toArray(request()),
            $message,
        );
    }
}
