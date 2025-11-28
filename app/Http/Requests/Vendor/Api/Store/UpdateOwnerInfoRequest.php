<?php

namespace App\Http\Requests\Vendor\Api\Store;

use Illuminate\Validation\Rule;

use App\Http\Requests\ResponseShape;

/**
 * @property string $action
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $password
 * @property string $password_confirmation
 */
class UpdateOwnerInfoRequest extends ResponseShape
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return
            [
                'action' => ['required', Rule::in(['login', 'register', 'reset_password', 'change_phone', 'update_owner_info'])],
                'name' => ['required', 'string'],
                'email' => ['required', 'email', Rule::unique('vendor_users')->ignore(auth()->id())],
                'phone' => ['required', Rule::unique('vendor_users')->ignore(auth()->id())],
                'password' => ['required', 'string', 'min:8', 'max:50', 'regex:/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/u',  'confirmed'],
            ];
    }
}
