<?php

namespace App\Services\Logistics\Providers;

use App\Helpers\CustomLog;
use App\Models\Enums\LaarShippingProductIdentifier;
use App\Models\Fulfillment;
use App\Models\LogisticProvider;
use App\Models\Order;
use App\Models\ShippingCity;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class LAARProvider implements ProviderInterface
{
    public function downloadDeliveryNote(Fulfillment $fulfillment): Response
    {
        $logisticProvider = $fulfillment->logisticProvider;
        $token = $this->login($logisticProvider);

        if (!$token) {
            throw new \RuntimeException('LAAR authentication failed while downloading delivery note.');
        }

        if (!$fulfillment->tracking_number) {
            throw new \RuntimeException('Tracking number is required to download LAAR delivery note.');
        }

        $endpoint = sprintf('%s/Pdfs/v3/etiqueta/descargar', rtrim($logisticProvider->api_url, '/'));
        $response = Http::withToken($token)
            ->accept('application/pdf')
            ->get($endpoint, ['guia' => $fulfillment->tracking_number]);

        if (!$response->successful()) {
            CustomLog::saveLog(
                'ERROR',
                'Error downloading LAAR delivery note',
                [
                    'tracking_number' => $fulfillment->tracking_number,
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ],
            );
            throw new \RuntimeException('LAAR delivery note download failed.');
        }

        return response(
            $response->body(),
            200,
            [
                'Content-Type' => $response->header('Content-Type', 'application/pdf'),
                'Content-Disposition' => sprintf(
                    'inline; filename="guia-%s.pdf"',
                    $fulfillment->tracking_number,
                ),
            ],
        );
    }

    public function createShipment(Fulfillment $fulfillment): array
    {
        $logisticProvider = $fulfillment->logisticProvider;
        $token = $this->login($logisticProvider);
        $payload = $this->preparePayload($fulfillment);
        $endpoint = sprintf('%s/Guias/v1/guias/contado?isRetorno=false', $logisticProvider->api_url);
        CustomLog::saveLog(
            'INFO',
            'Sending request to register a new shipment to LAAR courier provider',
            ['payload' => $payload,'endpoint' => $endpoint],
        );
        $response = Http::withToken($token)
            ->post(
                $endpoint,
                $payload,
            );
        CustomLog::saveLog(
            'INfO',
            'Response from request to register a new shipment to LAAR provider ',
            [
                'payload' => $payload,
                'response' => $response->json(),
            ],
        );
        if ($response->successful()) {
            $responseFromAPI = json_decode($response->body(), true);
            $trackingNumber = $responseFromAPI['guia'] ?? null;
            if ($trackingNumber) {
                $fulfillment->tracking_number = $trackingNumber;
                $fulfillment->tracking_url = sprintf(
                    '%s?guia=%s',
                    config(
                        'logistic-providers.LAAR.tracking_base_url',
                    ),
                    $trackingNumber,
                );
                $fulfillment->saveOrFail();
            }
        }
        return $response->json();
    }

    private function preparePayload(Fulfillment $fulfillment): array
    {
        $order = $fulfillment->order->order;
        [$totalWeight, $totalItems] = $this->calculateTotals($fulfillment);
        return [
            'origen' => $fulfillment->logisticProvider->config['ordenes']['origen'] ?? [],
            'destino' => [
                'ciudadD' => $this->getShippingCity($fulfillment),
                'nombreD' => sprintf(
                    '%s%s',
                    $order->shipping_address->first_name ?? '',
                    $order->shipping_address->last_name ?? '',
                ),
                'direccion' => $order->shipping_address->address1
                    ?? $order->shipping_address->address2
                        ?? null,
                'telefono' => $order->shipping_address->phone
                    ?? $order->customer->default_address->phone
                        ?? null,
                'celular' => $order->shipping_address->phone
                    ?? $order->customer->default_address->phone
                        ?? null,
            ],
            'numeroGuia' => $fulfillment->tracking_number ?? null,
            'tipoServicio' => LaarShippingProductIdentifier::CARGA->value,
            'noPiezas' => $totalItems,
            'peso' => $totalWeight,
            'fechaPedido' => $fulfillment->delivery_date,
            'contiene' => $this->getContentDelivery($fulfillment->order),
        ];
    }

    private function login(LogisticProvider $logisticProvider): ?string
    {
        $response = Http::post(
            sprintf('%s/Login/authenticate', $logisticProvider->api_url),
            $logisticProvider->credentials,
        );
        $response = json_decode($response->body());
        return $response->token ?? null;
    }

    /**
     * @param Fulfillment $fulfillment
     * @return string
     */
    public function getShippingCity(Fulfillment $fulfillment): string
    {
        $order = $fulfillment->order->order;
        $city = $order->shipping_address->city
            ?? $order->customer->default_address->city
            ?? null;
        return ShippingCity::query()
            ->where('name', '=', strtoupper($city))
            ->firstOrFail()
            ->code;
    }

    private function calculateTotals(Fulfillment $fulfillment): array
    {
        $order = $fulfillment->order;
        $totalWeight = 0;
        $totalItems = 0;
        if (isset($order->order->line_items)) {
            foreach ($order->order->line_items as $lineItem) {
                if (!isset($lineItem->grams) || !isset($lineItem->quantity)) {
                    continue;
                }
                $totalWeight += ((float)$lineItem->grams * $lineItem->quantity);
                $totalItems += (int)$lineItem->quantity ?? 1;
            }
        }
        $totalWeight = $fulfillment->total_weight ?? $totalWeight;
        return [$totalWeight, $totalItems];
    }

    public function cancelShipment(Fulfillment $fulfillment): array
    {

        $logisticProvider = $fulfillment->logisticProvider;
        $token = $this->login($logisticProvider);
        CustomLog::saveLog(
            'INFO',
            sprintf('Sending request to cancel %s shipment to LAAR courier provider', $fulfillment->tracking_number),
            [],
        );

        $response = Http::withToken($token)
            ->delete(sprintf('%s/Guias/v1/anular/%s', $logisticProvider->api_url, $fulfillment->tracking_number));

        $responseFromAPI = $response->json();
        CustomLog::saveLog(
            'INFO',
            sprintf('Sending cancel request for shipment %s to LAAR courier provider', $fulfillment->tracking_number),
            [
                'status' => $response->status(),
                'response' => $responseFromAPI,
            ],
        );
        return $responseFromAPI ?? [];
    }

    private function getContentDelivery(Order $order): ?string
    {
        $contentDelivery = [];
        if (!isset($order->order->line_items)) {
            return null;
        }
        foreach ($order->order->line_items as $lineItem) {
            if (isset($lineItem->name)) {
                $contentDelivery[] = $lineItem->name;
            }
        }
        return implode(', ', $contentDelivery);
    }

}
