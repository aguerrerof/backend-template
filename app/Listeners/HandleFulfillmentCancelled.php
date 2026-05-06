<?php

namespace App\Listeners;

use App\Events\FulfillmentCancelled;
use App\Models\Fulfillment;
use App\Models\UserDevice;
use App\Services\Google\PushNotificationService;
use App\Services\Logistics\LogisticProviderResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class HandleFulfillmentCancelled implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function __construct(
        private readonly LogisticProviderResolver $resolver,
        private readonly PushNotificationService $pushNotificationService,
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(FulfillmentCancelled $event): void
    {
        $fulfillment = $event->getFulfillment();

        if (!$fulfillment->logisticProvider()->exists()) {
            Log::warning('FulfillmentCancelled: no logistic provider for fulfillment id ' . $fulfillment->id);
            return;
        }
        $providerCode = $fulfillment->logisticProvider?->code ?? $fulfillment->provider_code ?? null;

        if (!$providerCode) {
            Log::warning("FulfillmentCancelled: provider code missing for fulfillment {$fulfillment->id}");
            return;
        }

        try {
            $this->notifyUser($fulfillment);
            Log::info("Shipment cancelled for fulfillment {$fulfillment->id}", ['response' => $response]);
        } catch (Throwable $e) {
            Log::error("Error cancelling shipment for fulfillment {$fulfillment->id}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
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
            $message = config(
                sprintf('notifications.fulfillment.messages.%s', $fulfillment->status)
            );
            if (!is_array($message) || !isset($message['title'], $message['body'])) {
                Log::warning("Notification message not configured for fulfillment status {$fulfillment->status}");
                return;
            }

            $this->pushNotificationService->sendNotification(
                $tokens,
                $message['title'],
                $message['body'],
                [
                'fulfillment_id' => $fulfillment->id,
                'status' => $fulfillment->status ?? null,
            ]
            );
        }
    }
}
