<?php

namespace App\Http\Requests;

use App\Helpers\WeightHelper;
use Illuminate\Foundation\Http\FormRequest;

class StoreLogisticProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:logistic_providers,code'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'api_url' => ['nullable', 'url', 'max:255'],
            'credentials' => ['nullable', 'string'],
            'config' => ['nullable', 'string'],
            'max_total_weight_kg' => ['nullable', 'numeric', 'min:0.01'],
            'can_cancel_orders' => ['nullable', 'boolean'],
        ];
    }
    public function validatedData(): array
    {
        $data = $this->validated();

        $data['credentials'] = $data['credentials']
            ? json_decode($data['credentials'], true)
            : null;

        $data['config'] = $data['config']
            ? json_decode($data['config'], true)
            : null;
        $data['can_cancel_orders'] = $this->boolean('can_cancel_orders');
        $data['max_total_weight_grams'] = WeightHelper::kgToGrams($data['max_total_weight_kg'] ?? null);
        unset($data['max_total_weight_kg']);

        return $data;
    }
}
