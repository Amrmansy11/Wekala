<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StatesShowResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name[ar]' => $this->resource->getTranslation('name', 'ar'),
            'name[en]' => $this->resource->getTranslation('name', 'en'),

        ];
    }
}
