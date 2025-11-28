<?php

namespace App\Http\Resources\Consumer\Gifts;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Consumer\Gifts\GiftProductPreviewResource;

class GiftListingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'source_product_id' => $this->source_product_id,
            'gift_product_id' => $this->gift_product_id,
            'archived_at' => optional($this->archived_at)?->toIso8601String(),
            'is_archived' => $this->isArchived(),
            'source_product' => new GiftProductPreviewResource($this->whenLoaded('sourceProduct')),
            'gift_product' => new GiftProductPreviewResource($this->whenLoaded('giftProduct')),
        ];
    }
}
