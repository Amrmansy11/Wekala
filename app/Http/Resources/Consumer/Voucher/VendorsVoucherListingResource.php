<?php

namespace App\Http\Resources\Consumer\Voucher;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Consumer\Voucher\VoucherListingResource;

class VendorsVoucherListingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->store_name,
            'followers_count' => $this->followers_count,
            'image' => $this->getFirstMediaUrl('vendor_logo'),
            'date' => optional($this->created_at)->format('d M'),
            'vouchers' => VoucherListingResource::collection($this->whenLoaded('vouchers')),
        ];
    }
}
