<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class LogisticProviderResource extends BaseJsonResource
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
            'name' => $this->name,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
        ];
    }
}
