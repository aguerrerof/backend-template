<?php

namespace App\Http\Controllers;

use App\Exceptions\UserCouldNotBeDeletedException;
use App\Exceptions\UserWasAlreadyDeletedException;
use App\Http\Formatters\ApiResponseFormatter;
use App\Http\Requests\LinkNewDeviceRequest;
use App\Services\Authentication\AuthenticationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationController extends Controller
{
    public function __construct(private readonly AuthenticationService $authServices)
    {
    }

    public function authenticate(Request $request): JsonResponse
    {
        try {
            $response = $this->authServices->verifyAndCreateEmailShopify($request);
            if (is_null($response['error'])) {
                return $this->formatServiceSuccessResponse($response);
            }
            return ApiResponseFormatter::formatError($response['error'], $response['devError'] ?? null);
        } catch (UserWasAlreadyDeletedException $exception) {
            return ApiResponseFormatter::formatError(
                $exception->getUserMessage(),
                $exception->getMessage(),
                Response::HTTP_FORBIDDEN,
                [
                    'can_reactivate' => true,
                ],
            );
        }
    }

    public function verify(Request $request): JsonResponse
    {
        $response = $this->authServices->checkEmail($request);

        if (is_null($response['error'])) {
            return $this->formatServiceSuccessResponse($response);
        }

        return ApiResponseFormatter::formatError($response['error'], $response['devError'] ?? null);
    }

    public function delete(Request $request): JsonResponse
    {
        try {
            $this->authServices->deleteUser(
                $request->attributes->get('shopify_uid') ?? '',
                $request->attributes->get('firebase_uid') ?? '',
                $request->attributes->get('firebase_email') ?? '',
            );
        } catch (UserCouldNotBeDeletedException $exception) {
            return ApiResponseFormatter::formatError(
                $exception->getMessage(),
                $exception->getUserMessage(),
                Response::HTTP_CONFLICT,
            );
        } catch (UserWasAlreadyDeletedException $exception) {
            return ApiResponseFormatter::formatError(
                $exception->getMessage(),
                $exception->getUserMessage(),
                Response::HTTP_FORBIDDEN,
                [
                    'can_reactivate' => true,
                ],
            );
        } catch (\Exception $exception) {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                $exception->getMessage(),
            );
        }
        return ApiResponseFormatter::formatSuccess([], __('custom.user_was_deleted_successfully'));
    }

    public function reactivate(Request $request): JsonResponse
    {
        try {
            $response = $this->authServices->reactivateUser(
                $request->attributes->get('firebase_email'),
                $request->attributes->get('firebase_uid'),
            );
            return ApiResponseFormatter::formatSuccess(
                $response,
                __('custom.user_was_restored_successfully'),
            );
        } catch (ModelNotFoundException $exception) {
            return ApiResponseFormatter::formatError(
                __('custom.user_was_already_restored_successfully'),
                $exception->getMessage(),
                Response::HTTP_FORBIDDEN,
            );
        } catch (\Exception $exception) {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                $exception->getMessage(),
            );
        }
    }

    public function linkNewDevice(LinkNewDeviceRequest $request): JsonResponse
    {
        try {
            $this->authServices->linkNewDevice(
                $request->attributes->get('shopify_uid') ?? '',
                $request->validated()['device_id'],
                $request->validated()['firebase_token'],
            );
            return ApiResponseFormatter::formatSuccess(
                [],
                'Ok',
                Response::HTTP_CREATED,
            );
        } catch (\Exception $exception) {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                $exception->getMessage(),
            );
        }
    }

    public function unlinkDevice(Request $request, string $deviceId): JsonResponse
    {
        try {
            $this->authServices->unlinkDevice(
                $request->attributes->get('shopify_uid') ?? '',
                $deviceId,
            );
            return ApiResponseFormatter::formatSuccess(
                [],
                'Ok',
                Response::HTTP_NO_CONTENT,
            );
        } catch (ModelNotFoundException $exception) {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                $exception->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        } catch (\Exception $exception) {
            return ApiResponseFormatter::formatError(
                __('custom.error_trying_to_process_request'),
                $exception->getMessage(),
            );
        }
    }

    private function formatServiceSuccessResponse(array $serviceResponse): JsonResponse
    {
        $data = $serviceResponse['data'];
        if (!is_array($data)) {
            $data = ['value' => $data];
        }

        return ApiResponseFormatter::formatSuccess(
            $data,
            $serviceResponse['message'] ?? 'Ok',
        );
    }
}
