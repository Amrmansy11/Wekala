<?php

namespace App\Http\Resources\Store;

use App\Http\Resources\Home\ColorsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class NewArrivalProductsResource extends JsonResource
{
    public function toArray($request): array
    {
        $variants = $this->resource->variants->unique()->values()->all();
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'price' => $this->resource->consumer_price,
            'original_price' => $this->resource->wholesale_price,
            'image' => $this->resource->getFirstMediaUrl('images') ?: null,
            'colors' => $variants ? ColorsResource::collection($variants) : [],
            'created_at' => $this->resource->created_at

        ];
    }
}
