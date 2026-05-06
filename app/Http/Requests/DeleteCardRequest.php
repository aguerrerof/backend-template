<?php

namespace App\Http\Requests;

class DeleteCardRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function getUserId(): string
    {
        return $this->attributes->get(
            'shopify_uid',
            $this->query('user_id')
        );
    }
}
