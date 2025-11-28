<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ColorResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'hex_code' => $this->resource->hex_code,
            'color' => $this->resource->color,
            'products_count' => $this->resource->products_count,
            'is_active' => $this->resource->is_active ? true : false,

        ];
    }
}
