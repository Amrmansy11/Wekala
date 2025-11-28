<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BrandShowResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name[ar]' => $this->resource->getTranslation('name', 'ar'),
            'name[en]' => $this->resource->getTranslation('name', 'en'),
            'category_id' => $this->resource->category_id,
            'logo' => $this->resource->getFirstMediaUrl('brand_logo') ?? null,
            'is_active' => $this->resource->is_active ? true : false,

        ];
    }
}
