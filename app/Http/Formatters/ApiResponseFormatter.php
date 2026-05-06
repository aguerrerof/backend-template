<?php

namespace App\Http\Formatters;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseFormatter
{
    public static function formatSuccess(
        array $data,
        string $message,
        int $status = Response::HTTP_OK,
        ?array $paginator = null,
    ): JsonResponse {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'error' => null,
            'devError' => null,
            'pageInfo' => $paginator
        ], $status);
    }

    public static function formatError(
        string $message,
        ?string $devError,
        int $status = Response::HTTP_INTERNAL_SERVER_ERROR,
        array $data = null
    ): JsonResponse {
        return response()->json([
            'data' => $data,
            'message' => null,
            'error' => $message,
            'devError' => $devError,
        ], $status);
    }
}
