<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubSubCategoriesResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'main_category' => $this->resource?->parent?->parent?->name,
            'sub_category' => $this->resource->parent?->name,
            'name' => $this->resource->name,
            'image' => $this->resource->getFirstMediaUrl('category_image'),

        ];
    }
}
