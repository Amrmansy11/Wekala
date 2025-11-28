<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoriesShowResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name[en]' => $this->resource->getTranslation('name', 'en'),
            'name[ar]' => $this->resource->getTranslation('name', 'ar'),
            'size_required' => (bool)$this->resource->size_required,
            'disabled' => (bool)$this->resource->is_active,
        ];
    }
}
