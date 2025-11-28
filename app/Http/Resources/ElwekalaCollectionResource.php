<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ElwekalaCollectionProductsResource;

class ElwekalaCollectionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'type' => $this->resource->type,
            'product' => new ElwekalaCollectionProductsResource($this->whenLoaded('product')),
        ];
    }
}
