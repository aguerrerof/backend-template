<?php

namespace App\Helpers;

use App\Models\UserDevice;
use App\Services\Google\PushNotificationService;

class NotificationHelper
{
    public static function sendToUserDevices(
        string $userId,
        string $title,
        string $message,
        array $body,
    ) {
        $pushService = app(PushNotificationService::class);
        $tokens = UserDevice::query()
            ->where('shopify_id', '=', $userId)
            ->pluck('firebase_token')
            ->filter()
            ->toArray();
        if (empty($tokens)) {
            throw new \Exception('No tokens found');
        }
        return $pushService->sendNotification(
            $tokens,
            $title,
            $message,
            $body,
        );
    }

}
