<?php

namespace App\Http\Requests\Vendor\Api\Auth;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $username
 * @property string $password
 */
class RegisterRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return
            [
                'action' => ['required', Rule::in(['login', 'register', 'reset_password', 'change_phone'])],
                'name' => ['required', 'string'],
                'email' => ['required', 'email', Rule::unique('vendor_users')],
                'phone' => ['required', Rule::unique('vendor_users')],
                'password' => ['required', 'string', 'min:8', 'max:50', 'regex:/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/u',  'confirmed'],
            ];
    }

}
