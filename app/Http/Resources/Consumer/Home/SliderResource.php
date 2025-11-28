<?php

namespace App\Http\Resources\Consumer\Home;

use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
{
    public function toArray($request): array
    {
        $variants = $this->whenLoaded('variants')->unique()->values()->all();
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->name,
            'consumer_price' => $this->resource->consumer_price,
            'total_pieces' => $variants ? collect($variants)->sum('total_pieces') : null,
            'colors' => $variants ? ColorsResource::collection($variants) : [],
            'image' => $this->resource->getFirstMediaUrl('images'),
        ];
    }
}
