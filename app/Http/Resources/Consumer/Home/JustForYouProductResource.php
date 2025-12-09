<?php
namespace App\Http\Resources\Consumer\Home;

use Illuminate\Http\Resources\Json\JsonResource;

class JustForYouProductResource extends JsonResource
{
    public function toArray($request): array
    {
        $type = 'normal';

        if ($this->discounts()->exists()) {
            $type = 'discount';
        } elseif ($this->points()->exists()) {
            $type = 'points';
        } elseif ($this->offer()->exists()) {
            $type = 'offer';
        }

        $originalPrice = $this->consumer_price ?? $this->wholesale_price;
        $discountPercentage = $this->discounts->first()->percentage ?? 0;
        $discountedPrice = $originalPrice - ($originalPrice * ($discountPercentage / 100));


        // Calculate sold count from order items
        $soldCount = $this->sold_count ?? 0;

        // Format sold count (e.g., 48000 -> "48K")
        $soldCountFormatted = $soldCount >= 1000
            ? number_format($soldCount / 1000, 1) . 'K'
            : (string) $soldCount;

        $averageRating = $this->whenLoaded('reviews', function () {
            return round($this->reviews->avg('rating') ?? 0, 1);
        }, 0);


        $point = $this->points->first();
        $offer = $this->offer->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $type, // النوع هنا
            'original_price' => $originalPrice,
            'percentage' => $discountPercentage,
            'discounted_price' => $discountedPrice,
            'image' => $this->resource->getFirstMediaUrl('images'),
            'rating' => $averageRating,
            'sold_count' => $soldCount,
            'sold_count_formatted' => $soldCountFormatted,
            'sold_display' => "(+{$soldCountFormatted} Sold)",
            'point_type' => $point?->type,
            'points'     => $point?->points,
            'offer_type' => $offer?->type,
            'offer_percentage' => $offer?->percentage,
        ];
    }
}
