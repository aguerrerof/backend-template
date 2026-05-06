<?php

namespace App\Http\Controllers;

use App\Http\Requests\PushNotificationTestRequest;
use App\Models\UserDevice;
use App\Services\Google\PushNotificationService;
use Illuminate\Http\JsonResponse;

class PushNotificationTestController extends Controller
{
    public function __construct(private readonly PushNotificationService $notificationService)
    {
    }

    public function __invoke(PushNotificationTestRequest $request): JsonResponse
    {
        $tokens = $this->resolveTokens($request);

        if (empty($tokens)) {
            return response()->json([
                'data' => null,
                'message' => 'No hay tokens de Firebase para el usuario activo.',
            ], 422);
        }
        $responses = $this->notificationService->sendNotification(
            $tokens,
            $request->input('title'),
            $request->input('body'),
            $request->getPayloadParameter()
        );

        return response()->json([
            'data' => [
                'tokens' => $tokens,
                'responses' => $responses,
            ],
            'message' => 'Notificación de prueba enviada.',
        ]);
    }

    /**
     * @return string[]
     */
    private function resolveTokens(PushNotificationTestRequest $request): array
    {

        return UserDevice::query()
            ->where('shopify_id', '=', $request->getUserId())
            ->pluck('firebase_token')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
