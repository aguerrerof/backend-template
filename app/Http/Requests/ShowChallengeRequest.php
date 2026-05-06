<?php

namespace App\Http\Requests;

class ShowChallengeRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'details.url' => ['string','required'],
            'details.parameters' => ['array','required'],
        ];
    }
    public function getUrlCallback(): string
    {
        return $this->query('details')['url'];
    }
    public function getParameters(): array
    {
        return (array)$this->query('details')['parameters'];
    }

}
