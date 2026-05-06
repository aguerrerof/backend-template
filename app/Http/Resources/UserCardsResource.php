<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserCardsResource extends BaseJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'token' => $this->token,
            'status' => $this->status,
            'card_issuer' => $this->card_issuer,
            'card_info' => $this->card_info,
            'is_expired' => $this->is_expired,
            'client_name' => $this->client_name,
            'extra_information' => $this->extra_information,
        ];
    }
}
