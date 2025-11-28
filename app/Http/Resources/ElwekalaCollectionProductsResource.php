<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ElwekalaCollectionProductsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'price' => $this->resource->consumer_price,
            'original_price' => $this->resource->wholesale_price,
            'image' => $this->resource->getFirstMediaUrl('images'),
        ];
    }
}
