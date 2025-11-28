<?php

namespace App\Http\Resources\Store;

use Illuminate\Http\Resources\Json\JsonResource;

class TrendingProductsStoreResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->consumer_price,
            'original_price' => $this->wholesale_price,
            'discount_percentage' => 5,
            'image' => $this->resource->getFirstMediaUrl('images') ?: null,

        ];
    }
}
