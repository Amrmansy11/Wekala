<?php

namespace App\Http\Resources\Consumer\Discount;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Consumer\Discount\DiscountListingResource;

class VendorsDiscountListingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'store_name' => $this->store_name,
            'followers_count' => $this->followers_count,
            'image' => $this->getFirstMediaUrl('vendor_logo'),
            'date' => optional($this->created_at)->format('d M'),
            'discounts' => DiscountListingResource::collection($this->whenLoaded('discounts')),
        ];
    }
}
