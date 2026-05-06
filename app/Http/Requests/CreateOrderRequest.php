<?php

namespace App\Http\Requests;

class CreateOrderRequest extends BaseFormRequest
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
            'products' => 'required|array',
            'address' => 'required|array',
            'user_card_id' => 'required|exists:user_cards,id',
            'shipping_code' => 'string|exists:shipping_rates,code',
            'recurring_id' => 'string',
            'source' => 'string',
        ];
    }
}
