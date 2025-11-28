<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BranchDetailsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'uuid' => $this->resource->uuid,
            'store_name' => $this->resource->store_name,
            'store_type' => $this->resource->store_type,
            'phone' => $this->resource->phone,
            'manager_name' => $this->resource->vendorUsers->name,
            'state' => $this->resource->state->name,
            'city' => $this->resource->city->name,
            'address' => $this->resource->address,
        ];
    }
}
