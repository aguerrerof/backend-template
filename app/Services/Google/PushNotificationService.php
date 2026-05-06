<?php

namespace App\Services\Google;

interface PushNotificationService
{
    public function sendNotification(array $tokens, string $title, string $body, array $data = []): array;

}
