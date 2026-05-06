<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteCardRegistrationWithOTPRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'otp' => ['string', 'required', 'not_in' => ['']],
            'transaction_id' => ['string', 'required', 'not_in' => ['']],
        ];
    }

    public function getOTP(): string
    {
        return $this->input('otp');
    }

    public function getTransactionId(): string
    {
        return $this->input('transaction_id');
    }
}
