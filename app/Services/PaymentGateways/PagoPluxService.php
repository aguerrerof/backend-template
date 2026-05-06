<?php

namespace App\Services\PaymentGateways;

use App\Exceptions\CardBlockedException;
use App\Exceptions\CardCannotBeDeletedException;
use App\Exceptions\CardExceededException;
use App\Exceptions\CardInsufficientFundsException;
use App\Exceptions\CardNotFoundException;
use App\Exceptions\ChallengeNotCompletedException;
use App\Exceptions\DocumentNotFoundException;
use App\Exceptions\EstablishmentNotFoundException;
use App\Exceptions\PaymentGatewayException;
use App\Exceptions\TransactionDeniedException;
use App\Exceptions\WrongCredentialsException;
use App\Exceptions\WrongOTPCodeException;
use App\Models\ActivityLog;
use App\Models\Enums\Currency;
use App\Models\PaymentGateway\BuyerInformation;
use App\Models\PaymentGateway\CardInformation;
use App\Models\PaymentGateway\CreatePayment;
use App\Models\PaymentGateway\DataTransactionThreeDsResource;
use App\Models\PaymentGateway\Response\CustomStatus;
use App\Models\PaymentGateway\Response\ExternalServiceResponse;
use App\Models\PaymentGateway\ShippingAddress;
use App\Models\PendingUserTransaction;
use App\Models\RecurringOrder;
use App\Models\UserCard;
use Throwable;

class PagoPluxService implements PaymentGatewayService
{
    public const STATUS_INACTIVE_FOR_CARDS = 'Inactive';
    public const STATUS_ACTIVE_FOR_CARDS = 'Active';
    public const CALLBACK_MOBILE_APP = 'app://callbackCreditCard';

    public function __construct(private readonly PagoPluxClient $client)
    {
    }

