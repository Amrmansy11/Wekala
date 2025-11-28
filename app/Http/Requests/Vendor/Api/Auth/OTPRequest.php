<?php

namespace App\Http\Requests\Vendor\Api\Auth;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $username
 * @property string $password
 */
class OTPRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return $this->getRules();
    }

    private function getRules(): array
    {
        return match (request()->action) {
            'register' => [
                'action' => ['required', Rule::in(['login', 'register', 'reset_password', 'change_phone'])],
                'name' => ['required', 'string'],
                'email' => ['required', 'email', Rule::unique('vendor_users')],
                'phone' => ['required', Rule::unique('vendor_users')],
                'password' => ['required', 'string', 'min:8', 'regex:/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/u', 'confirmed'],
            ],
            'login' => [
                'action' => ['required', Rule::in(['login', 'register', 'reset_password', 'change_phone'])],
                'phone' => ['required', 'exists:vendor_users,phone'],
                'password' => ['required', 'string', 'min:8', 'regex:/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/u', 'confirmed'],
            ],
            'reset_password' => [
                'action' => ['required', Rule::in(['login', 'register', 'reset_password', 'change_phone'])],
                'phone' => ['required', 'exists:vendor_users,phone'],
            ],
            'update_owner_info' => [
                'action' => ['required', Rule::in(['login', 'register', 'reset_password', 'change_phone', 'update_owner_info'])],
                'name' => ['required', 'string'],
                'email' => ['required', 'email', Rule::unique('vendor_users')->ignore(auth()->id())],
                'phone' => ['required', Rule::unique('vendor_users')->ignore(auth()->id())],
                'password' => ['required', 'string', 'min:8', 'regex:/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/u', 'confirmed'],
            ],
            default => [],
        };
    }
}
