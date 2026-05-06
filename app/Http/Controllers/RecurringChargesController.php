<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecurringChargesRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class RecurringChargesController extends Controller
{
    public function __invoke(RecurringChargesRequest $request): JsonResponse
    {
        $chargeDate = $request->getChargeDate()->format('Y-m-d');
        Artisan::call('app:process-recurring-charges', ['--date' => $chargeDate]);
        return response()->json([
            'message' => sprintf(
                'El proceso de cargos recurrentes ha sido iniciado en segundo plano para la fecha %s.',
                $chargeDate
            ),
            'status' => 'queued'
        ], 202);
    }
}
