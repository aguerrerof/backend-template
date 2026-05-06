<?php

namespace App\Services\Google;

use App\Helpers\CustomLog;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebasePushNotificationService implements PushNotificationService
{
    public function __construct(private readonly Messaging $messaging)
    {
    }

    public function sendNotification(array $tokens, string $title, string $body, array $data = []): array
    {
        CustomLog::saveLog(
            'INFO',
            'Sending notification to users',
            ['title' => $title, 'body' => $body, 'data' => $data, 'tokens' => $tokens]
        );

        $notification = Notification::create($title, $body);
        $responses = [];
        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::new()
                    ->withNotification($notification)
                    ->withData($data)
                    ->toToken($token);
                $responses[$token] = $this->messaging->send($message);
            } catch (\Throwable $e) {
                Log::error($e->getTraceAsString());
                $responses[$token] = ['error' => $e->getMessage()];
            }
        }

        CustomLog::saveLog(
            'INFO',
            'Sending notification to users responses',
            $responses
        );

        return $responses;
    }
}
