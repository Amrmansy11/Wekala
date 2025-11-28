<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoriesVendorResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'size_required' => $this->resource->size_required,
            'size' => $this->resource->size,
            'image' => $this->resource->image ? $this->resource->image_path : null,

        ];
    }
}
