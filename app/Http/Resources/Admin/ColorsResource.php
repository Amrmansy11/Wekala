<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ColorsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->color,
            'hex' => $this->resource->colorHex ? $this->resource->colorHex->hex_code : null,
            'images' => $this->resource->getMedia('variant_images')->map(fn($media) => $media->getUrl())->all(),
        ];
    }
}
