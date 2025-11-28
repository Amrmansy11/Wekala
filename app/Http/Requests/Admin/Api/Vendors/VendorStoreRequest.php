<?php

namespace App\Http\Requests\Admin\Api\Vendors;


use Illuminate\Validation\Rule;
use App\Http\Requests\ResponseShape;

/**
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $password
 * @property string $store_type
 * @property string $store_name
 * @property string $phone_vendor
 * @property int $category_id
 * @property int $state_id
 * @property int $city_id
 * @property string $description
 * @property string $address
 * @property string $logo
 * @property string $cover
 * @property string $national_id_file
 * @property string $tax_card_file
 */
class VendorStoreRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return
            [
                'name' => ['required', 'string'],
                'email' => ['required', 'email', Rule::unique('vendor_users')],
                'phone' => ['required', Rule::unique('vendor_users')],
                'password' => ['required', 'string', 'min:8', 'regex:/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/u', 'confirmed'],
                'image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
                'store_type' => ['required', Rule::in(['retailer', 'seller'])],
                'store_name' => ['required', Rule::unique('vendors')],
                'phone_vendor' => ['required', Rule::unique('vendors', 'phone')],
                'category_id' => ['required', Rule::exists('categories', 'id')],
                'state_id' => ['required', Rule::exists('states', 'id')],
                'city_id' => ['required', Rule::exists('cities', 'id')],
                'description' => ['nullable', 'string', 'min:2', 'max:255'],
                'address' => ['required_without_all:state_id,city_id'],
                'logo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
                'cover' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
                'national_id_file' => ['required', 'file', 'mimes:jpeg,jpg,png', 'max:2048'],
                'tax_card_file' => [
                    'file',
                    'mimes:jpeg,jpg,png',
                    'max:2048',
                    Rule::requiredIf($this->input('store_type') === 'seller'),
                ],
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
