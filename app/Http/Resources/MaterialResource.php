<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'products_count' => $this->resource->products_count,
            'is_active' => $this->resource->is_active ? true : false,

        ];
    }
}
