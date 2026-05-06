<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->uncompromised(),
            ],
            'is_admin' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => __('custom.wrong_format_email'),
            'email.unique' => __('custom.email_already_exists'),
            'password.required' => 'Please enter a password.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least :min characters.',
            'password.letters' => 'Password must include at least one letter.',
            'password.mixedCase' => 'Password must include both uppercase and lowercase letters.',
            'password.numbers' => 'Password must include at least one number.',
            'password.symbols' => 'Password must include at least one special symbol.',
            'password.uncompromised' => 'This password has appeared in a data leak. Please choose another.',
        ];
    }
}
