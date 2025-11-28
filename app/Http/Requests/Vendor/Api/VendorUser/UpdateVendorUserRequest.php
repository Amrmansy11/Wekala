<?php

namespace App\Http\Requests\Vendor\Api\VendorUser;


use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $role
 */
class UpdateVendorUserRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('user');
        return
            [
                'name' => ['required', 'string'],
                'email' => ['required', 'email', Rule::unique('vendor_users')->ignore($id)],
                'phone' => ['required', Rule::unique('vendor_users')->ignore($id)],
                'roles' => ['nullable', 'array'],
                'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'vendor')],
            ];
    }
}
