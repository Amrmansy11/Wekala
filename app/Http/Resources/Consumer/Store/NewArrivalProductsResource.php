<?php

namespace App\Http\Resources\Consumer\Store;

use App\Http\Resources\Home\ColorsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class NewArrivalProductsResource extends JsonResource
{
    public function toArray($request): array
    {
        $variants = $this->resource->variants->unique()->values()->all();
        // Calculate discounted price
        $originalPrice = $this->wholesale_price ?? $this->consumer_price;
        $discountPercentage = $this->discount_percentage ?? 0;

        $discountedPrice = $originalPrice - ($originalPrice * ($discountPercentage / 100));

        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_truncated' => strlen($this->name) > 30 ? substr($this->name, 0, 30) . '...' : $this->name,
            'image' => $this->getFirstMediaUrl('images'),
            'current_price' => number_format($discountedPrice, 0) . ' EGP',
            'original_price' => number_format($originalPrice, 0) . ' LE',
            'discounted_price' => $discountedPrice,
            'original_price_value' => $originalPrice,
            'colors' => $variants ? ColorsResource::collection($variants) : [],
            'created_at' => $this->created_at

        ];
    }
}
