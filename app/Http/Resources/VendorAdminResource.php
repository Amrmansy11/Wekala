<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorAdminResource extends JsonResource
{
    public function toArray($request): array
    {


        return [
            'id' => $this->resource->id,
            'store_type' => $this->resource->store_type,
            'store_name' => $this->resource->store_name,
            'image' => $this->resource->vendorUsers?->getFirstMediaUrl('vendor_user') ?: null,
            'logo' => $this->resource->getFirstMediaUrl('vendor_logo') ?: null,
            'cover' => $this->resource->getFirstMediaUrl('vendor_cover') ?: null,
            'status' => $this->resource->status,
            'joined_date' => $this->resource->created_at?->toDateString(),
            'followers_count' => $this->resource->followers_count ?? 0,
            'following_count' => $this->resource->following_count ?? 0,
            'branches_count' => $this->resource->branches_count ?? 0,
            'products_count' => $this->resource->products_count ?? 0,
            'order_count' => 150,
            'returned_order_count' => 5,
            'clients_count' => 1200,
            'favourites_count' => 4800,
        ];
    }
}
