<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CityShowResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name[en]' => $this->resource->getTranslation('name', 'en'),
            'name[ar]' => $this->resource->getTranslation('name', 'ar'),
            'state_id' => $this->state_id,

        ];
    }
}
