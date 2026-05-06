<?php

namespace App\Jobs;

use App\Helpers\CustomLog;
use App\Models\Enums\FulfillmentStatus;
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

class ProcessLaarCourierWebhook implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly array $data,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $fulfillment = Fulfillment::query()
                ->where('tracking_number', '=', $this->data['noGuia'])
                ->firstOrFail();

            $previousStatus = $fulfillment->status;
            $mappedStatus = null;

            if (isset($this->data['estadoActualCodigo'])) {
                $newStatus = FulfillmentStatus::fromProvider(
                    $fulfillment->logisticProvider,
                    (string)$this->data['estadoActualCodigo']
                );
                if (!is_null($newStatus)) {
                    $fulfillment->status = $newStatus->value;
                    $mappedStatus = $newStatus->value;
                }
            }

            $fulfillment->dispatched_at = $this->parseProviderDate(
                $this->data['remitenteFecha'] ?? null,
                'remitenteFecha'
            );
            $fulfillment->delivered_at = $this->parseProviderDate(
                $this->data['destinatarioFecha'] ?? null,
                'destinatarioFecha'
            );
            $fulfillment->tracking_info = $this->data;
            $fulfillment->saveOrFail();

            CustomLog::saveLog('INFO', sprintf('LAAR fulfillment updated | noGuia: %s', $fulfillment->tracking_number), [
                'fulfillment_id' => $fulfillment->id,
                'tracking_number' => $fulfillment->tracking_number,
                'previous_status' => $previousStatus,
                'new_status' => $fulfillment->status,
                'mapped_status' => $mappedStatus,
                'provider_status_code' => $this->data['estadoActualCodigo'] ?? null,
                'dispatched_at' => $fulfillment->dispatched_at,
                'delivered_at' => $fulfillment->delivered_at,
            ]);

            $this->notifyUser($fulfillment);
        } catch (Exception $exception) {
            CustomLog::saveLog('ERROR', sprintf('Failed to process LAAR courier webhook | noGuia: %s', (string)($this->data['noGuia'] ?? 'unknown')), [
                'tracking_number' => $this->data['noGuia'] ?? null,
                'error' => $exception->getMessage(),
            ]);
            throw $exception;
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
            CustomLog::saveLog('INFO', sprintf('LAAR notification skipped: no device tokens | noGuia: %s', $fulfillment->tracking_number), [
                'fulfillment_id' => $fulfillment->id,
                'user_id' => $userId,
                'status' => $fulfillment->status,
            ]);
            return;
        }

        $status = $fulfillment->status;

        $message = config("notifications.fulfillment.messages.$status");

        if (!$message) {
            CustomLog::saveLog('INFO', sprintf('LAAR notification skipped: missing message config | noGuia: %s', $fulfillment->tracking_number), [
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

        CustomLog::saveLog('INFO', sprintf('LAAR notification sent | noGuia: %s', $fulfillment->tracking_number), [
            'fulfillment_id' => $fulfillment->id,
            'user_id' => $userId,
            'status' => $status,
            'token_count' => count($tokens),
        ]);
    }

    private function parseProviderDate(?string $value, string $field): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        $normalized = trim($value);
        $normalized = preg_replace('/:(\d{1,6})$/', '.$1', $normalized) ?? $normalized;

        try {
            return Carbon::parse($normalized);
        } catch (Exception $exception) {
            CustomLog::saveLog(
                'WARNING',
                sprintf('LAAR date parse failed | noGuia: %s', (string)($this->data['noGuia'] ?? 'unknown')),
                [
                    'field' => $field,
                    'value' => $value,
                    'error' => $exception->getMessage(),
                ]
            );

            return null;
        }
    }

}
