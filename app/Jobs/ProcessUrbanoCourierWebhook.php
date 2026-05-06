<?php

namespace App\Jobs;

use App\Helpers\CustomLog;
use App\Models\Enums\FulfillmentStatus;
use App\Models\Enums\UrbanoWebhookStatus;
use App\Models\Fulfillment;
use App\Models\UserDevice;
use App\Services\Google\PushNotificationService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessUrbanoCourierWebhook implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        private readonly array $data,
    ) {
    }

    public function handle(): void
    {
        try {
            $trackingNumber = (string)$this->data['num_pedido'];

            if ($trackingNumber === '') {
                CustomLog::saveLog('WARNING', 'Urbano webhook skipped: missing num_pedido', [
                    'payload' => $this->data,
                ]);
                return;
            }

            $fulfillment = Fulfillment::query()
                 ->whereHas('logisticProvider', function ($query) {
                     $query->where('code', 'UHD');
                 })
                 ->where('tracking_info->num_pedido', $trackingNumber)
                 ->first();
            if (!$fulfillment) {
                CustomLog::saveLog('WARNING', sprintf('Urbano webhook skipped: fulfillment not found | num_pedido: %s', $trackingNumber), [
                    'num_pedido' => $trackingNumber,
                    'payload' => $this->data,
                ]);
                return;
            }

            $previousStatus = $fulfillment->status;
            $mappedStatus = $this->mapStatus($this->data['chk'] ?? null);

            $eventDate = $this->parseProviderDate($this->data['fecha'] ?? null, $this->data['hora'] ?? null);

            if (isset($mappedStatus)) {
                $fulfillment->status = $mappedStatus->value;
            }

            if (!is_null($eventDate)) {
                if (
                    in_array($fulfillment->status, [
                        FulfillmentStatus::DISPATCHED->value,
                        FulfillmentStatus::PICKED_UP->value,
                        FulfillmentStatus::IN_WAREHOUSE->value,
                        FulfillmentStatus::IN_TRANSIT->value,
                        FulfillmentStatus::OUT_FOR_DELIVERY->value,
                    ], true)
                ) {
                    $fulfillment->dispatched_at = $eventDate;
                }

                if ($fulfillment->status === FulfillmentStatus::DELIVERED->value) {
                    $fulfillment->delivered_at = $eventDate;
                }
            }

            $fulfillment->tracking_info = array_merge($fulfillment->tracking_info ?? [], $this->data);
            $fulfillment->saveOrFail();

            CustomLog::saveLog('INFO', sprintf('Urbano fulfillment updated | num_pedido: %s', $trackingNumber), [
                'fulfillment_id' => $fulfillment->id,
                'tracking_number' => $fulfillment->tracking_number,
                'previous_status' => $previousStatus,
                'new_status' => $fulfillment->status,
                'provider_status_code' => $this->data['chk'] ?? null,
                'provider_status' => $this->data['estado'] ?? null,
                'provider_sub_status' => $this->data['sub_estado'] ?? null,
                'dispatched_at' => $fulfillment->dispatched_at,
                'delivered_at' => $fulfillment->delivered_at,
            ]);

            if ($previousStatus !== $fulfillment->status) {
                try {
                    $this->notifyUser($fulfillment);
                } catch (Exception $exception) {
                    CustomLog::saveLog('WARNING', sprintf('Urbano notification failed | num_pedido: %s', $trackingNumber), [
                        'tracking_number' => $trackingNumber,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        } catch (Exception $exception) {
            CustomLog::saveLog('ERROR', sprintf('Failed to process Urbano courier webhook | num_pedido: %s', (string)($this->data['num_pedido'] ?? 'unknown')), [
                'tracking_number' => $this->data['num_pedido'] ?? null,
                'error' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }

    private function mapStatus(?string $chk): ?FulfillmentStatus
    {
        return UrbanoWebhookStatus::fromCode($chk)?->toFulfillmentStatus();
    }

    private function parseProviderDate(?string $date, ?string $hour): ?Carbon
    {
        if (empty($date) || empty($hour)) {
            return null;
        }

        $value = sprintf('%s %s', trim($date), trim($hour));

        try {
            return Carbon::createFromFormat('d/m/Y H:i', $value);
        } catch (Exception $exception) {
            CustomLog::saveLog('WARNING', sprintf('Urbano date parse failed | num_pedido: %s', (string)($this->data['num_pedido'] ?? 'unknown')), [
                'fecha' => $date,
                'hora' => $hour,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function notifyUser(Fulfillment $fulfillment): void
    {
        $userId = $fulfillment->order->user_id;

        $pushService = app(PushNotificationService::class);

        $tokens = UserDevice::query()
            ->where('shopify_id', '=', $userId)
            ->pluck('firebase_token')
            ->filter()
            ->toArray();

        if (empty($tokens)) {
            CustomLog::saveLog('INFO', sprintf('Urbano notification skipped: no device tokens | num_pedido: %s', $fulfillment->tracking_number), [
                'fulfillment_id' => $fulfillment->id,
                'user_id' => $userId,
                'status' => $fulfillment->status,
            ]);
            return;
        }

        $status = $fulfillment->status;
        $message = config("notifications.fulfillment.messages.$status");

        if (!$message) {
            CustomLog::saveLog('INFO', sprintf('Urbano notification skipped: missing message config | num_pedido: %s', $fulfillment->tracking_number), [
                'fulfillment_id' => $fulfillment->id,
                'user_id' => $userId,
                'status' => $status,
            ]);
            return;
        }

        $pushService->sendNotification(
            $tokens,
            $message['title'],
            $message['body'],
            [
                'fulfillment_id' => $fulfillment->id,
                'status' => $status,
            ]
        );

        CustomLog::saveLog('INFO', sprintf('Urbano notification sent | num_pedido: %s', $fulfillment->tracking_number), [
            'fulfillment_id' => $fulfillment->id,
            'user_id' => $userId,
            'status' => $status,
            'token_count' => count($tokens),
        ]);
    }
}
