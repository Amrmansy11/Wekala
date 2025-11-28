<?php

namespace App\Http\Resources\Consumer\Store;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductFeedResource extends JsonResource
{
    public function toArray($request): array
    {
        $originalPrice = $this->wholesale_price ?? $this->consumer_price;
        $discountPercentage = $this->discount_percentage ?? 0;

        $discountedPrice = $originalPrice - ($originalPrice * ($discountPercentage / 100));

        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->getFirstMediaUrl('images'),
            'price' => number_format($discountedPrice, 2) . ' EGP',
            'original_price' => $originalPrice,
            'discounted_price' => $discountedPrice,
        ];
    }
}
