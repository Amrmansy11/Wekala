<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FollowResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->resource->store_name ?? null,
            'logo' => $this->resource->getFirstMediaUrl('vendor_logo') ?: null,
        ];
    }
}
