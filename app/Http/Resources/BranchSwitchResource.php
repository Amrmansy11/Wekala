<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BranchSwitchResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'uuid' => $this->resource->uuid,
            'store_name' => $this->resource->store_name,
            'state' => $this->resource->state->name,
            'city' => $this->resource->city->name,
            'address' => $this->resource->address,
        ];
    }
}
