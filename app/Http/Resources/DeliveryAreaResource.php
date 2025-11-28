<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryAreaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'state' => $this->resource->state?->name,
            'state_id' => $this->resource->state_id,
            'city' => $this->resource->city?->name,
            'city_id' => $this->resource->city_id,
            'district' => $this->resource->district,
            'price' => $this->resource->price,
        ];
    }
}
