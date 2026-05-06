<?php

namespace App\Listeners;

use App\Events\FulfillmentCreated;
use App\Models\Fulfillment;
use App\Models\UserDevice;
use App\Services\Google\PushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleFulfillmentCreated implements ShouldQueue
{
    use InteractsWithQueue;
    public int $tries = 3;

    public function __construct(
        private readonly PushNotificationService $pushNotificationService
    ) {
    }

    public function handle(FulfillmentCreated $event): void
    {
        $fulfillment = $event->getFulfillment();
        $this->notifyUser($fulfillment);
    }

    private function notifyUser(Fulfillment $fulfillment): void
    {
        $userId = $fulfillment->order->user_id;

        $tokens = UserDevice::query()
            ->where('shopify_id', '=', $userId)
            ->pluck('firebase_token')
            ->filter()
            ->toArray();

        if (!empty($tokens)) {
            $message = config(sprintf('notifications.fulfillment.messages.%s', $fulfillment->status));
            $this->pushNotificationService->sendNotification(
                $tokens,
                $message['title'],
                $message['body'],
                [
                    'fulfillment_id' => $fulfillment->id,
                ]
            );
        }
    }
}
