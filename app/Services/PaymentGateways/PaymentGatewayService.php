<?php

namespace App\Services\PaymentGateways;

use App\Exceptions\CardBlockedException;
use App\Exceptions\CardCannotBeDeletedException;
use App\Exceptions\CardExceededException;
use App\Exceptions\ChallengeNotCompletedException;
use App\Exceptions\DocumentNotFoundException;
use App\Exceptions\EstablishmentNotFoundException;
use App\Exceptions\PaymentGatewayException;
use App\Exceptions\TransactionDeniedException;
use App\Exceptions\WrongCredentialsException;
use App\Exceptions\WrongOTPCodeException;
use App\Models\Enums\Currency;
use App\Models\PaymentGateway\BuyerInformation;
use App\Models\PaymentGateway\CardInformation;
use App\Models\PaymentGateway\CreatePayment;
use App\Models\PaymentGateway\DataTransactionThreeDsResource;
use App\Models\PaymentGateway\Response\ExternalServiceResponse;
use App\Models\PaymentGateway\ShippingAddress;
use Throwable;

interface PaymentGatewayService
{
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
        string $userId
    ): ExternalServiceResponse;

    /**
     * @param DataTransactionThreeDsResource $dsResource
     * @return void
     * @throws ChallengeNotCompletedException
     * @throws DocumentNotFoundException
     * @throws PaymentGatewayException
     */
    public function completeTransactionThreeDs(DataTransactionThreeDsResource $dsResource): void;

    /**
     * @param string $transactionId
     * @param string $otpCode
     * @return ExternalServiceResponse
     * @throws CardBlockedException
     * @throws CardExceededException
     * @throws DocumentNotFoundException
     * @throws EstablishmentNotFoundException
     * @throws PaymentGatewayException
     * @throws WrongCredentialsException
     * @throws WrongOTPCodeException
     * @throws TransactionDeniedException
     */
    public function completeRegistrationWithOTP(string $transactionId, string $otpCode): ExternalServiceResponse;

    /**
     * Processes a payment using a token with specific installment and tax details.
     *
     * @param CreatePayment $createPayment
     * @return ExternalServiceResponse
     * @throws PaymentGatewayException
     * @throws Throwable
     */
    public function processPaymentWithToken(CreatePayment $createPayment): ExternalServiceResponse;

    /**
     * @param string $token
     * @param string $userId
     * @return ExternalServiceResponse
     * @throws PaymentGatewayException
     * @throws CardCannotBeDeletedException
     */
    public function deleteCard(string $token, string $userId): ExternalServiceResponse;
}
