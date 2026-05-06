<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateCustomerBillingInformationRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'email' => 'required|email',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'identification' => [
                'required',
                'string',
                Rule::unique('customer_billing_information', 'identification')
                    ->where(fn ($query) => $query->where('user_id', $this->getUserId()))
                    ->ignore($this->route('id')),
            ],
            'type' => 'required|string',
            'phone' => 'required|string',
            'is_default' => 'required|boolean',
        ];
    }
    public function getUserId(): string
    {
        return $this->attributes->get('shopify_uid') ?? $this->request->get('user_id', 'test-user');
    }

    public function messages(): array
    {
        return [
            'identification.unique' => __('custom.identification_already_registered'),
        ];
    }
}
