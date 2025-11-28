<?php

namespace App\Http\Resources\Consumer\Voucher;

use Illuminate\Http\Resources\Json\JsonResource;

class VoucherProductResource extends JsonResource
{
    public function toArray($request): array
    {
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
            'name_truncated' => strlen($this->name) > 30 ? substr($this->name, 0, 30) . '...' : $this->name,
            'image' => $this->getFirstMediaUrl('images'),
            'current_price' => number_format($discountedPrice, 0) . ' EGP',
            'original_price' => number_format($originalPrice, 0) . ' LE',
            'discounted_price' => $discountedPrice,
            'original_price_value' => $originalPrice,
            'rating' => $averageRating,
            'sold_count' => $soldCount,
            'sold_count_formatted' => $soldCountFormatted,
            'sold_display' => "(+{$soldCountFormatted} Sold)",
        ];
    }
}

