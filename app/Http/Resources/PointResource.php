<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PointResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'points' => (int) $this->points,
            'vendor_id' => (int) $this->vendor_id,
            'is_archived' => $this->isArchived(),
            'archived_at' => optional($this->archived_at)->toDateTimeString(),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}


