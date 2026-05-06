<?php

namespace App\Http\Requests;

use App\Models\Shopify\Address;

class UpdateAddressRequest extends BaseFormRequest
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
            'id' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'province_code' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'country_code' => 'string',
            'default' => 'boolean',
        ];
    }

    public function getAddress(): Address
    {
        return new Address(
            $this->validated()['first_name'],
            $this->validated()['address_line_1'],
            $this->validated()['address_line_2'],
            $this->validated()['city'],
            $this->validated()['province_code'],
            $this->validated()['phone'],
            $this->validated()['country_code'],
            $this->validated()['default'] ?? false,
            $this->validated()['id'],
        );
    }

    public function getCustomerId(): ?string
    {
        return $this->attributes->get(
            'shopify_uid',
            $this->get('shopify_customer_id'),
        );
    }
}
