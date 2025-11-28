<?php

namespace App\Http\Resources\Consumer\Store;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreInfoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->store_name,
            'followers_count' => $this->followers_count ?? 0,
            'is_following' => (bool) $this->is_following,
            'logo' => $this->resource->getFirstMediaUrl('vendor_logo') ?: null,
            'cover' => $this->resource->getFirstMediaUrl('vendor_cover') ?: null


        ];
    }
}
