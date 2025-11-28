<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GiftResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'source_product_id' => $this->source_product_id,
            'gift_product_id' => $this->gift_product_id,
//            'starts_at' => optional($this->starts_at)?->toIso8601String(),
//            'ends_at' => optional($this->ends_at)?->toIso8601String(),
//            'is_active' => (bool) $this->is_active,
            'archived_at' => optional($this->archived_at)?->toIso8601String(),
            'is_archived' => $this->isArchived(),
            'source_product' => new ProductResource($this->whenLoaded('sourceProduct')),
            'gift_product' => new ProductResource($this->whenLoaded('giftProduct')),
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}


