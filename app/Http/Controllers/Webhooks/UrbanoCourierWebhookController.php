<?php

namespace App\Http\Controllers\Webhooks;

use App\Helpers\CustomLog;
use App\Http\Controllers\Controller;
use App\Http\Requests\UrbanoCourierWebhookRequest;
use App\Jobs\ProcessUrbanoCourierWebhook;
use Illuminate\Http\JsonResponse;

class UrbanoCourierWebhookController extends Controller
{
    public function handle(UrbanoCourierWebhookRequest $request): JsonResponse
    {
        $data = $request->all();

        CustomLog::saveLog(
            'INFO',
            sprintf(
                'Webhook received from Urbano Courier | numero_pedido: %s',
                (string)($data['num_pedido'] ?? 'unknown')
            ),
            $data,
        );

        ProcessUrbanoCourierWebhook::dispatch(
            $request->all(),
        );

        return response()->json(['status' => 'ok']);
    }
}
