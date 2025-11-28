<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PackingUnitShowResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name[ar]' => $this->resource->getTranslation('name', 'ar'),
            'name[en]' => $this->resource->getTranslation('name', 'en'),
            'category_id' => $this->resource->category_id,
            'is_active' => $this->resource->is_active ? true : false,

        ];
    }
}
