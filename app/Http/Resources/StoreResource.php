<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'uuid' => $this->resource->uuid,
            'store_type' => $this->resource->store_type,
            'store_name' => $this->resource->store_name,
            'phone' => $this->resource->phone,
            'address' => $this->resource->address,
            'description' => $this->resource->description,
            'logo' => $this->resource->getFirstMediaUrl('vendor_logo') ?: null,
            'status' => $this->resource->status,
            'followers'=>$this->resource->followers_count,
            'following'=>$this->resource->following_count,
            'products_count'=>$this->resource->products_count,
            'favourites'=>4800,
            'sold'=>100,
            'products'=> ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
