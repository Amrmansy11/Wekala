<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PolicyShowResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name[ar]' => $this->resource->getTranslation('name', 'ar'),
            'name[en]' => $this->resource->getTranslation('name', 'en'),
            'title[ar]' => $this->resource->getTranslation('title', 'ar'),
            'title[en]' => $this->resource->getTranslation('title', 'en'),
            'desc[ar]' => $this->resource->getTranslation('desc', 'ar'),
            'desc[en]' => $this->resource->getTranslation('desc', 'en'),
            'type' => $this->resource->type,
        ];
    }
}
