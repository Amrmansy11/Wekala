<?php

namespace App\Http\Resources\Consumer\Point;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Consumer\Point\PointListingResource;

class VendorsPointListingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->store_name,
            'followers' => $this->followers_count,
            'points' => PointListingResource::collection($this->whenLoaded('points')),
        ];
    }
}

