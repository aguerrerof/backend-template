<?php

namespace App\Http\Requests;

use App\Helpers\LogisticProviderHelper;
use Illuminate\Foundation\Http\FormRequest;

class UrbanoCourierWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'num_pedido' => 'required|string',
            'chk' => 'required|string|max:10',
            'estado' => 'required|string|max:255',
            'sub_estado' => 'required|string|max:255',
            'fecha' => 'required|date_format:d/m/Y',
            'hora' => 'required|date_format:H:i',
            'ciudad' => 'required|string|max:20',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $apiKey = $this->header('api-key-urbano');
            $expectedKey = LogisticProviderHelper::getApiKey('UHD');

            if (empty($apiKey)) {
                $validator->errors()->add('api-key-urbano', 'API key requerida');
                return;
            }

            if ($apiKey !== $expectedKey) {
                $validator->errors()->add('api-key-urbano', 'API key invalida');
            }
        });
    }
}
