<?php

namespace App\Http\Requests;

use App\Models\PaymentGateway\CreatePayment;

class ProcessNewPaymentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'base_amount_0' => 'required|numeric',
            'tax_amount' => 'required|numeric',
            'token' => 'required|string',
            'description' => 'required|string',
            'has_grace_period' => 'required|boolean',
            'apply_interest' => 'required|boolean',
            'installments' => 'required|integer',
            'additional_details' => 'string',
        ];
    }

    public function getCreatePayment(): CreatePayment
    {
        return CreatePayment::fromArray($this->validated());
    }
}
