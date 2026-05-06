<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class DiscountResource extends BaseJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'enabled' => $this->enabled,
            'ends_at' => $this->ends_at,
            'rule_id' => $this->rule_id,
            'starts_at' => $this->starts_at,
            'title' => $this->title,
            'usage_count' => $this->usage_count,
            'usage_limit' => $this->usage_limit,
            'value' => $this->value,
            'value_type' => $this->value_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'deleted' => (bool)$this->deleted_at,
        ];
    }
}
