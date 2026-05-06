<?php

namespace App\Jobs;

use App\Models\Billing;
use App\Models\OrderPayment;
use App\Services\Finance\Billing\BillingProviderInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
use Throwable;

class ProcessInvoice implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 3;
    public $backoff = 60;

    public function __construct(private readonly OrderPayment $payment)
    {
    }

    public function handle(BillingProviderInterface $billingProvider): void
    {
        if ($this->payment->billings()->exists()) {
            return;
        }

        $billing = Billing::firstOrCreate(
            ['order_payment_id' => $this->payment->id],
            [
                'status' => 'processing',
                'total' => (float) ($this->payment->amount ?? 0),
            ],
        );

        try {
            $invoice = $billingProvider->createInvoice($this->payment);
            $invoiceId = $invoice['data']['id']
                ?? $invoice['data']['invoice_id']
                ?? $invoice['id']
                ?? null;

            if (!is_string($invoiceId) || trim($invoiceId) === '') {
                throw new RuntimeException('ProcessInvoice: no se pudo obtener invoiceId desde la respuesta de createInvoice().');
            }

            $authorize = null;
            if (app()->isProduction()) {
                $authorize = $billingProvider->authorizeInvoice($invoiceId);
            }

            $send = $billingProvider->sendInvoice($invoiceId);

            $billing->fill([
                'invoice_number' => $this->extractInvoiceNumber($invoice),
                'access_key' => $this->extractAccessKey($invoice),
                'status' => 'completed',
                'external_response' => [
                    'create' => $invoice,
                    'authorize' => $authorize,
                    'send' => $send,
                ],
               'total'  => $data['importeTotal'] ?? (($data['subtotal'] ?? 0) + ($data['iva'] ?? 0)),
            ])->save();
        } catch (Throwable $e) {
            $billing->fill([
                'status' => 'failed',
                'external_response' => [
                    'error' => [
                        'message' => $e->getMessage(),
                    ],
                ],
            ])->save();

            throw $e;
        }
    }

    private function extractInvoiceNumber(array $invoice): ?string
    {
        $data = is_array($invoice['data'] ?? null) ? $invoice['data'] : $invoice;
        $value = $data['invoice_number']
            ?? $data['invoiceNumber']
            ?? $data['numero']
            ?? $data['number']
            ?? null;

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    private function extractAccessKey(array $invoice): ?string
    {
        $data = is_array($invoice['data'] ?? null) ? $invoice['data'] : $invoice;
        $value = $data['access_key']
            ?? $data['accessKey']
            ?? $data['claveAcceso']
            ?? $data['access_key_sri']
            ?? null;

        return is_string($value) && trim($value) !== '' ? $value : null;
    }
}
