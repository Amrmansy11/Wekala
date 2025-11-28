<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Admin\ColorsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class FlashSaleProductResource extends JsonResource
{
    public function toArray($request): array
    {
        $variants = $this->resource->variants->unique()->values()->all();

        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'price' => $this->resource->consumer_price,
            'original_price' => $this->resource->wholesale_price,
            'image' => $this->resource->getFirstMediaUrl('images'),
            'colors' => $variants ? ColorsResource::collection($variants) : [],

        ];
    }
}
