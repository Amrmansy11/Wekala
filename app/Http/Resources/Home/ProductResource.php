<?php

namespace App\Http\Resources\Home;

use App\Http\Resources\Home\ColorsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        $variants = $this->whenLoaded('variants', function ($variants) {
            return $variants->unique()->values()->all();
        });

        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'price' => $this->resource->consumer_price,
            'original_price' => $this->resource->wholesale_price,
            'stock' => $this->resource->stock,
            'total_pieces' => $variants ? collect($variants)->sum('total_pieces') : null,
            'image' => $this->resource->getFirstMediaUrl('images'),
            'colors' => $variants ? ColorsResource::collection($variants) : [],
        ];
    }
}
