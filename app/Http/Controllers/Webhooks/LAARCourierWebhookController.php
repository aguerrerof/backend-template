<?php

namespace App\Http\Controllers\Webhooks;

use App\Helpers\CustomLog;
use App\Http\Controllers\Controller;
use App\Http\Requests\LAARCourierWebhookRequest;
use App\Jobs\ProcessLaarCourierWebhook;
use Illuminate\Http\JsonResponse;

class LAARCourierWebhookController extends Controller
{
    public function handle(LAARCourierWebhookRequest $request): JsonResponse
    {
        $data = $request->all();
        CustomLog::saveLog(
            'INFO',
            'Webhook received from LAAR Courier',
            $data,
        );
        ProcessLaarCourierWebhook::dispatchSync(
            $request->all(),
        );
        return response()->json(['status' => 'ok']);
    }
}
