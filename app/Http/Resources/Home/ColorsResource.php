<?php

namespace App\Http\Resources\Home;

use Illuminate\Http\Resources\Json\JsonResource;

class ColorsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->color,
            'hex' => $this->resource?->colorHex?->hex_code,
            'bags' => $this->resource->bags,
            'total_pieces' => $this->resource->total_pieces,
            'quantity_b2b' => $this->resource->quantity_b2b,
            'quantity_b2c' => $this->resource->quantity_b2c,
            'images' => $this->resource->getMedia('variant_images')->map(fn($media) => $media->getUrl())->all(),
        ];
    }
}
