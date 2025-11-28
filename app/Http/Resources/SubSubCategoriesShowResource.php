<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubSubCategoriesShowResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name[en]' => $this->resource->getTranslation('name', 'en'),
            'name[ar]' => $this->resource->getTranslation('name', 'ar'),
            'size_required' => (bool)$this->resource->size_required,
            'size' => $this->resource->size,
            'main_category_id' => $this->resource->parent?->parent_id,
            'sub_category_id' => $this->resource->parent_id,
            'image' => $this->resource->getFirstMediaUrl('category_image'),
            'disabled' => (bool)$this->resource->is_active,
        ];
    }
}
