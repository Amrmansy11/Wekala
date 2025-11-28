<?php

namespace App\Http\Requests\Consumer\Api\Auth;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $birthday
 * @property string $password
 * @property string $password_confirmation
 */
class RegisterRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['nullable', 'email:rfc,dns', 'string', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')],
            'birthday' => ['nullable', 'date', 'before:today'],
            'password' => ['required', 'string', 'min:8', 'regex:/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/u', 'confirmed'],
        ];
    }
}
