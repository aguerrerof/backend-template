<?php

namespace App\Services\Finance\Billing;

use App\Helpers\CustomLog;
use App\Helpers\OrderHelper;
use App\Models\Order;
use App\Models\OrderPayment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class UvaCloudBillingService implements BillingProviderInterface
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.uva_cloud.base_url');
        $this->apiKey = config('services.uva_cloud.api_key');
    }

    public function authorizeInvoice(string $id): array
    {
        $url = "{$this->baseUrl}/api/v1/ventas/facturas/autorizar/{$id}";
        $this->logExternalRequest('authorizeInvoice', 'POST', $url, [], [
            'headers' => $this->redactHeaders(['x-api-key' => $this->apiKey]),
        ]);

        $response = Http::withHeader('x-api-key', $this->apiKey)->post($url, []);

        $this->throwIfFailed($response, [
            'action' => 'authorizeInvoice',
        ]);
        return $this->decodeJsonOrFail($response, 'authorizeInvoice');
    }

    public function sendInvoice(string $id): array
    {

        $invoiceId = trim($id);
        if ($invoiceId === '') {
            throw new Exception('UvaCloudBillingService: invoiceId es requerido para enviar la factura por email.');
        }

        $url = "{$this->baseUrl}/api/v1/ventas/facturas/{$invoiceId}/send-email";
        $this->logExternalRequest('sendInvoice', 'POST', $url, [], [
            'headers' => $this->redactHeaders(['x-api-key' => $this->apiKey]),
        ]);

        $response = Http::withHeader('x-api-key', $this->apiKey)->post($url, []);

        $this->throwIfFailed($response, [
            'action' => 'sendInvoice',
            'invoice_id' => $invoiceId,
        ]);
        return $this->decodeJsonOrFail($response, 'sendInvoice');
    }

    public function createInvoice(OrderPayment $payment): array
    {
        try {
            $this->assertConfigured();
            $productDetails = $this->getProductDetails($payment->order);
            $client = $this->registerClient($payment);

            $clientId = (string)($client['id'] ?? '');
            if ($clientId === '') {
                throw new Exception('UvaCloudBillingService: no se pudo determinar el clientId para generar la factura.');
            }

            return $this->generateInvoice($productDetails, $clientId);
        } catch (Throwable $e) {
            $this->logExternalError('createInvoiceFailed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function generateInvoice(array $productDetails, string $clientId): array
    {

        $salesResources = $this->getSalesResources();
        $puntoEmision = $salesResources['puntosEmision'][0] ?? null;

        if (!is_array($puntoEmision) || !isset($puntoEmision['id'], $puntoEmision['numeroSerie'])) {
            throw new Exception('UvaCloudBillingService: no se encontraron recursos de venta (punto de emision/serie) para generar la factura.');
        }
        $url = "{$this->baseUrl}/api/v1/ventas/facturas";
        $payload = [
            'tipoDocumento' => 'FACTURA',
            'puntoEmisionId' => $puntoEmision['id'],
            'numeroSerie' => $puntoEmision['numeroSerie'],
            'fechaEmision' => Carbon::now()->format('Y-m-d'),
            'clienteId' => $clientId,
            'detalles' => $productDetails,
        ];

        $this->logExternalRequest('generateInvoice', 'POST', $url, $payload, [
            'headers' => $this->redactHeaders(['x-api-key' => $this->apiKey]),
        ]);

        $response = Http::withHeader('x-api-key', $this->apiKey)->post($url, $payload);

        $this->throwIfFailed($response, [
            'action' => 'generateInvoice',
        ]);
        $response =  $this->decodeJsonOrFail($response, 'generateInvoice');
        $this->logExternalRequest('generateInvoiceResponse', 'POST', $url, $payload, $response);
        return $response;
    }
    private function registerClient(OrderPayment $payment): array
    {

        $order = $payment->order;
        $customerBillingInformation = OrderHelper::getDefaultCustomerBillingInformation($order);

        if (!$customerBillingInformation) {
            throw new Exception('UvaCloudBillingService: no existe informacion de facturacion por defecto para el usuario de la orden.');
        }

        $dni = (string) $customerBillingInformation->identification;
        if ($dni === '') {
            throw new Exception('UvaCloudBillingService: la identificacion (DNI) de facturacion esta vacia.');
        }

        $existing = $this->getClientByDNI($dni);
        if (!empty($existing['data'])) {
            return $existing['data'][0] ?? [];
        }

        $url = "{$this->baseUrl}/api/v1/contactos/clientes";
        $payload = [
            'nombre' => trim((string) $customerBillingInformation->first_name . ' ' . (string) $customerBillingInformation->last_name),
            'tipoIdentificacion' => (string) $customerBillingInformation->type,
            'identificacion' => $dni,
            'email' => (string) $customerBillingInformation->email,
        ];

        $this->logExternalRequest('registerClient', 'POST', $url, $payload, [
            'headers' => $this->redactHeaders(['x-api-key' => $this->apiKey]),
            'payment_id' => $payment->id,
        ]);

        $responseFromCustomerRegistration = Http::withHeader('x-api-key', $this->apiKey)->post($url, $payload);

        $this->throwIfFailed($responseFromCustomerRegistration, [
            'action' => 'registerClient',
            'payment_id' => $payment->id,
        ]);

        $data = $this->decodeJsonOrFail($responseFromCustomerRegistration, 'registerClient');

        if (!is_array($data) || empty($data)) {
            throw new Exception('UvaCloudBillingService: respuesta inesperada al registrar cliente (se esperaba un arreglo con al menos un elemento).');
        }

        return $data[0] ?? [];
    }
    private function getClientByDNI(string $dni): array
    {

        $url = "{$this->baseUrl}/api/v1/contactos/clientes";
        $query = [
            'filter' => $dni,
        ];

        $this->logExternalRequest('getClientByDNI', 'GET', $url, $query, [
            'headers' => $this->redactHeaders(['x-api-key' => $this->apiKey]),
        ]);

        $response = Http::withHeader('x-api-key', $this->apiKey)->get($url, $query);
        $this->throwIfFailed($response, [
            'action' => 'getClientByDNI',
            'dni' => $dni,
        ]);

        return $this->decodeJsonOrFail($response, 'getClientByDNI');

    }

    private function getSalesResources(): array
    {

        $url = "{$this->baseUrl}/api/v1/ventas/facturas/resources";

        $this->logExternalRequest('getSalesResources', 'GET', $url, [], [
            'headers' => $this->redactHeaders(['x-api-key' => $this->apiKey]),
        ]);

        $response = Http::withHeader('x-api-key', $this->apiKey)->get($url);
        $this->throwIfFailed($response, [
            'action' => 'getSalesResources',
        ]);

        $json = $this->decodeJsonOrFail($response, 'getSalesResources');

        return $json['data'] ?? [];

    }
    private function getStandardProduct(string $sku): array
    {

        $url = "{$this->baseUrl}/api/v1/catalogo/producto";
        $query = [
            'filter' => $sku,
        ];

        $this->logExternalRequest('getStandardProduct', 'GET', $url, $query, [
            'headers' => $this->redactHeaders(['x-api-key' => $this->apiKey]),
        ]);

        $response = Http::withHeader('x-api-key', $this->apiKey)->get($url, $query);
        $this->throwIfFailed($response, [
            'action' => 'getStandardProduct',
            'sku' => $sku,
        ]);

        $json = $this->decodeJsonOrFail($response, 'getStandardProduct');

        return $json['data'] ?? [];

    }
    private function getProductDetails(Order $order): array
    {
        $orderPayload = $order->order;
        if (!isset($orderPayload->line_items) || !is_iterable($orderPayload->line_items)) {
            return [];
        }

        $standardProduct = $this->getStandardProduct('sku_generic');
        $products = [];

        foreach ($orderPayload->line_items as $lineItem) {
            if (!isset($lineItem->name)) {
                continue;
            }
            $products[] = [
                'productoId' => $standardProduct[0]['id'] ?? null,
                'productoNombre' => $lineItem->name,
                'precio' => (float)$lineItem->price ?? null,
                'cantidad' => $lineItem->quantity ?? null,
            ];
        }

        return $products;
    }

    private function assertConfigured(): void
    {
        if ((string) $this->baseUrl === '') {
            throw new Exception('UvaCloudBillingService: falta configurar services.uva_cloud.base_url.');
        }
        if ((string) $this->apiKey === '') {
            throw new Exception('UvaCloudBillingService: falta configurar services.uva_cloud.api_key.');
        }
    }

    private function throwIfFailed(Response $response, array $context = []): void
    {
        if (!$response->failed()) {
            return;
        }

        $status = $response->status();
        if (method_exists($response, 'effectiveUri')) {
            $uri = $response->effectiveUri();
            $url = $uri ? (string) $uri : null;
        } else {
            $url = null;
        }
        $body = (string) $response->body();
        $bodySnippet = $this->snippet($body);
        $ctx = $context === [] ? '' : (' context=' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $externalError = $this->extractExternalError($response);
        if ($externalError !== null) {
            $this->logExternalError('externalApiError', [
                'status' => $status,
                'url' => $url,
                'error' => $externalError,
                'context' => $context,
            ]);

            $message = $externalError['message'] ?? 'Error del API externo';
            $details = $externalError['details'] ?? null;
            $detailsSuffix = $details ? (' details=' . json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) : '';

            throw new Exception("UvaCloudBillingService: {$message} status={$status} url={$url}{$detailsSuffix}{$ctx}");
        }

        $this->logExternalError('externalRequestFailed', [
            'status' => $status,
            'url' => $url,
            'body' => $bodySnippet,
            'context' => $context,
        ]);

        throw new Exception("UvaCloudBillingService: request fallido status={$status} url={$url} body={$bodySnippet}{$ctx}");
    }

    private function decodeJsonOrFail(Response $response, string $action): array
    {
        $json = $response->json();

        if (!is_array($json)) {
            $bodySnippet = $this->snippet((string) $response->body());
            $this->logExternalError('invalidJsonResponse', [
                'action' => $action,
                'body' => $bodySnippet,
            ]);
            throw new Exception("UvaCloudBillingService: respuesta no es JSON valido en {$action}. body={$bodySnippet}");
        }

        if (isset($json['error']) && is_array($json['error'])) {
            $this->logExternalError('externalApiError', [
                'action' => $action,
                'error' => $json['error'],
            ]);

            $message = $json['error']['message'] ?? 'Error del API externo';
            $details = $json['error']['stackError'] ?? $json['error'];
            $detailsSuffix = $details ? (' details=' . json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) : '';

            throw new Exception("UvaCloudBillingService: {$message} action={$action}{$detailsSuffix}");
        }

        return $json;
    }

    private function snippet(string $value, int $max = 600): string
    {
        $value = trim($value);
        if ($value === '') {
            return '(empty)';
        }
        if (strlen($value) <= $max) {
            return $value;
        }

        return substr($value, 0, $max) . '...';
    }

    /**
     * Extrae un error estandarizado desde la respuesta del API externo si existe.
     *
     * Retorna:
     * - ['message' => string, 'details' => mixed] cuando puede parsear el error.
     * - null cuando no hay estructura reconocible.
     */
    private function extractExternalError(Response $response): ?array
    {
        $json = $response->json();
        if (!is_array($json) || !isset($json['error']) || !is_array($json['error'])) {
            return null;
        }

        $error = $json['error'];
        $message = $error['message'] ?? ($error['name'] ?? 'Error del API externo');
        $details = $error['stackError'] ?? $error;

        if (is_array($details)) {
            $validationMessages = array_values(array_filter(array_map(function ($item) {
                return is_array($item) && isset($item['message']) ? (string) $item['message'] : null;
            }, $details)));

            if ($validationMessages !== []) {
                $message = $message . ': ' . implode(' | ', $validationMessages);
            }
        }

        return [
            'message' => (string) $message,
            'details' => $details,
        ];
    }

    private function redactHeaders(array $headers): array
    {
        $redacted = [];
        foreach ($headers as $key => $value) {
            $lower = strtolower((string) $key);
            if ($lower === 'authorization' || $lower === 'x-api-key') {
                $redacted[$key] = '***';
                continue;
            }
            $redacted[$key] = $value;
        }

        return $redacted;
    }

    private function logExternalRequest(string $action, string $method, string $url, array $payload, array $context = []): void
    {
        try {
            CustomLog::saveLog('INFO', "UVA Cloud request: {$action}", array_merge([
                'action' => $action,
                'method' => $method,
                'url' => $url,
                'payload' => $payload,
            ], $context));
        } catch (Throwable) {

        }
    }

    private function logExternalError(string $action, array $context = []): void
    {
        try {
            CustomLog::saveLog('ERROR', "UVA Cloud error: {$action}", $context);
        } catch (Throwable) {
        }
    }
}
