<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'El asunto es requerido.',
            'subject.max' => 'El asunto no puede tener mas de :max caracteres.',
            'message.required' => 'El mensaje es requerido.',
            'message.max' => 'El mensaje no puede tener mas de :max caracteres.',
        ];
    }
}
