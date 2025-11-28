<?php

namespace App\Http\Resources\Consumer\Feed;

use Illuminate\Http\Resources\Json\JsonResource;

class FeedResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'media_url' => $this->resource->getFirstMediaUrl('feed_media'),
        ];
    }
}
