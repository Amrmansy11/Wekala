<?php

namespace App\Http\Resources\Home;

use Illuminate\Http\Resources\Json\JsonResource;

class FlashSaleResource extends JsonResource
{
    public function toArray($request): array
    {

        $variants = $this->resource->product->variants->unique()->values()->all();
        return [
            'id' => $this->resource->product->id,
            'title' => $this->resource->product->name,
            'consumer_price' => $this->resource->product->consumer_price,
            'total_pieces' => $variants ? collect($variants)->sum('total_pieces') : null,
            'colors' => $variants ? ColorsResource::collection($variants) : [],
            'image' => $this->resource->product->getFirstMediaUrl('images'),
        ];
    }
}
