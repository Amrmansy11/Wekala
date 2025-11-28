<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'reviewable_name' => $this->reviewable->name ?? 'Anonymous', // Adjust based on reviewable model
            'created_at' => $this->created_at->format('Y-m-d'),
            'has_images_or_videos' => $this->has_images_or_videos,
            'media' => $this->getMedia('images_videos')->map->getUrl(),
        ];
    }
}
