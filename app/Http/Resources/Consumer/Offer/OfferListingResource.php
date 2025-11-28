<?php

namespace App\Http\Resources\Consumer\Offer;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Consumer\Offer\OfferProductPreviewResource;

class OfferListingResource extends JsonResource
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
            'is_active' => now()->between($this->start, $this->end),
            'products' => OfferProductPreviewResource::collection($this->whenLoaded('products')),
        ];
    }
}
