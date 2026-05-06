<?php

namespace App\Http\Requests;

use App\Models\Enums\FulfillmentStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFulfillmentRequest extends FormRequest
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
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'tracking_url' => ['nullable', 'url', 'max:500'],
            'dispatched_at' => [
                'nullable',
                'date',
                sprintf('required_if:status,%s', FulfillmentStatus::DISPATCHED->value),
            ],
            'delivered_at' => [
                'nullable',
                'date',
                sprintf('required_if:status,%s', FulfillmentStatus::DELIVERED->value),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'dispatched_at.required_if' => 'La fecha de despacho es obligatoria cuando el estado es "Despachado".',
            'dispatched_at.date' => 'La fecha de despacho debe tener un formato válido.',
            'delivered_at.required_if' => 'La fecha de despacho es obligatoria cuando el estado es "Entregado".',
            'delivered_at.date' => 'La fecha de entrega debe tener un formato válido.',
        ];
    }
}
