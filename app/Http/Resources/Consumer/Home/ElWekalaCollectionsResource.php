<?php

namespace App\Http\Resources\Consumer\Home;

use Illuminate\Http\Resources\Json\JsonResource;

class ElWekalaCollectionsResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'id' => $this->resource->product->id,
            'title' => $this->resource->product->name,
            'image' => $this->resource->product->getFirstMediaUrl('images'),
        ];
    }
}
