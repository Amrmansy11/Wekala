<?php

namespace App\Http\Resources\Consumer\Store;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreBrandResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'logo' => $this->resource->getFirstMediaUrl('brand_logo') ?? null,

        ];
    }
}
