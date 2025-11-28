<?php

namespace App\Http\Resources;

use App\Models\Vendor;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorUserResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Vendor $vendor */
        $vendor = $this->resource->vendor;
        if (request()->hasHeader('vendor-id') && $vendorId = request()->header('vendor-id')) {
            $vendor = Vendor::query()->find($vendorId);
        }
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'photo' => $this->resource->getFirstMediaUrl('vendor_user'),
            'vendor_id' => $this->resource->vendor_id,
            'vendor' => new VendorResource($vendor),
            'first_step' => (bool)$vendor,
            'second_step' => $vendor && $vendor->hasMedia('vendor_national_id'),
            'roles' => $this->resource->getRoleNames(),
            'permissions' => $this->resource->getAllPermissions()->pluck('name')
        ];
    }
}
