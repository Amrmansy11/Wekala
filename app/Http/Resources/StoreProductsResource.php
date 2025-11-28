<?php

namespace App\Http\Resources;

use App\Http\Resources\Home\ColorsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreProductsResource extends JsonResource
{
    public function toArray($request): array
    {
        $variants = $this->resource->variants->unique()->values()->all();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->consumer_price,
            'original_price' => $this->wholesale_price,
            'colors' => $variants ? ColorsResource::collection($variants) : [],
            'image' => $this->resource->getFirstMediaUrl('images') ?: null,


        ];
    }
}
