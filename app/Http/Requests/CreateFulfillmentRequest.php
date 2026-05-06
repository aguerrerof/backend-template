<?php

namespace App\Http\Requests;

use App\Rules\ProviderWeightLimit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CreateFulfillmentRequest extends FormRequest
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
    private const MAX_ITEM_WEIGHT_GRAMS = 99_999_000;
    private const MAX_ABSOLUTE_WEIGHT_GRAMS = 99_999_000;

    public function rules(): array
    {
        return [
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'line_items' => [
                'required',
                'array',
                'min:1',
            ],
            'logistic_provider_id' => [
                'required',
                'numeric',
                'exists:logistic_providers,id',
                new ProviderWeightLimit($this->getTotalWeight()),
            ],
            'order_id' => ['required', Rule::exists('orders', 'id')],
            'items_weight' => ['required', 'array', 'min:1'],
            'items_weight.*' => ['numeric', 'min:0.01', 'max:'.self::MAX_ITEM_WEIGHT_GRAMS],
        ];
    }

    public function messages(): array
    {
        return [
            'line_items.min' => 'Debe seleccionar al menos un producto para el despacho.',
            'line_items.required' => 'Debe seleccionar al menos un producto para el despacho.',
            'logistic_provider_id.required' => 'Debe seleccionar un proveedor logístico',
            'items_weight.required' => 'Debe ingresar los pesos de los productos.',
            'items_weight.*.min' => 'Cada producto debe tener un peso mayor a cero.',
            'items_weight.*.max' => 'Cada producto debe tener un peso menor a :max gramos.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $totalWeight = $this->getTotalWeight();
            if ($totalWeight <= 0) {
                $validator->errors()->add(
                    'items_weight',
                    __('custom.fulfillment_cannot_be_created_if_weight_is_zero'),
                );
                return;
            }

            $this->validateAbsoluteWeightLimit($validator, $totalWeight);
        });
    }

    public function getTotalWeight(): float
    {
        $totalWeights = $this->input('items_weight', []);
        return (float)collect($totalWeights)
            ->map(fn ($weight) => (float)$weight)
            ->sum();
    }

    private function formatKilograms(int $grams): string
    {
        $kg = $grams / 1000;
        $formatted = number_format($kg, 2, '.', '');
        return rtrim(rtrim($formatted, '0'), '.');
    }

    private function validateAbsoluteWeightLimit(Validator $validator, float $totalWeight): void
    {
        if ($totalWeight <= self::MAX_ABSOLUTE_WEIGHT_GRAMS) {
            return;
        }

        $validator->errors()->add(
            'items_weight',
            __('custom.fulfillment_weight_exceeds_absolute_limit', [
                'limit' => $this->formatKilograms(self::MAX_ABSOLUTE_WEIGHT_GRAMS),
            ]),
        );
    }
}
