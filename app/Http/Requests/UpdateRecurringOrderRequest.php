<?php

namespace App\Http\Requests;

use App\Models\Domain\UpdateRecurringOrder;

class UpdateRecurringOrderRequest extends BaseFormRequest
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
            'frequency' => 'string',
            'next_charge_date' => ['date', 'after:now'],
            'line_items' => ['array', 'min:1'],
            'line_items.*.variantId' => ['required', 'string'],
            'line_items.*.quantity' => ['required', 'integer', 'min:1'],
            'line_items.*.requiresShipping' => ['required', 'boolean'],
            'line_items.*.taxable' => ['required', 'boolean'],
            'line_items.*.price' => ['required', 'numeric', 'min:0'],
            'line_items.*.title' => ['required', 'string'],
            'line_items.*.imageUrl' => ['nullable', 'url'],
            'shipping_address' => 'array',
            'user_card_id' => 'int|exists:user_cards,id',
            'notes' => 'string',
            'email' => 'string',
        ];
    }

    public function getUpdateRecurringOrder(): UpdateRecurringOrder
    {
        $payload = $this->validated();
        $payload['id'] = $this->route('id');
        return UpdateRecurringOrder::fromArray($payload);
    }
}
