<?php

namespace App\Http\Resources;

use App\Models\Vendor;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminVendorUserResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Vendor $vendor */

        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'image' => $this->resource->getFirstMediaUrl('vendor_user') ?: null,
        ];
    }
}
