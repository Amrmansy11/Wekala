<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'desc' => $this->desc,
            'start' => $this->start,
            'end' => $this->end,
            'type' => $this->type,
            'discount' => $this->discount,
            'quantity' => $this->quantity,
            'amount' => $this->amount,
            'buy' => $this->buy,
            'get' => $this->get,
            'logo' => $this->getFirstMediaUrl('logo'),
            'cover' => $this->getFirstMediaUrl('cover'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'is_active' => now()->between($this->start, $this->end),
            'creatable' => $this->whenLoaded('creatable', function () {
                return [
                    'id' => $this->creatable->id,
                    'name' => $this->creatable->name ?? $this->creatable->email,
                    'type' => $this->creatable_type,
                ];
            }),
        ];
    }
}
