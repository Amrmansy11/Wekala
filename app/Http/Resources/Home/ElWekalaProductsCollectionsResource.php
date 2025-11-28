<?php

namespace App\Http\Resources\Home;

use Illuminate\Http\Resources\Json\JsonResource;

class ElWekalaProductsCollectionsResource extends JsonResource
{
    public function toArray($request): array
    {

        return [
            'id' => $this->resource->product->id,
            'title' => $this->resource->product->name,
            'price' => $this->resource->product->consumer_price,
            'original_price' => $this->resource->product->wholesale_price,
            'image' => $this->resource->product->getFirstMediaUrl('images'),
        ];
    }
}
