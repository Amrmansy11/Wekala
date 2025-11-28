<?php

namespace App\Http\Resources;

use App\Http\Resources\Home\ColorsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        $variants = $this->resource->variants->unique()->values()->all();
        $gallery = $this->getMedia('images')->map(fn($media) => $media->getUrl())->all();
        // ✅ Ratings breakdown
        $positiveRatingCount = $this->reviews()->where('rating', '>', 2)->count();
        $negativeRatingCount = $this->reviews()->where('rating', '<=', 2)->count();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type ?? 'b2c',
            'price' => $this->consumer_price,
            'original_price' => $this->wholesale_price,
            'discount_percentage' => 5,
            'published_at' => $this->published_at,
            'min_color' => $this->min_color,
            'views' => 5000,
            'favorites' => 25000,
            'sales' => 266000,
            'colors' => $variants ? ColorsResource::collection($variants) : [],
            'gallery' => $gallery,
            'reviews' => [
                'average_rating' => $this->reviews()->avg('rating') ?? 0,
                'review_count' => $this->reviews()->count(),
                'with_images_videos' => $this->reviews()->where('has_images_or_videos', true)->count(),
                'repeat_customers' => $this->reviews()->where('is_repeat_customer', true)->count(),
                'positive_count' => $positiveRatingCount,
                'negative_count' => $negativeRatingCount,
            ],
        ];
    }}
