<?php

namespace App\Http\Resources\Consumer\Home;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->resource->id,
            'name'        => $this->resource->store_name,
            'logo'        => $this->resource->getFirstMediaUrl('vendor_logo') ?? null,
            'followers'   => $this->followers_count ?? 0,
            'views'       => $this->views_count ?? 0,
            'is_following' =>  false,
            'products'    => ProductVendorResource::collection($this->whenLoaded('products')),

        ];
    }
}
