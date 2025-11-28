<?php

namespace App\Http\Resources\Consumer\Store;

use Illuminate\Http\Resources\Json\JsonResource;

class SubSubCategoriesStoreResource extends JsonResource
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
