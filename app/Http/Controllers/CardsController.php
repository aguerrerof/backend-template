<?php

namespace App\Http\Controllers;

use App\Exceptions\CardBlockedException;
use App\Exceptions\CardCannotBeDeletedException;
use App\Exceptions\CardExceededException;
use App\Exceptions\CardNotFoundException;
use App\Exceptions\DocumentNotFoundException;
use App\Exceptions\EstablishmentNotFoundException;
use App\Exceptions\PaymentGatewayException;
use App\Exceptions\TransactionDeniedException;
use App\Exceptions\WrongCredentialsException;
use App\Exceptions\WrongOTPCodeException;
use App\Http\Formatters\ApiResponseFormatter;
use App\Http\Requests\CompleteCardRegistrationWithOTPRequest;
use App\Http\Requests\DeleteCardRequest;
use App\Http\Requests\GetCardsByUserRequest;
use App\Http\Requests\RegisterCardByUser;
use App\Http\Resources\UserCardsResource;
use App\Models\PaymentGateway\Response\ExternalServiceResponse;
use App\Models\UserCard;
use App\Services\PaymentGateways\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CardsController extends Controller
{
    public function __construct(private readonly PaymentGatewayService $paymentGatewayService)
    {
    }

    public function register(RegisterCardByUser $request): JsonResponse
    {
        try {
            /** @var ExternalServiceResponse $response */
            $response = $this->paymentGatewayService->registerCard(
                $request->getBuyerInformation(),
                $request->getCardInformation(),
                $request->getShippingAddress(),
                $request->getClientIp(),
                $request->getCurrency(),
                $request->getUserId(),
            );
            return response()->json(
                [
                    'data' => [
                        'message' => $response->getMessage(),
                        'details' => $response->getDetails(),
                        'status' => $response->getCustomStatus()->name,
                    ],
                ],
                $response->needExtraValidation() ?
                    Response::HTTP_ACCEPTED :
                    Response::HTTP_CREATED,
            );
        } catch (EstablishmentNotFoundException $exception) {
            return response()->json([
                'error' => $exception->getUserMessage(),
                'devError' => $exception->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (CardBlockedException|CardExceededException|WrongCredentialsException $exception) {
            return response()->json([
                'error' => $exception->getUserMessage(),
                'devError' => $exception->getMessage(),
            ], Response::HTTP_CONFLICT);
        } catch (\Exception $exception) {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                $exception->getMessage(),
                Response::HTTP_CONFLICT
            );
        }
    }

    public function completeRegistrationWithOtp(CompleteCardRegistrationWithOTPRequest $request): JsonResponse
    {
        try {
            $response = $this->paymentGatewayService->completeRegistrationWithOTP(
                $request->getTransactionId(),
                $request->getOTP(),
            );
            return response()->json([
                'data' => [
                    'message' => $response->getMessage(),
                ],
            ], Response::HTTP_CREATED);
        } catch (DocumentNotFoundException $exception) {
            return response()->json([
                'error' => __('custom.error_otp_process'),
                'devError' => $exception->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (
            TransactionDeniedException
            |CardBlockedException
            |CardExceededException
            |WrongCredentialsException
            |WrongOTPCodeException
            $exception
        ) {
            return response()->json([
                'error' => $exception->getUserMessage() ?? __('custom.transaction_not_allowed'),
                'devError' => $exception->getMessage(),
            ], Response::HTTP_CONFLICT);
        } catch (\Exception $exception) {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                $exception->getMessage(),
                Response::HTTP_CONFLICT
            );
        }
    }

    public function delete(string $token, DeleteCardRequest $deleteCardRequest): JsonResponse
    {
        try {
            /** @var ExternalServiceResponse $response */
            $response = $this->paymentGatewayService->deleteCard($token, $deleteCardRequest->getUserId());
            return response()->json(
                [
                    'data' => [
                        'message' => $response->getMessage(),
                        'details' => $response->getDetails(),
                        'status' => $response->getCustomStatus()->name,
                    ],
                ],
                Response::HTTP_OK,
            );
        } catch (PaymentGatewayException $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
                'devError' => $exception->getMessage(),
            ], Response::HTTP_CONFLICT);
        } catch (CardNotFoundException $exception) {
            return response()->json([
                'error' => $exception->getUserMessage(),
                'devError' => $exception->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (CardCannotBeDeletedException $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
                'devError' => $exception->getMessage(),
            ], Response::HTTP_CONFLICT);
        } catch (\Exception $exception) {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                $exception->getMessage(),
                Response::HTTP_CONFLICT
            );
        }
    }

    public function index(GetCardsByUserRequest $request): JsonResponse
    {
        return UserCardsResource::formatCollectionToApiResponse(
            UserCard::query()
                ->where('user_id', '=', $request->getUserId())
                ->paginate(),
            null,
        );
    }
}
