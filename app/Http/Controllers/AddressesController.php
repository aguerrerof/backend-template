<?php

namespace App\Http\Controllers;

use App\Exceptions\AddressAlreadyExistsException;
use App\Exceptions\AddressNotExistException;
use App\Exceptions\MissingCustomerAddressException;
use App\Exceptions\UserHasAlreadyDefaultAddress;
use App\Http\Formatters\ApiResponseFormatter;
use App\Http\Requests\CreateAddressRequest;
use App\Http\Requests\DeleteAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Services\Shop\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddressesController extends Controller
{
    public function __construct(private readonly AddressService $addressService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $customerId = $request->attributes->get('shopify_uid', $request->get('shopify_customer_id'));
            $response = $this->addressService->getByUser($customerId);
            if (!$response->isSuccess()) {
                return ApiResponseFormatter::formatError(
                    __('custom.error_trying_to_process_request'),
                    $response->getFullErrorMessage(),
                );
            }
            return ApiResponseFormatter::formatSuccess($response->getData(), 'Ok');
        } catch (MissingCustomerAddressException $exception) {
            return ApiResponseFormatter::formatError(
                $exception->getMessage(),
                $exception->getUserMessage(),
                Response::HTTP_NOT_FOUND
            );
        } catch (\Exception $exception) {
            return ApiResponseFormatter::formatError($exception->getMessage(), null);
        }
    }

    public function store(CreateAddressRequest $request): JsonResponse
    {
        try {
            $response = $this->addressService->create(
                $request->getCustomerId(),
                $request->getAddress(),
            );
            if (!$response->isSuccess()) {
                return ApiResponseFormatter::formatError(
                    __('custom.error_trying_to_process_request'),
                    $response->getFullErrorMessage(),
                );
            }
            return ApiResponseFormatter::formatSuccess($response->getData(), 'Ok', Response::HTTP_CREATED);
        } catch (AddressAlreadyExistsException|UserHasAlreadyDefaultAddress $exception) {
            return ApiResponseFormatter::formatError(
                $exception->getUserMessage(),
                $exception->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }
    }

    public function update(UpdateAddressRequest $request): JsonResponse
    {
        try {
            $response = $this->addressService->update(
                $request->getCustomerId(),
                $request->getAddress(),
            );
            if (is_null($response['error'])) {
                return ApiResponseFormatter::formatSuccess($response['data'] ?? [], $response['message']);
            } else {
                return ApiResponseFormatter::formatError($response['error'], $response['devError'] ?? null);
            }

        } catch (AddressAlreadyExistsException|UserHasAlreadyDefaultAddress $exception) {
            return ApiResponseFormatter::formatError(
                $exception->getUserMessage(),
                $exception->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteAddressRequest $request): JsonResponse
    {
        try {
            $response = $this->addressService->delete(
                $request->getCustomerId(),
                $request->getAddressId(),
            );
            if (is_null($response['error'])) {
                return ApiResponseFormatter::formatSuccess($response['data'] ?? [], $response['message']);
            } else {
                return ApiResponseFormatter::formatError($response['error'], $response['devError'] ?? null);
            }
        } catch (AddressNotExistException $exception) {
            return ApiResponseFormatter::formatError(
                $exception->getUserMessage(),
                $exception->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
    }
}
