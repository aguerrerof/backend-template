<?php

namespace App\Http\Requests;

class GetRecurringOrdersByUserRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function getUserId(): ?string
    {
        return $this->attributes->get('shopify_uid') ?? $this->request->get('user_id');
    }
}
