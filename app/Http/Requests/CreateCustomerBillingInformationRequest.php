<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class CreateCustomerBillingInformationRequest extends BaseFormRequest
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
                Rule::unique('customer_billing_information', 'identification')
                    ->where(fn ($query) => $query->where('user_id', $this->getUserId()))
            ],
            'type' => 'required|string',
            'phone' => 'required|string',
            'is_default' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'El apellido es obligatorio.',
            'identification.required' => 'La identificación es obligatoria.',
            'identification.unique' => __('custom.identification_already_registered'),
            'type.required' => 'El tipo es obligatorio.',
            'phone.required' => 'El teléfono es obligatorio.',
            'is_default.required' => 'Debe indicar si es el valor predeterminado.',
            'is_default.boolean' => 'El valor predeterminado debe ser verdadero o falso.',
        ];
    }
    public function getUserId(): string
    {
        return $this->attributes->get('shopify_uid') ?? $this->request->get('user_id', 'test-user');
    }
}
