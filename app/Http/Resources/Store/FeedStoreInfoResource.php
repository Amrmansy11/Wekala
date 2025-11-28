<?php

namespace App\Http\Resources\Store;

use Illuminate\Http\Resources\Json\JsonResource;

class FeedStoreInfoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'media' => $this->resource->getFirstMediaUrl('feed_media') ?: null,
        ];
    }
}
