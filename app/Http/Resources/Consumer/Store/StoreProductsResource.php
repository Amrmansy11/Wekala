<?php

namespace App\Http\Resources\Consumer\Store;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Consumer\Store\ColorsResource;

class StoreProductsResource extends JsonResource
{
    public function toArray($request): array
    {
        $originalPrice = $this->wholesale_price ?? $this->consumer_price;
        $discountPercentage = $this->discount_percentage ?? 0;

        $discountedPrice = $originalPrice - ($originalPrice * ($discountPercentage / 100));

        $variants = $this->resource->variants->unique()->values()->all();
        
        // Check if product has active points (using eager loaded relationship if available)
        $hasPoints = $this->resource->relationLoaded('points') 
            ? $this->resource->points->whereNull('archived_at')->isNotEmpty()
            : $this->resource->points()->whereNull('archived_at')->exists();
        
        // Check if product has active discounts (using eager loaded relationship if available)
        $hasDiscounts = $this->resource->relationLoaded('discounts')
            ? $this->resource->discounts->whereNull('archived_at')->isNotEmpty()
            : $this->resource->discounts()->whereNull('archived_at')->exists();
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_truncated' => strlen($this->name) > 30 ? substr($this->name, 0, 30) . '...' : $this->name,
            'current_price' => number_format($discountedPrice, 0) . ' EGP',
            'original_price' => number_format($originalPrice, 0) . ' LE',
            'discounted_price' => $discountedPrice,
            'original_price_value' => $originalPrice,
            'colors' => $variants ? ColorsResource::collection($variants) : [],
            'image' => $this->resource->getFirstMediaUrl('images') ?: null,
            'has_points' => $hasPoints,
            'has_discounts' => $hasDiscounts,
        ];
    }
}
