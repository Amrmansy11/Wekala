<?php

namespace App\Http\Resources\Consumer\Products;

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

        // Calculate discounted price
        $originalPrice = $this->wholesale_price ?? $this->consumer_price;
        $discountPercentage = $this->discount_percentage ?? 0;

        $discountedPrice = $originalPrice - ($originalPrice * ($discountPercentage / 100));

        // Calculate sold count from order items
        $soldCount = $this->sold_count ?? 0;

        // Format sold count (e.g., 48000 -> "48K")
        $soldCountFormatted = $soldCount >= 1000
            ? number_format($soldCount / 1000, 1) . 'K'
            : (string) $soldCount;

        // Get average rating
        $averageRating = $this->whenLoaded('reviews', function () {
            return round($this->reviews->avg('rating') ?? 0, 1);
        }, 0);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type ?? 'b2c',
            'current_price' => number_format($discountedPrice, 0) . ' EGP',
            'original_price' => number_format($originalPrice, 0) . ' LE',
            'discounted_price' => $discountedPrice,
            'discount_percentage' => $discountPercentage,
            'original_price_value' => $originalPrice,
            'rating' => $averageRating,
            'sold_count' => $soldCount,
            'sold_count_formatted' => $soldCountFormatted,
            'sold_display' => "(+{$soldCountFormatted} Sold)",
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
    }
}
