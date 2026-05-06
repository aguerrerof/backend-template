<?php

namespace App\Services\PaymentGateways;

use App\Exceptions\PaymentGatewayException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class PagoPluxClient
{
    private PendingRequest $client;
    private string $symmetricKey;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $idClient,
        private readonly string $secret,
        private readonly string $apiPublicKey,
        private readonly string $establishmentId
    ) {
        $authorizationHeader = base64_encode(sprintf('%s:%s', $this->idClient, $this->secret));
        $this->client = Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'Authorization' => "Basic $authorizationHeader",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->timeout(30);
    }

    public function get(string $uri, array $query = []): array
    {
        try {
            $response = $this->client->get($uri, $query);
            return $this->handleResponse($response);
        } catch (Throwable $e) {
            throw new PaymentGatewayException(
                sprintf(
                    'Error on endpoint %s, response:%s',
                    $uri,
                    $e->getMessage(),
                ),
                0,
                $e,
            );
        }
    }

    public function post(string $uri, array $data = []): array
    {
        try {
            $response = $this->client->post($uri, $data);
            return $this->handleResponse($response);
        } catch (Throwable $e) {
            throw new PaymentGatewayException(
                sprintf(
                    'Error on endpoint %s, response:%s',
                    $uri,
                    $e->getMessage(),
                ),
                0,
                $e,
            );
        }
    }

    public function postEncrypted(
        string $uri,
        array $requestBody,
        ?string $symmetricKey
    ): array {
        try {
            $authorizationHeader = base64_encode(sprintf('%s:%s', $this->idClient, $this->secret));
            $clientWithEncryptedKey = Http::baseUrl($this->baseUrl)
                ->withHeaders([
                    'Authorization' => "Basic $authorizationHeader",
                    'simetricKey' => Helpers::encryptRSA($symmetricKey ?? $this->symmetricKey, $this->apiPublicKey),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->timeout(30);

            $response = $clientWithEncryptedKey->post($uri, $requestBody);

            return $this->handleResponse($response);
        } catch (Throwable $e) {
            throw new PaymentGatewayException(
                sprintf(
                    'Error from endpoint %s: description: %s',
                    $uri,
                    $e->getMessage(),
                ),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * Only for debug purposes
     * @param string $fullUrl
     * @param string $encryptedSymmetricKey
     * @param array $encryptedCardData
     * @param array $additionalData
     * @return string
     */
    public function getPostEncryptedCurlCommand(
        string $fullUrl,
        string $encryptedSymmetricKey,
        array $encryptedCardData,
        array $additionalData = [],
    ): string {
        $requestBody = array_merge($additionalData, $encryptedCardData);

        $authorizationHeader = base64_encode(sprintf('%s:%s', $this->idClient, $this->secret));

        $headers = [
            sprintf("'Authorization: Basic %s'", $authorizationHeader),
            sprintf("'simetricKey: %s'", $encryptedSymmetricKey),
            "'Accept: application/json'",
            "'Content-Type: application/json'",
        ];

        $jsonBody = json_encode($requestBody, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        $escapedJsonBody = str_replace("'", "'\\''", $jsonBody);

        $command = sprintf(
            "curl -X POST \\\n  '%s' \\\n",
            $fullUrl,
        );

        foreach ($headers as $header) {
            $command .= sprintf("  -H %s \\\n", $header);
        }

        $command .= sprintf("  -d '%s'", $escapedJsonBody);

        return $command;
    }

    protected function handleResponse(Response $response): array
    {
        if ($response->successful()) {
            return $response->json();
        }
        $statusCode = $response->status();
        $errorMessage = $response->json('message') ?? $response->body();

        throw new PaymentGatewayException(
            sprintf('Payment gateway error  %s: %s', $statusCode, $errorMessage),
            $statusCode,
        );
    }

    public function getSymmetricKey(): string
    {
        return $this->symmetricKey;
    }

    public function generateSymmetricKey(): self
    {
        $this->symmetricKey = Helpers::generateSymmetricKey();
        return $this;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getEstablishmentId(): string
    {
        return $this->establishmentId;
    }
}
