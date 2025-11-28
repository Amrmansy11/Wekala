<?php

namespace App\Http\Requests\Vendor\Api\Auth;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $store_type
 * @property string $store_name
 * @property string $phone
 * @property int $category_id
 * @property int $state_id
 * @property int $city_id
 * @property string $address
 * @property string $description
 * @property array $logo
 */
class VendorRegisterFirstStepRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return
            [
                'store_type' => ['required', Rule::in(['retailer', 'seller'])],
                'store_name' => ['required', Rule::unique('vendors')],
                'phone' => ['required', Rule::unique('vendors')],
                'category_id' => ['required', Rule::exists('categories', 'id')],
                'state_id' => ['required', Rule::exists('states', 'id')],
                'city_id' => ['required', Rule::exists('cities', 'id')],
                'description' => ['nullable', 'string', 'min:2', 'max:255'],
                'address' => ['required_without_all:state_id,city_id'],
                'logo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            ];
    }
}
