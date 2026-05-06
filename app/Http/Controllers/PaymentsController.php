<?php

namespace App\Http\Controllers;

use App\Exceptions\ChallengeNotCompletedException;
use App\Exceptions\DocumentNotFoundException;
use App\Exceptions\PaymentGatewayException;
use App\Http\Formatters\ApiResponseFormatter;
use App\Http\Requests\Complete3dsValidationRequest;
use App\Http\Requests\ProcessNewPaymentRequest;
use App\Http\Requests\ShowChallengeRequest;
use App\Services\PaymentGateways\PaymentGatewayService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PaymentsController extends Controller
{
    public function __construct(private readonly PaymentGatewayService $paymentGatewayService)
    {
    }

    public function complete3dsValidation(Complete3dsValidationRequest $request): JsonResponse|View
    {
        try {
            $this->paymentGatewayService->completeTransactionThreeDs($request->getDataTransactionThreeDsResource());
            return ApiResponseFormatter::formatSuccess(
                [],
                __('custom.card_registered_successfully'),
            );
        } catch (PaymentGatewayException|DocumentNotFoundException|ChallengeNotCompletedException $exception) {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                $exception->getMessage(),
                Response::HTTP_CONFLICT,
            );
        } catch (\Exception $exception) {
            return view(
                'errors.500',
            );
        }
    }
    public function showChallenge(ShowChallengeRequest $request): View
    {
        return view(
            'payments.challenge',
            ['urlCallback' => $request->getUrlCallback(), 'parameters' => $request->getParameters() ?? []],
        );
    }

    public function store(ProcessNewPaymentRequest $request): JsonResponse
    {
        try {
            $response = $this->paymentGatewayService->processPaymentWithToken($request->getCreatePayment());
            return ApiResponseFormatter::formatSuccess(
                [
                    'message' => $response->getMessage(),
                    'details' => $response->getDetails(),
                    'status' => $response->getCustomStatus()->name,
                ],
                $response->getMessage(),
                Response::HTTP_CREATED
            );
        } catch (PaymentGatewayException $exception) {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                $exception->getMessage(),
                Response::HTTP_CONFLICT
            );
        }
    }
}
