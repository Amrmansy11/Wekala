<?php

namespace App\Http\Resources\Consumer\Products;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoriesResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'image' => $this->resource->getFirstMediaUrl('category_image'),
        ];
    }
}
