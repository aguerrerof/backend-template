<?php

namespace App\Http\Requests;

class PushNotificationTestRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1024',
            'user_id' => 'sometimes|string',
            'payload' => 'array'
        ];
    }

    /**
     * @return array<string, string|int>
     */
    public function getPayloadParameter(): array
    {
        return $this->input('payload', []);
    }
    public function getUserId()
    {
        return $this->get('user_id', null);
    }
}
