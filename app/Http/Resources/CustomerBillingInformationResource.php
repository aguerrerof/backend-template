<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CustomerBillingInformationResource extends BaseJsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'identification' => $this->identification,
            'type' => $this->type,
            'phone' => $this->phone,
            'is_default' => $this->is_default,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
            'deleted' => (bool)$this->deleted_at,
        ];
    }
}
