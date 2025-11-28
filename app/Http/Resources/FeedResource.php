<?php

namespace App\Http\Resources;

use App\Http\Resources\Home\ProductFeedResource;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->vendor?->store_name,
            'media_url' => $this->resource->getFirstMediaUrl('feed_media'),
            'products' => ProductFeedResource::collection($this->products),

        ];
    }
}
