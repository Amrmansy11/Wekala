<?php

namespace App\Http\Resources\Store;

use App\Http\Resources\Home\ColorsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductsOfferResource extends JsonResource
{
    public function toArray($request): array
    {
        $variants = $this->resource->variants->unique()->values()->all();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->consumer_price,
            'original_price' => $this->wholesale_price,
            'image' => $this->resource->getFirstMediaUrl('images') ?: null,
            'colors' => $variants ? ColorsResource::collection($variants) : [],


        ];
    }
}
