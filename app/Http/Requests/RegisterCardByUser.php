<?php

namespace App\Http\Requests;

use App\Models\Enums\Currency;
use App\Models\PaymentGateway\BuyerInformation;
use App\Models\PaymentGateway\CardInformation;
use App\Models\PaymentGateway\ShippingAddress;
use Illuminate\Validation\Rules\Enum;

class RegisterCardByUser extends BaseFormRequest
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
            'card.number' => ['required', 'string'],
            'card.expirationYear' => ['required', 'string', 'digits:2'],
            'card.expirationMonth' => [
                'required',
                'string',
                'regex:/^(0[1-9]|1[0-2])$/',
            ],
            'card.cvv' => ['required', 'string'],
            'buyer.documentNumber' => ['required', 'string'],
            'buyer.firstName' => ['required', 'string', 'regex:/^[\pL\s\'-]+$/u'],
            'buyer.lastName' => ['required', 'string', 'regex:/^[\pL\s\'-]+$/u'],
            'buyer.phone' => ['required', 'string'],
            'buyer.email' => ['required', 'string', 'email'],
            'shippingAddress.country' => ['required', 'string'],
            'shippingAddress.city' => ['required', 'string'],
            'shippingAddress.street' => ['required', 'string'],
            'shippingAddress.number' => ['required', 'string'],
            'currency' => ['string', new Enum(Currency::class)],
            'paramsOtp.otpCode' => ['string'],
            'paramsOtp.idTransaction' => ['string'],
            'paramsOtp.sessionId' => ['string'],
            'paramsOtp.tkn' => ['string'],
            'paramsOtp.tknky' => ['string'],
            'paramsOtp.tkniv' => ['string'],
        ];
    }

    public function getCardInformation(): CardInformation
    {
        return new CardInformation(
            (string)$this->input('card')['number'],
            (string)$this->input('card')['expirationYear'],
            (string)$this->input('card')['expirationMonth'],
            (string)$this->input('card')['cvv'],
        );
    }

    public function getShippingAddress(): ShippingAddress
    {
        return new ShippingAddress(
            $this->input('shippingAddress')['country'],
            $this->input('shippingAddress')['city'],
            $this->input('shippingAddress')['street'],
            $this->input('shippingAddress')['number'],
        );
    }

    public function getBuyerInformation(): BuyerInformation
    {
        return new BuyerInformation(
            $this->input('buyer')['documentNumber'],
            $this->input('buyer')['firstName'],
            $this->input('buyer')['lastName'],
            $this->input('buyer')['phone'],
            $this->input('buyer')['email'],
        );
    }

    public function getCurrency(): Currency
    {
        if ($currency = $this->input('currency')) {
            return Currency::tryFrom($currency);
        }
        return Currency::USD;
    }

    public function getUserId()
    {
        return $this->attributes->get('shopify_uid') ?? $this->request->get('user_id');
    }

    public function messages(): array
    {
        return [
            'card.expirationMonth.regex' => 'The expiration month must be in a valid two-digit format (e.g., 01 for January, 12 for December).',
            'card.expirationYear.digits' => 'The expiration year must be a two-digit year (e.g., 25 for 2025).',
            'card.cvv.digits_between' => 'The CVV must be between 3 and 4 digits.',
            'card.number.numeric' => 'The card number must be numeric.',
            'buyer.firstName.alpha' => 'The first name field must only contain letters.',
            'buyer.lastName.alpha' => 'The last name field must only contain letters.',
        ];
    }
}
