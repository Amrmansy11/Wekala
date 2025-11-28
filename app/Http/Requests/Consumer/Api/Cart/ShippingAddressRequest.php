<?php

namespace App\Http\Requests\Consumer\Api\Cart;

use App\Http\Requests\ResponseShape;

/**
 * @property string $address_type
 * @property string $recipient_name
 * @property string $recipient_phone
 * @property string $full_address
 * @property int $state_id
 * @property int $city_id
 */
class ShippingAddressRequest extends ResponseShape
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_type' => 'required|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:255',
            'full_address' => 'required|string|max:255',
            'state_id' => 'required|integer|exists:states,id',
            'city_id' => 'required|integer|exists:cities,id',
        ];
    }
}
