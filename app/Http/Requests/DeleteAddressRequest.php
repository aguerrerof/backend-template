<?php

namespace App\Http\Requests;

class DeleteAddressRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => 'required|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'address_id' => $this->route('address_id'),
        ]);
    }

    public function getCustomerId(): ?string
    {
        $customerId = $this->attributes->get('shopify_uid');
        return is_null($customerId)
        ? $this->get('shopify_customer_id')
            : $customerId;
    }

    public function getAddressId(): string
    {
        return $this->validated()['address_id'];
    }
}
