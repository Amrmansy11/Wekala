<?php

namespace App\Http\Resources\Consumer\Offer;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Consumer\Offer\OfferListingResource;

class VendorsOfferListingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->store_name,
            'followers_count' => $this->followers_count,
            'offers' => OfferListingResource::collection($this->whenLoaded('offers')),

        ];
    }
}
