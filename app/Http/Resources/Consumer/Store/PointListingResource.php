<?php

namespace App\Http\Resources\Consumer\Store;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Consumer\Point\PointProductPreviewResource;

class PointListingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'points' => $this->points,
            'products' => PointProductPreviewResource::collection($this->whenLoaded('products')),
        ];
    }
}

