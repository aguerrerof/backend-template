<?php

namespace App\Http\Controllers;

use App\Http\Formatters\ApiResponseFormatter;
use App\Http\Requests\CreateCustomerBillingInformationRequest;
use App\Http\Requests\GetCustomerBillingInformationByUserRequest;
use App\Http\Requests\UpdateCustomerBillingInformationRequest;
use App\Http\Resources\CustomerBillingInformationResource;
use App\Models\CustomerBillingInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CustomersBillingInformationController extends Controller
{
    public function index(GetCustomerBillingInformationByUserRequest $request): JsonResponse
    {
        return CustomerBillingInformationResource::formatCollectionToApiResponse(
            CustomerBillingInformation::query()
                ->where('user_id', '=', $request->getUserId())
                ->get(),
            'Registro de facturación obtenidos exitosamente',
        );
    }

    public function store(CreateCustomerBillingInformationRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $customerBillingInformation = CustomerBillingInformation::getDefaultByUser($request->getUserId());
        if ($payload['is_default'] && !is_null($customerBillingInformation)) {
            $customerBillingInformation->update(['is_default' => false]);
        }
        $payload['user_id'] = $request->getUserId();
        $record = CustomerBillingInformation::query()->create($payload);
        return (new CustomerBillingInformationResource($record))
            ->toApiResponse('Registro de facturación creados correctamente', Response::HTTP_CREATED);
    }

    public function update(
        UpdateCustomerBillingInformationRequest $request,
        string $id,
    ): JsonResponse {
        try {
            $payload = $request->validated();
            $defaultCustomerBillingInformation = CustomerBillingInformation::getDefaultByUser($request->getUserId());
            $record = CustomerBillingInformation::findOrFail($id);
            if (
                ($payload['is_default'] ?? false)
                && $defaultCustomerBillingInformation
                && $defaultCustomerBillingInformation->id !== $record->id
            ) {
                $defaultCustomerBillingInformation->update(['is_default' => false]);
            }
            $record->update($payload);
            return (new CustomerBillingInformationResource($record))
                ->toApiResponse('Registro de facturación actualizados correctamente', Response::HTTP_OK);
        } catch (\Exception $e) {
            return ApiResponseFormatter::formatError(
                message: 'Hubo un error al actualizar el registro de facturación',
                devError: $e->getMessage(),
                status: Response::HTTP_CONFLICT,
            );
        }
    }

    public function delete(string $id): JsonResponse
    {
        try {
            $record = CustomerBillingInformation::findOrFail($id);
            $record->delete();
            return ApiResponseFormatter::formatSuccess(
                data: [],
                message: 'Registro de facturación eliminado correctamente',
            );
        } catch (\Exception $e) {
            return ApiResponseFormatter::formatError(
                message: 'Hubo un error al eliminar el registro de facturación',
                devError: $e->getMessage(),
                status: Response::HTTP_NOT_FOUND,
            );
        }
    }
}
