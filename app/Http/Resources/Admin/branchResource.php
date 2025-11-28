<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class branchResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'store_name' => $this->resource->store_name,
            'store_type' => $this->resource->store_type,
            'phone' => $this->resource->phone,
            'state' => $this->resource->state->name,
            'city' => $this->resource->city->name,
            'address' => $this->resource->address,
            'name_manager' => $this->resource->vendorUsers->name,
            'image_manager' => $this->resource->vendorUsers->getFirstMediaUrl('vendor_user'),
        ];
    }
}
