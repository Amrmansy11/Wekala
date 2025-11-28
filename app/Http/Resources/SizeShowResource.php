<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SizeShowResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'category_id' => $this->resource->category_id,
            'is_active' => $this->resource->is_active ? true : false,

        ];
    }
}
