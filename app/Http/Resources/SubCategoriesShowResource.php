<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubCategoriesShowResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name[en]' => $this->resource->getTranslation('name', 'en'),
            'name[ar]' => $this->resource->getTranslation('name', 'ar'),
            'category_id'  => $this->resource->parent_id,
            'disabled' => (bool)$this->resource->is_active,
        ];
    }
}
