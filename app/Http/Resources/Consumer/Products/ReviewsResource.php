<?php

namespace App\Http\Resources\Consumer\Products;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $positiveRatingCount = $this->where('rating', '>', 2)->count();
        $negativeRatingCount = $this->where('rating', '<=', 2)->count();
        return [
            'average_rating' => $this->avg('rating') ?? 0,
            'review_count' => $this->count(),
            'with_images_videos' => $this->where('has_images_or_videos', true)->count(),
            'repeat_customers' => $this->where('is_repeat_customer', true)->count(),
            'positive_count' => $positiveRatingCount,
            'negative_count' => $negativeRatingCount,
        ];
    }
}
