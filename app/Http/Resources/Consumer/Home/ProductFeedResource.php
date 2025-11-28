<?php

namespace App\Http\Resources\Consumer\Home;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductFeedResource extends JsonResource
{
    public function toArray($request): array
    {

        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'consumer_price' => $this->resource->consumer_price,
            'stock' => $this->resource->stock,
            'image' => $this->resource->getFirstMediaUrl('images'),
        ];
    }
}
