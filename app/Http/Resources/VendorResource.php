<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'uuid' => $this->resource->uuid,
            'store_type' => $this->resource->store_type,
            'store_name' => $this->resource->store_name,
            'phone' => $this->resource->phone,
            'category_id' => $this->resource->category_id,
            'state_id' => $this->resource->state_id,
            'city_id' => $this->resource->city_id,
            'address' => $this->resource->address,
            'description' => $this->resource->description,
            'national_id_path' => $this->resource->getFirstMediaUrl('vendor_national_id') ?: null,
            'tax_card_path' => $this->resource->getFirstMediaUrl('vendor_tax_card') ?: null,
            'logo' => $this->resource->getFirstMediaUrl('vendor_logo') ?: null,
            'cover' => $this->resource->getFirstMediaUrl('vendor_cover') ?: null,
            'status' => $this->resource->status,
            'followers'=>$this->resource->followers()->count(),
            'favourites'=>4800,
            'sold'=>100,
        ];
    }
}
