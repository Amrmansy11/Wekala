<?php

namespace App\Http\Resources\Home;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVendorResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'price' => $this->resource->consumer_price,
            'original_price' => $this->resource->wholesale_price,
            'stock' => $this->resource->stock,
            'image' => $this->resource->getFirstMediaUrl('images'),
        ];
    }
}
