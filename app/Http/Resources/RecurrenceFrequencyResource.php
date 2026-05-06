<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class RecurrenceFrequencyResource extends BaseJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'is_default' => $this->is_default,
            'name' => $this->name,
            'value' => $this->value,
        ];
    }
}
