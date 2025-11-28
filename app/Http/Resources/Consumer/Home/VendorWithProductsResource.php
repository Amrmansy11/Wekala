<?php

namespace App\Http\Resources\Consumer\Home;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorWithProductsResource extends JsonResource
{
    public function toArray($request): VendorResource
    {
        $vendor = $this->first()->product->vendor;
        $vendor->setRelation('products', $this->map(fn($collection) => $collection->product));
        return new VendorResource($vendor);
    }
}
