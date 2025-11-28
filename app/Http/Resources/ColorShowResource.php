<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ColorShowResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name[ar]' => $this->resource->getTranslation('name', 'ar'),
            'name[en]' => $this->resource->getTranslation('name', 'en'),
            'hex_code' => $this->resource->hex_code,
            'color' => $this->resource->color,
            'is_active' => $this->resource->is_active ? true : false,

        ];
    }
}
