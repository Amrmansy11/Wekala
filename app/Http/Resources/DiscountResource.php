<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DiscountResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'percentage' => $this->percentage,
            'vendor_id' => $this->vendor_id,
            'is_archived' => $this->isArchived(),
            'archived_at' => $this->archived_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'products' => GeneralResource::collection($this->whenLoaded('products')),
            'vendor' => $this->whenLoaded('vendor', function () {
                return [
                    'id' => $this->vendor->id,
                    'store_name' => $this->vendor->store_name,
                ];
            }),
        ];
    }
}







