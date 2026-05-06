<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ShippingRateResource extends BaseJsonResource
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
            'code' => $this->code,
            'identifier' => $this->identifier,
            'title' => $this->title,
            'price' => $this->price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted' => (bool)$this->deleted_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
