<?php

namespace App\Http\Requests\Consumer\Api\Auth;

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
                'email' => ['nullable', 'email', Rule::unique('users')],
                'phone' => ['required', Rule::unique('users')],
                'birthday' => ['nullable', 'date', 'before:today'],
                'password' => ['required', 'string', 'min:8', 'regex:/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/u', 'confirmed'],
            ],
            'login' => [
                'action' => ['required', Rule::in(['login', 'register', 'reset_password', 'change_phone'])],
                'phone' => ['required', 'exists:users,phone'],
                'password' => ['required', 'string', 'min:8', 'regex:/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/u', 'confirmed'],
            ],
            'reset_password' => [
                'action' => ['required', Rule::in(['login', 'register', 'reset_password', 'change_phone'])],
                'phone' => ['required', 'exists:users,phone'],
            ],
            default => [],
        };
    }
}
