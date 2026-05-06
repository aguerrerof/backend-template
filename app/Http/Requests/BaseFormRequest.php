<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'data'     => null,
            'message'  => null,
            'error'    => $this->formatValidationErrors($validator->errors()->toArray()),
            'devError' => json_encode($validator->errors()),
        ], 422));
    }
    private function formatValidationErrors(array $errors): string
    {
        $messages = collect($errors)->flatten()->toArray();
        return implode(', ', $messages);
    }
}
