<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuestSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_name' => ['required', 'string', 'max:150'],
            'guest_email' => ['required', 'string', 'email', 'max:190'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'guest_name.required' => 'El nombre es requerido.',
            'guest_email.required' => 'El correo es requerido.',
            'guest_email.email' => 'El correo no tiene un formato valido.',
            'subject.required' => 'El asunto es requerido.',
            'message.required' => 'El mensaje es requerido.',
        ];
    }
}
