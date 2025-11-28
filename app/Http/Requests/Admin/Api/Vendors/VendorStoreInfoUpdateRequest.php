<?php

namespace App\Http\Requests\Admin\Api\Vendors;


use Illuminate\Validation\Rule;
use App\Http\Requests\ResponseShape;

/**
 * @property string $store_name
 * @property string $phone_vendor
 * @property int $category_id
 * @property int $state_id
 * @property int $city_id
 * @property string $description
 * @property string $address
 * @property string $logo
 * @property string $cover
 */
class VendorStoreInfoUpdateRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('vendor');
        return
            [
                'store_name' => ['required', Rule::unique('vendors')->ignore($id)],
                'phone_vendor' => ['required', Rule::unique('vendors', 'phone')->ignore($id)],
                'category_id' => ['required', Rule::exists('categories', 'id')],
                'state_id' => ['required', Rule::exists('states', 'id')],
                'city_id' => ['required', Rule::exists('cities', 'id')],
                'description' => ['nullable', 'string', 'min:2', 'max:255'],
                'address' => ['required_without_all:state_id,city_id'],
                'logo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
                'cover' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            ];
    }

    public function messages(): array
    {
        return [
            'phone.unique' => 'Manager Phone is already taken',
            'phone_vendor.unique' => 'Phone Vendor has already been taken',
        ];
    }
}
