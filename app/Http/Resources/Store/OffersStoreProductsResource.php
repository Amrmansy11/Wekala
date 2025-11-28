<?php

namespace App\Http\Resources\Store;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Store\ProductsOfferResource;

class OffersStoreProductsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'desc' => $this->desc,
            'start' => $this->start,
            'end' => $this->end,
            'logo' => $this->resource->getFirstMediaUrl('logo') ?: null,
            'cover' => $this->resource->getFirstMediaUrl('cover') ?: null,
            'products' => ProductsOfferResource::collection($this->whenLoaded('products')),
        ];
    }
}
