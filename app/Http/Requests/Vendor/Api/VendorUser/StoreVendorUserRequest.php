<?php

namespace App\Http\Requests\Vendor\Api\VendorUser;


use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $password
 * @property array $roles
 * @property array $permissions
 */
class StoreVendorUserRequest extends ResponseShape
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
                'roles' => ['nullable', 'array'],
                'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'vendor')],
                'permissions' => ['required', 'array'],
                'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'vendor')],

            ];
    }
}
