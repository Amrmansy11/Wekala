<?php

namespace App\Http\Requests\Vendor\Api\Store;

use App\Helpers\AppHelper;
use Illuminate\Validation\Rule;
use App\Http\Requests\ResponseShape;

/**
 * @property string $store_type
 * @property string $store_name
 * @property string $phone
 * @property int $category_id
 * @property int $state_id
 * @property int $city_id
 * @property string $address
 * @property string $description
 */
class StoreUpdateProfileRequest extends ResponseShape
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vendor_id = AppHelper::getVendorId();
        return [
            'store_type' => ['required', Rule::in(['retailer', 'seller'])],
            'store_name' => ['required', Rule::unique('vendors')->ignore($vendor_id)],
            'phone' => ['required', Rule::unique('vendors')->ignore($vendor_id)],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'state_id' => ['required', Rule::exists('states', 'id')],
            'city_id' => ['required', Rule::exists('cities', 'id')],
            'description' => ['nullable', 'string', 'min:2', 'max:255'],
            'address' => ['required_without_all:state_id,city_id'],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
