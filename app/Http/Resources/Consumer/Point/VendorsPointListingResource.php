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
            'image' => $this->getFirstMediaUrl('vendor_logo'),
            'date' => optional($this->created_at)->format('d M'),
            'points' => PointListingResource::collection($this->whenLoaded('points')),
        ];
    }
}

