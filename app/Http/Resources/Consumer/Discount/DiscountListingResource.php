<?php

namespace App\Http\Resources\Consumer\Discount;

use Illuminate\Http\Resources\Json\JsonResource;

class DiscountListingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'percentage' => $this->percentage,
            'products' => DiscountProductPreviewResource::collection($this->whenLoaded('products')),
        ];
    }
}

