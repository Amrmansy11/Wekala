<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BestSellingStoreResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->consumer_price,
            'original_price' => $this->wholesale_price,
            'discount_percentage' => 5,
            "reviews_avg_rating" => round($this->reviews_avg_rating, 1) ?? 0,
            'image' => $this->resource->getFirstMediaUrl('images') ?: null,

        ];
    }
}
