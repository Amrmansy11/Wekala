<?php

namespace App\Http\Resources\Consumer\Cart;


use App\Models\CartShippingAddress;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CartShippingAddress */
class CartShippingAddressesResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'address_type' => $this->address_type,
            'recipient_name' => $this->recipient_name,
            'recipient_phone' => $this->recipient_phone,
            'full_address' => $this->full_address,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

}