    /**
     * @param DataTransactionThreeDsResource $dsResource
     * @return void
     * @throws ChallengeNotCompletedException
     * @throws DocumentNotFoundException
     * @throws PaymentGatewayException
     */
    public function completeTransactionThreeDs(DataTransactionThreeDsResource $dsResource): void
    {
        $uri = sprintf(
            '%s/integrations/dataTransactionThreeDsResource',
            $this->client->getBaseUrl(),
        );
        $transactionId = $dsResource->getPti();
        /** @var PendingUserTransaction $pendingTransaction */
        $pendingTransaction = PendingUserTransaction::query()
            ->where('transaction_id', '=', $transactionId)
            ->first();

        if (is_null($pendingTransaction)) {
            throw new DocumentNotFoundException(
                sprintf('Could not retrieve transaction id: %s', $transactionId),
            );
        }
        $payload = [
            'pti' => $transactionId,
            'pcc' => $dsResource->getPcc(),
            'ptk' => $dsResource->getPtk(),
            'prc' => $dsResource->getPrc(),
        ];
        $this->saveLog('INFO', 'PagoPlux complete 3ds request initiated', [
            'url' => $uri,
            'request_payload_non_sensitive' => $payload,
        ]);
        try {
            $response = $this->client->post(
                $uri,
                $payload,
            );
            $this->saveLog('INFO', 'PagoPlux complete 3ds response', [
                'url' => $uri,
                'request_payload_non_sensitive' => $payload,
                'response' => $response,
            ]);
            if ((int)$response['code'] !== 0) {
                $pendingTransaction->delete();
                throw new ChallengeNotCompletedException(
                    $response['description'] ?? $response['detail']['result']['description'] ?? null,
                    0,
                    null,
                );
            }
            $this->assignCardToUser($pendingTransaction->user_id, $response['detail']);
            $pendingTransaction->delete();
        } catch (PaymentGatewayException|DocumentNotFoundException $e) {
            $this->saveLog('ERROR', 'PagoPlux complete 3ds failed', [
                'url' => $uri ?? 'endpoint to complete 3ds card',
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * @param BuyerInformation $buyerInformation
     * @param CardInformation $cardInformation
     * @param ShippingAddress $shippingAddress
     * @param string $ipAddress
     * @param Currency $currency
     * @param string $userId
     * @return ExternalServiceResponse
     * @throws CardBlockedException
     * @throws CardExceededException
     * @throws EstablishmentNotFoundException
     * @throws PaymentGatewayException
     * @throws Throwable
     * @throws WrongCredentialsException
     * @throws WrongOTPCodeException
     */
    public function registerCard(
        BuyerInformation $buyerInformation,
        CardInformation $cardInformation,
        ShippingAddress $shippingAddress,
        string $ipAddress,
        Currency $currency,
        string $userId,
    ): ExternalServiceResponse {
        try {
            $symmetricKey = $this->client
                ->generateSymmetricKey()
                ->getSymmetricKey();

            $uri = sprintf('%s/credentials/addPaymentCardResource', $this->client->getBaseUrl());
            $payload = $this->preparePayload(
                $cardInformation,
                $symmetricKey,
                $buyerInformation,
                $shippingAddress,
                $currency,
                $ipAddress,
            );
            $this->saveLog('INFO', 'PagoPlux registerCard request initiated', [
                'url' => $uri,
                'request_payload_non_sensitive' => $payload,
                'client_ip' => $ipAddress,
            ]);

            $response = $this->client->postEncrypted(
                $uri,
                $payload,
                null,
            );
            $this->saveLog('INFO', 'PagoPlux registerCard response', [
                'url' => $uri,
                'request_payload_non_sensitive' => $payload,
                'response' => $response,
            ]);
            return $this->processResponse($response, $userId, $payload);
        } catch (Throwable $e) {
            $this->saveLog('ERROR', 'PagoPlux registerCard failed', [
                'url' => $uri ?? 'endpoint to register card',
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function saveLog(string $level, string $message, array $context = []): void
    {
        ActivityLog::create([
            'level' => $level,
            'message' => $message,
            'context' => json_encode($context),
        ]);
    }

    /**
     * @param CardInformation $cardInformation
     * @param string $symmetricKey
     * @param BuyerInformation $buyerInformation
     * @param ShippingAddress $shippingAddress
     * @param Currency $currency
     * @param string $ipAddress
     * @return array
     */
    public function preparePayload(
        CardInformation $cardInformation,
        string $symmetricKey,
        BuyerInformation $buyerInformation,
        ShippingAddress $shippingAddress,
        Currency $currency,
        string $ipAddress,
    ): array {
        return [
            'card' => [
                'number' => Helpers::encryptAES_ECB(
                    $cardInformation->getNumber(),
                    $symmetricKey,
                ),
                'expirationYear' => Helpers::encryptAES_ECB(
                    $cardInformation->getExpirationYear(),
                    $symmetricKey,
                ),
                'expirationMonth' => Helpers::encryptAES_ECB(
                    $cardInformation->getExpirationMonth(),
                    $symmetricKey,
                ),
                'cvv' => Helpers::encryptAES_ECB($cardInformation->getCvv(), $symmetricKey),
            ],
            'buyer' => [
                'documentNumber' => trim($buyerInformation->getDocumentNumber()),
                'firstName' => trim($buyerInformation->getFirstName()),
                'lastName' => trim($buyerInformation->getLastName()),
                'phone' => trim($buyerInformation->getPhoneNumber()),
                'email' => trim($buyerInformation->getEmail()),
            ],
            'shippingAddress' => [
                'country' => trim($shippingAddress->getCountry()),
                'city' => trim($shippingAddress->getCity()),
                'street' => trim($shippingAddress->getStreet()),
                'number' => trim($shippingAddress->getNumber()),
            ],
            'paramsRecurrent' => [
                'idPlan' => '',
                'idSuscription' => '',
            ],
            'currency' => $currency->value,
            'description' => 'Registra tarjeta',
            'clientIp' => $ipAddress,
            'idEstablecimiento' => base64_encode($this->client->getEstablishmentId()),
            'urlRetorno3ds' => app()->isLocal()
                ? config('app.url') . '/payments/3ds/callback'
                : self::CALLBACK_MOBILE_APP,
        ];
    }

    private function processResponse(array $response, string $userId, array $payload): ExternalServiceResponse
    {
        $customStatus = $this->getCustomStatus((int)$response['code']);
        $needExtraValidation = match ($customStatus) {
            CustomStatus::VALIDATION_OTP_REQUIRED,
            CustomStatus::VALIDATION_3D_REQUIRED,
            CustomStatus::TRANSACTION_PENDING_3DS_APPROVAL => true,
            default => false
        };
        $details = $response['detail'] ?? [];
        switch ($customStatus) {
            case CustomStatus::SUCCESS:
                $this->assignCardToUser($userId, $response['detail']);
                break;
            case CustomStatus::VALIDATION_3D_REQUIRED:
            case CustomStatus::TRANSACTION_PENDING_3DS_APPROVAL:
                PendingUserTransaction::create([
                    'type' => 'TRANSACTION_PENDING_3DS_APPROVAL',
                    'user_id' => $userId,
                    'transaction_id' => $response['detail']['idTransaction'],
                ]);
                if ($response['detail']['customFormChallenge']) {
                    $details = [];
                    $details['url_challenge'] = sprintf(
                        '%s/payments/challenge?%s',
                        config('app.url'),
                        http_build_query(['details' => $response['detail']]),
                    );
                }
                break;
            case CustomStatus::WRONG_OTP_CODE:
                throw new WrongOTPCodeException(
                    $response['description'],
                    0,
                    null,
                    __('custom.wrong_otp_code'),
                );
            case CustomStatus::WRONG_CREDENTIALS:
                throw new WrongCredentialsException(
                    $response['description'],
                    0,
                    null,
                    __('custom.wrong_credentials'),
                );
            case CustomStatus::EXCEEDED:
                throw new CardExceededException(
                    $response['description'],
                    0,
                    null,
                    __('custom.card_exceeded'),
                );
            case CustomStatus::ESTABLISHMENT_DOES_NOT_EXIST:
                throw new EstablishmentNotFoundException(
                    $response['description'],
                    0,
                    null,
                    __('custom.establishment_not_found'),
                );
            case CustomStatus::CARD_BLOCKED:
                throw new CardBlockedException(
                    $response['description'],
                    0,
                    null,
                    __('custom.card_blocked'),
                );
            case CustomStatus::TRANSACTION_DENIED:
                throw new TransactionDeniedException(
                    $response['description'],
                    0,
                    null,
                    __('custom.transaction_not_allowed'),
                );
            case CustomStatus::VALIDATION_OTP_REQUIRED:
                $payload['paramsOtp'] = $response['detail'];
                $idTransaction = $response['detail']['idTransaction'];
                PendingUserTransaction::create([
                    'user_id' => $userId,
                    'type' => 'OTP_VALIDATION_REQUIRED',
                    'transaction_id' => $idTransaction,
                    'payload' => json_encode($payload),
                    'symmetric_key' => $this->client->getSymmetricKey(),
                ]);
                $details = [];
                $details['idTransaction'] = $idTransaction;
                break;
        }

        return new ExternalServiceResponse(
            $response['description'],
            $customStatus,
            $needExtraValidation,
            $details,
        );
    }

    public function getCustomStatus(int $code): CustomStatus
    {
        return match ($code) {
            0 => CustomStatus::SUCCESS,
            1 => CustomStatus::WRONG_CREDENTIALS,
            100 => CustomStatus::VALIDATION_OTP_REQUIRED,
            102 => CustomStatus::WRONG_OTP_CODE,
            103 => CustomStatus::TRANSACTION_PENDING_3DS_APPROVAL,
            2 => CustomStatus::CARD_BLOCKED,
            302 => CustomStatus::ESTABLISHMENT_DOES_NOT_EXIST,
            304 => CustomStatus::EXCEEDED,
            107 => CustomStatus::TRANSACTION_DENIED,
        };
    }

    /**
     * @param string $userId
     * @param array $details
     * @return void
     * @throws DocumentNotFoundException
     */
    public function assignCardToUser(string $userId, array $details): void
    {
        if (!array_key_exists('token', $details)) {
            throw new DocumentNotFoundException('Token was not found in response from external service');
        }
        UserCard::create([
            'user_id' => $userId,
            'token' => $details['token'],
            'status' => self::STATUS_ACTIVE_FOR_CARDS,
            'extra_information' => $details,
        ]);
    }

    /**
     * @param string $transactionId
     * @param string $otpCode
     * @return ExternalServiceResponse
     * @throws CardBlockedException
     * @throws CardExceededException
     * @throws DocumentNotFoundException
     * @throws EstablishmentNotFoundException
     * @throws PaymentGatewayException
     * @throws TransactionDeniedException
     * @throws WrongCredentialsException
     * @throws WrongOTPCodeException
     */
    public function completeRegistrationWithOTP(string $transactionId, string $otpCode): ExternalServiceResponse
    {
        $uri = sprintf('%s/credentials/addPaymentCardResource', $this->client->getBaseUrl());
        /** @var PendingUserTransaction $pendingTransaction */
        $pendingTransaction = PendingUserTransaction::query()
            ->where('transaction_id', '=', $transactionId)
            ->first();

        if (is_null($pendingTransaction)) {
            throw new DocumentNotFoundException(
                sprintf('Could not retrieve transaction id: %s', $transactionId),
            );
        }

        $payload = $pendingTransaction->payload;
        $payload['paramsOtp']['otpCode'] = $otpCode;

        $this->saveLog('INFO', 'PagoPlux registerCard request initiated with OTP code', [
            'url' => $uri,
            'request_payload_non_sensitive' => $payload,
        ]);

        $response = $this->client->postEncrypted(
            $uri,
            $payload,
            $pendingTransaction->symmetric_key,
        );

        $this->saveLog('INFO', 'PagoPlux registerCard response with OTP code', [
            'url' => $uri,
            'request_payload_non_sensitive' => $payload,
            'response' => $response,
        ]);

        $response = $this->processResponse($response, $pendingTransaction->user_id, $payload);
        if ($response->getCustomStatus() === CustomStatus::SUCCESS) {
            $pendingTransaction->delete();
        }
        return $response;
    }

    /**
     * Processes a payment using a token with specific installment and tax details.
     *
     * @param CreatePayment $createPayment
     * @return ExternalServiceResponse
     * @throws PaymentGatewayException
     */
    public function processPaymentWithToken(CreatePayment $createPayment): ExternalServiceResponse
    {
        $uri = sprintf(
            '%s/admincards/paymentByTokenCardResource',
            $this->client->getBaseUrl(),
        );
        $payload = [
            'montobase0' => $createPayment->getBaseAmount0(),
            'montobase12' => $createPayment->getTaxAmount(),
            'token' => $createPayment->getToken(),
            'detalle' => $createPayment->getDescription(),
            'mesesgracia' => $createPayment->hasGracePeriod(),
            'intereses' => $createPayment->applyInterest(),
            'cuotas' => $createPayment->getInstallments(),
            'extras' => $createPayment->getAdditionalDetails(),
        ];

        $this->saveLog('INFO', 'PagoPlux payment by token request initiated', [
            'url' => $uri,
            'request_payload_non_sensitive' => $payload,
        ]);

        try {
            $response = $this->client->post(
                $uri,
                $payload,
            );
            $this->saveLog('INFO', 'PagoPlux payment by token response', [
                'url' => $uri,
                'request_payload_non_sensitive' => $payload,
                'response' => $response,
            ]);
            return new ExternalServiceResponse(
                $this->getUserMessageForPaymentProcess($response),
                ($response['code'] == 0)
                    ? CustomStatus::SUCCESS
                    : CustomStatus::TRANSACTION_DENIED,
                false,
                $response['detail'] ?? [],
            );
        } catch (PaymentGatewayException $e) {
            $this->saveLog('ERROR', 'PagoPlux payment by token failed', [
                'url' => $uri ?? 'endpoint to pay by token',
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * @param string $token
     * @param string $userId
     * @return ExternalServiceResponse
     * @throws CardCannotBeDeletedException
     * @throws PaymentGatewayException
     * @throws Throwable
     */
    public function deleteCard(string $token, string $userId): ExternalServiceResponse
    {
        if ($this->isCardAssignedToRecurringOrder($userId, $token) === true) {
            throw new CardCannotBeDeletedException(
                __('custom.card_cannot_be_deleted_because_is_assigned_recurring_order'),
            );
        }
        $uri = sprintf(
            '%s/admincards/updateStatusCardResource',
            $this->client->getBaseUrl(),
        );
        $payload = ['token' => $token, 'estado' => 0];
        $this->saveLog('INFO', 'PagoPlux delete card request initiated', [
            'url' => $uri,
            'request_payload_non_sensitive' => $payload,
        ]);
        try {
            $response = $this->client->post(
                $uri,
                $payload,
            );
            if ($response['code'] != 0) {
                throw new PaymentGatewayException($response['detail']);
            }
            $this->saveLog('INFO', 'PagoPlux delete card response', [
                'url' => $uri,
                'request_payload_non_sensitive' => $payload,
                'response' => $response,
            ]);

            $details = is_array($response['detail'])
                ? [$response['detail']]
                : [];

            $this->inactiveCard($token, $userId);

            return new ExternalServiceResponse(
                sprintf('Respuesta por parte de pasarela de pagos: %s', trim($response['description'])),
                CustomStatus::SUCCESS,
                false,
                $details,
            );
        } catch (Throwable $e) {
            $this->saveLog('ERROR', 'PagoPlux delete card failed', [
                'url' => $uri ?? 'endpoint to delete card by token',
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * @param string $userId
     * @param string $token
     * @return bool
     * @throws CardNotFoundException
     */
    public function isCardAssignedToRecurringOrder(string $userId, string $token): bool
    {
        $userCard = UserCard::query()
            ->where('user_id', '=', $userId)
            ->where('token', '=', $token)
            ->first();
        if (!$userCard) {
            throw new CardNotFoundException(
                sprintf('Card with token %s not found', $token),
                0,
                null,
                __('custom.card_not_found'),
            );
        }
        return RecurringOrder::query()
            ->where('user_card_id', '=', $userCard->id)
            ->exists();
    }

    private function inactiveCard(string $token, string $userId): void
    {
        $userCard = UserCard::query()
            ->where('user_id', '=', $userId)
            ->where('token', '=', $token)
            ->firstOrFail();
        $userCard->update(['status' => self::STATUS_INACTIVE_FOR_CARDS]);
        $userCard->delete();
    }

    private function getUserMessageForPaymentProcess(array $response): string
    {
        $description = $response['description'] ?? null;

        if (empty($description)) {
            return __('payment-gateway.transaction_processed_correctly');
        }
        $normalized = strtoupper(trim($description));
        if (preg_match('/\b51\b|FONDOS\s+INSU/i', $normalized)) {
            throw new CardInsufficientFundsException(
                __('custom.error_trying_to_get_registering_order'),
                0,
                null,
                __('payment-gateway.insufficient_funds')
            );
        }

        return trim($response['description']);
    }
}
