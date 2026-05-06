<?php

namespace App\Services\Logistics\Providers;

use App\Helpers\CustomLog;
use App\Models\Enums\FulfillmentStatus;
use App\Models\Enums\UrbanoUbigeo;
use App\Models\Fulfillment;
use DateTimeInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class UrbanoProvider implements ProviderInterface
{
    private const TRACKING_INFO_NUM_PEDIDO_KEY = 'num_pedido';
    private const TRACKING_INFO_COD_SEGUIMIENTO_KEY = 'cod_seguimiento';
    private const TRACKING_INFO_GUIA_UE_KEY = 'guia_ue';
    private const REQUEST_TIMEOUT_SECONDS = 20;
    private const CONNECT_TIMEOUT_SECONDS = 10;

    public function getShipmentPayload(Fulfillment $fulfillment): array
    {
        return $this->preparePayload($fulfillment);
    }

    public function createShipment(Fulfillment $fulfillment): array
    {
        $logisticProvider = $fulfillment->logisticProvider;
        $credentials = $logisticProvider->credentials ?? [];
        $payload = $this->getShipmentPayload($fulfillment);
        $formPayload = [
            'json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ];
        CustomLog::saveLog(
            'INFO',
            'Sending request to register a new shipment to URBANO courier provider',
            $payload,
        );
        try {
            $response = Http::withHeaders([
             'Usuario' => (string)($credentials['usuario'] ?? $credentials['Usuario'] ?? ''),
             'Token' => (string)($credentials['token'] ?? $credentials['Token'] ?? ''),
             'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()
             // Keep under PHP max_execution_time to avoid hard-failing with a fatal error.
             ->timeout(self::REQUEST_TIMEOUT_SECONDS)
             ->connectTimeout(self::CONNECT_TIMEOUT_SECONDS)
             ->post(
                 sprintf('%s/api/ws/generar_pedido', $logisticProvider->api_url),
                 $formPayload,
             );
            $responseFromAPI = $response->json();
            if (!is_array($responseFromAPI)) {
                $responseFromAPI = json_decode($response->body(), true) ?? [];
            }
            CustomLog::saveLog(
                'INFO',
                'Response from request to register a new shipment to URBANO provider',
                [
                    'payload' => $payload,
                    'form_payload' => $formPayload,
                    'response' => $responseFromAPI,
                ],
            );
            $errorCode = $responseFromAPI['error'] ?? null;
            if (is_string($errorCode) && is_numeric($errorCode)) {
                $errorCode = (int)$errorCode;
            }

            if (!$response->successful() || $errorCode !== 0) {
                $providerMessage = trim((string)($responseFromAPI['mensaje'] ?? $responseFromAPI['message'] ?? ''));
                if ($providerMessage === '') {
                    $providerMessage = trim((string)($responseFromAPI['error_message'] ?? $response->body()));
                }
                throw new RuntimeException($providerMessage !== '' ? $providerMessage : 'Urbano API error.');
            }

            if ($response->successful() && $errorCode === 0) {
                $trackingNumber = $responseFromAPI['guia_ue'] ?? null;
                $providerOrderNumber = (string)($payload['num_pedido'] ?? '');
                $providerTrackingCode = (string)($payload['cod_seguimiento'] ?? '');

                $trackingInfoUpdates = array_filter([
                    self::TRACKING_INFO_NUM_PEDIDO_KEY => $providerOrderNumber !== '' ? $providerOrderNumber : null,
                    self::TRACKING_INFO_COD_SEGUIMIENTO_KEY => $providerTrackingCode !== '' ? $providerTrackingCode : null,
                ], static fn ($value) => $value !== null);

                if ($trackingNumber) {
                    $trackingInfoUpdates[self::TRACKING_INFO_GUIA_UE_KEY] = $trackingNumber;
                    $trackingBaseUrl = config('logistic-providers.UHD.tracking_base_url');
                    if ($trackingBaseUrl) {
                        $fulfillment->tracking_url = sprintf(
                            '%s?id=%s',
                            $trackingBaseUrl,
                            $trackingNumber,
                        );
                    }
                }

                if (!empty($trackingInfoUpdates)) {
                    $fulfillment->tracking_info = array_merge($fulfillment->tracking_info ?? [], $trackingInfoUpdates);
                }

                if ($providerTrackingCode !== '') {
                    $fulfillment->tracking_number = $providerTrackingCode;
                }

                if ($trackingNumber || $providerOrderNumber !== '' || $providerTrackingCode !== '') {
                    $fulfillment->saveOrFail();
                }
            }
            return $responseFromAPI;
        } catch (\Throwable $e) {
            CustomLog::saveLog(
                'ERROR',
                'Error trying to register a new shipment to URBANO provider',
                [
                    'payload' => $payload,
                    'form_payload' => $formPayload,
                    'error' => $e->getTraceAsString(),
                ],
            );
            throw $e;
        }

    }

    private function preparePayload(Fulfillment $fulfillment): array
    {
        $order = $fulfillment->order->order;
        $providerConfig = $fulfillment->logisticProvider->config ?? [];
        $shippingAddress = $order->shipping_address ?? null;
        $customer = $order->customer ?? null;

        $existingOrderIdentifier = (string)($fulfillment->tracking_info[self::TRACKING_INFO_NUM_PEDIDO_KEY] ?? '');
        $orderIdentifier = $this->resolveOrderIdentifier((string)($order->order_number ?? ''), $existingOrderIdentifier);
        $trackingCode = $this->buildTrackingCode($orderIdentifier);
        $orderDate = $this->formatDate('now');
        $orderHour = $this->formatTime('now');
        $deliveryDate = $this->formatDate($fulfillment->delivery_date ?? $order->created_at ?? 'now');

        $customerName = trim(sprintf(
            '%s %s',
            $shippingAddress->first_name ?? '',
            $shippingAddress->last_name ?? '',
        ));
        $customerName = $customerName !== ''
            ? $customerName
            : (string)($customer->first_name ?? 'Cliente');

        $customerPhone = (string)($shippingAddress->phone
            ?? $customer->default_address->phone
            ?? '');
        $customerEmail = (string)($order->email
            ?? $customer->email
            ?? '');

        $address = (string)($shippingAddress->address1 ?? $shippingAddress->address2 ?? '');
        $transversal = (string)($shippingAddress->address2 ?? '');
        $reference = (string)($shippingAddress->name
            ?? trim(sprintf('%s %s', (string)($shippingAddress->first_name ?? ''), (string)($shippingAddress->last_name ?? '')))
            ?? '');
        $latitude = $shippingAddress->latitude ?? null;
        $longitude = $shippingAddress->longitude ?? null;
        $deliveryNote = (string)($order->note ?? '');
        $ubigeoCode = $this->resolveUbigeoCode($shippingAddress, $customer, $providerConfig);

        $products = $this->buildProducts($fulfillment);

        return [
            'contrato' => (string) $providerConfig['contrato'] ?? '',
            'cod_seguimiento' => $trackingCode,
            'num_pedido' => str($orderIdentifier)
                ->replace('#', '')
                ->prepend('ORD-')
                ->append('-', strtoupper(Str::random(5)))
                ->limit(16, ''),
            'fecha_pedido' => $orderDate,
            'hora_pedido' => $orderHour,
            'nom_cliente' => $customerName,
            'telf_cliente' => $customerPhone,
            'mail_cliente' => $customerEmail,
            'tipo_cobranza' => '000',
            'puntos_recojo' => [
                [
                    'cod_tienda' => (string)$providerConfig['cod_tienda_default'] ?? '001',
                    'contacto_tienda' => (string)$providerConfig['contacto_tienda_default']  ?? null,
                    'productos' => $products,
                    'apuntes' => 'Pedido desde la aplicacion',
                ],
            ],
            'direcciones' => [
                [
                    'cod_localidad' => $ubigeoCode,
                    'direccion' => $address,
                    'transversal' => $transversal,
                    'referencia' => $reference,
                    'punto_x' => (string)($latitude ?? ''),
                    'punto_y' => (string)($longitude ?? ''),
                    'fecha_entrega' => $deliveryDate,
                    'anotacion_entrega' => $deliveryNote,
                ],
            ]
        ];
    }

    private function resolveUbigeoCode(?object $shippingAddress, ?object $customer, array $providerConfig): string
    {
        $configuredUbigeo = trim((string)($providerConfig['cod_localidad'] ?? ''));
        if ($configuredUbigeo !== '') {
            return $configuredUbigeo;
        }

        $defaultAddress = $customer->default_address ?? null;
        $province = (string)($shippingAddress->province
            ?? $defaultAddress->province
            ?? '');
        $canton = (string)($shippingAddress->city
            ?? $defaultAddress->city
            ?? '');
        $city = (string)($shippingAddress->city
            ?? $defaultAddress->city
            ?? '');

        $ubigeo = UrbanoUbigeo::fromLocation($province, $canton, $city)
            ?? UrbanoUbigeo::fromCity($city !== '' ? $city : $canton)
            ?? UrbanoUbigeo::GUAYAS_GUAYAQUIL_GUAYAQUIL;

        return $ubigeo->value;
    }

    public function cancelShipment(Fulfillment $fulfillment): array
    {
        $logisticProvider = $fulfillment->logisticProvider;
        $credentials = $logisticProvider->credentials ?? [];

        $providerOrderNumber = (string)$fulfillment->tracking_info[self::TRACKING_INFO_NUM_PEDIDO_KEY] ?? '';
        $providerGuideNumber = (string)$fulfillment->tracking_info[self::TRACKING_INFO_COD_SEGUIMIENTO_KEY] ?? '';

        $payload =  [
                    'num_pedido' => $providerOrderNumber,
                    'num_guía' => $providerGuideNumber,
                    'motivo_codigo' => 5,
                 ];
        $formPayload = [
            'json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ];
        CustomLog::saveLog(
            'INFO',
            sprintf(
                'Sending request to cancel shipment to URBANO courier provider (num_pedido=%s, num_guia=%s)',
                $providerOrderNumber,
                $providerGuideNumber
            ),
            [
                'num_pedido' => $providerOrderNumber,
                'num_guia' => $providerGuideNumber,
                'payload' => $payload,
            ],
        );
        try {
            $response = Http::withHeaders([
             'Usuario' => (string)($credentials['usuario'] ?? $credentials['Usuario'] ?? ''),
             'Token' => (string)($credentials['token'] ?? $credentials['Token'] ?? ''),
             'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()
             ->post(
                 sprintf('%s/api/ws/cancela_pedido', $logisticProvider->api_url),
                 $formPayload,
             );
            $responseFromAPI = $response->json();
            if (!is_array($responseFromAPI)) {
                $responseFromAPI = json_decode($response->body(), true) ?? [];
            }
            CustomLog::saveLog(
                'INFO',
                sprintf(
                    'Response from request to cancel shipment to URBANO provider (num_pedido=%s, num_guia=%s)',
                    $providerOrderNumber,
                    $providerGuideNumber
                ),
                [
                    'num_pedido' => $providerOrderNumber,
                    'num_guia' => $providerGuideNumber,
                    'payload' => $payload,
                    'response' => $responseFromAPI,
                ],
            );

            $errorCode = $responseFromAPI['error'] ?? null;
            if (is_string($errorCode) && is_numeric($errorCode)) {
                $errorCode = (int)$errorCode;
            }
            if ($errorCode === -1) {
                $fulfillment->status = FulfillmentStatus::PENDING->value;
                $fulfillment->saveOrFail();
            }

            return $responseFromAPI;
        } catch (\Throwable $e) {
            CustomLog::saveLog(
                'ERROR',
                sprintf(
                    'Error trying to cancel a shipment to URBANO provider (num_pedido=%s, num_guia=%s)',
                    $providerOrderNumber,
                    $providerGuideNumber
                ),
                [
                    'num_pedido' => $providerOrderNumber,
                    'num_guia' => $providerGuideNumber,
                    'payload' => $payload,
                    'form_payload' => $formPayload,
                    'error' => $e->getTraceAsString(),
                ],
            );
            throw $e;
        }

        return [];
    }

    private function buildProducts(Fulfillment $fulfillment): array
    {
        $order = $fulfillment->order->order;
        $products = [];

        if (isset($order->line_items) && is_iterable($order->line_items)) {
            foreach ($order->line_items as $lineItem) {
                $quantity = (int)$this->getLineItemValue($lineItem, 'quantity', 1);
                $weightInKg = $this->resolveWeightInKg($lineItem);
                $sku = (string)$this->getLineItemValue($lineItem, 'sku', '');
                $title = trim((string)$this->getLineItemValue($lineItem, 'title', ''));
                $variantTitle = trim((string)$this->getLineItemValue($lineItem, 'variant_title', ''));
                $name = trim((string)$this->getLineItemValue($lineItem, 'name', ''));

                if ($name === '') {
                    if ($title !== '' && $variantTitle !== '' && strtolower($variantTitle) !== 'default title') {
                        $name = sprintf('%s - %s', $title, $variantTitle);
                    } else {
                        $name = $title !== '' ? $title : ($variantTitle !== '' ? $variantTitle : 'Producto');
                    }
                }

                $price = $this->getLineItemValue($lineItem, 'price');
                if ($price === null || $price === '') {
                    $price = $this->getNestedLineItemValue($lineItem, ['price_set', 'shop_money', 'amount'], '0');
                }

                $products[] = [
                    'cod_sku' => $sku,
                    'nombre' => $name,
                    'peso' => number_format($weightInKg, 2, '.', ''),
                    'cantidad' => (string)$quantity,
                    'precio' => (string)$price
                ];
            }
        }

        return $products;
    }

    private function getLineItemValue($lineItem, string $key, $default = null)
    {
        if (is_array($lineItem)) {
            return $lineItem[$key] ?? $default;
        }
        if (is_object($lineItem)) {
            return $lineItem->{$key} ?? $default;
        }
        return $default;
    }

    private function getNestedLineItemValue($lineItem, array $keys, $default = null)
    {
        $value = $lineItem;
        foreach ($keys as $key) {
            if (is_array($value)) {
                if (!array_key_exists($key, $value)) {
                    return $default;
                }
                $value = $value[$key];
                continue;
            }
            if (is_object($value)) {
                if (!isset($value->{$key})) {
                    return $default;
                }
                $value = $value->{$key};
                continue;
            }
            return $default;
        }
        return $value;
    }

    private function resolveWeightInKg($lineItem): float
    {
        $grams = (float)$this->getLineItemValue($lineItem, 'grams', 0);
        if ($grams > 0) {
            return $grams / 1000;
        }

        $searchText = trim(sprintf(
            '%s %s %s',
            (string)$this->getLineItemValue($lineItem, 'variant_title', ''),
            (string)$this->getLineItemValue($lineItem, 'name', ''),
            (string)$this->getLineItemValue($lineItem, 'title', ''),
        ));

        if ($searchText !== '' && preg_match('/(\d+(?:[.,]\d+)?)\s*(kg|g)\b/i', $searchText, $matches) === 1) {
            $amount = (float)str_replace(',', '.', $matches[1]);
            $unit = strtolower($matches[2]);
            return $unit === 'g' ? ($amount / 1000) : $amount;
        }

        return 0;
    }

    private function formatDate(DateTimeInterface|string $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y/m/d');
        }
        return date('Y/m/d', strtotime((string)$value));
    }

    private function formatTime(DateTimeInterface|string $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('H:i');
        }
        return date('H:i', strtotime((string)$value));
    }

    private function resolveOrderIdentifier(string $orderNumber, string $existingOrderIdentifier = ''): string
    {
        if ($existingOrderIdentifier !== '') {
            return $this->limitOrderIdentifier($existingOrderIdentifier);
        }

        $cleanOrderNumber = trim($orderNumber);
        if ($cleanOrderNumber !== '') {
            return $this->limitOrderIdentifier($cleanOrderNumber);
        }

        return $this->generateRandomOrderIdentifier();
    }

    private function limitOrderIdentifier(string $value): string
    {
        return mb_substr(trim($value), 0, 14);
    }

    private function generateRandomOrderIdentifier(): string
    {
        return strtoupper(Str::random(10));
    }

    private function buildTrackingCode(string $orderIdentifier): string
    {
        return sprintf('UHD%s', $orderIdentifier);
    }
}
