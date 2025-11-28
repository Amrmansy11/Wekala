<?php

namespace App\Http\Resources;

use App\Http\Resources\AdminVendorUserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorShowAdminResource extends JsonResource
{
    public function toArray($request): array
    {


        return [
            'id' => $this->resource->id,
            'store_type' => $this->resource->store_type,
            'store_name' => $this->resource->store_name,
            'phone' => $this->resource->phone,
            'category_id' => $this->resource->category_id,
            'state_id' => $this->resource->state_id,
            'city_id' => $this->resource->city_id,
            'address' => $this->resource->address,
            'description' => $this->resource->description,
            'status' => $this->resource->status,
            'national_id_path' => $this->resource->getFirstMediaUrl('vendor_national_id') ?: null,
            'tax_card_path' => $this->resource->getFirstMediaUrl('vendor_tax_card') ?: null,
            'cover' => $this->resource->getFirstMediaUrl('vendor_cover') ?: null,
            'logo' => $this->resource->getFirstMediaUrl('vendor_logo') ?: null,
            'joined_date' => $this->resource->created_at?->toDateString(),
            'followers_count' => $this->resource->followers_count,
            'following_count' => $this->resource->following_count,
            'branches_count' => $this->resource->branches_count,
            'products_count' => $this->resource->products_count,
            'order_count' => 150,
            'returned_order_count' => 5,
            'clients_count' => 1200,
            'favourites_count' => 4800,
            'vendor_user' => new AdminVendorUserResource($this->resource->users->first()),
        ];
    }
}
