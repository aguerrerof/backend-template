<?php

namespace App\Http\Requests;

use App\Helpers\LogisticProviderHelper;
use Illuminate\Foundation\Http\FormRequest;

class LAARCourierWebhookRequest extends FormRequest
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
            'noGuia' => 'required|string',
        ];
    }
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $expectedKey = LogisticProviderHelper::getApiKey('LAAR');
            $apiKey = $this->header('api-key-laar');

            if ($apiKey !== $expectedKey) {
                $validator->errors()->add('api-key-laar', 'API key inválida');
            }
        });
    }

}
