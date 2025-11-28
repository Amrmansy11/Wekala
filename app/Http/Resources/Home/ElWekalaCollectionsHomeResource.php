<?php

namespace App\Http\Resources\Home;

use Illuminate\Http\Resources\Json\JsonResource;

class ElWekalaCollectionsHomeResource extends JsonResource
{
    public function toArray($request): array
    {
        $variants = $this->resource->product->variants->unique()->values()->all();
        return [
            'id' => $this->resource->product->id,
            'title' => $this->resource->product->name,
            'price' => $this->resource->product->consumer_price,
            'original_price' => $this->resource->product->wholesale_price,
            'total_pieces' => $variants ? collect($variants)->sum('total_pieces') : null,
            'colors' => $variants ? ColorsResource::collection($variants) : [],
            'image' => $this->resource->product->getFirstMediaUrl('images'),
        ];
    }
}
