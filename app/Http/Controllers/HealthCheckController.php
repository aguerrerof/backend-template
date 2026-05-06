<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    public function index(): JsonResponse
    {
        $status = [
            'app' => 'ok',
            'database' => [
                'status' => 'ok',
                'error' => null,
            ],
        ];

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $status['database']['status'] = 'error';
            $status['database']['error'] = $e->getMessage();
        }

        return response()->json(
            $status,
            $status['database']['status'] === 'ok'
                ? 200
                : 503,
        );
    }
}
