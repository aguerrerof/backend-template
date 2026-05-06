<?php

namespace App\Http\Requests;

class GetProductOfferRequest extends BaseFormRequest
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
            'id' => 'required|string',
            'quantity' => 'required|integer',
        ];
    }

    public function getVariantData(): array
    {
        return $this->validated();
    }

    public function getVariantId(): string
    {
        return $this->validated()['id'];
    }

    public function getQuantity(): int
    {
        return $this->validated()['quantity'];
    }
}
