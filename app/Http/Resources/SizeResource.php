<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SizeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'category' => $this->resource?->category?->name,
            'products_count' => $this->resource->products_count,
            'is_active' => $this->resource->is_active ? true : false,

        ];
    }
}
