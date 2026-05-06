<?php

namespace App\Http\Requests;

class GetCardsByUserRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function getUserId()
    {
        return $this->attributes->get(
            'shopify_uid',
            $this->query('user_id')
        );
    }
}
