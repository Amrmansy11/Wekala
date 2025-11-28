<?php

namespace App\Http\Requests\Consumer\Api\Auth;

use App\Http\Requests\ResponseShape;
use App\Models\User;

/**
 * @property string $email_or_phone
 * @property string $password
 */
class LoginRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email_or_phone' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $exists = User::query()
                        ->where('email', $value)
                        ->orWhere('phone', $value)
                        ->exists();
                    if (!$exists) {
                        $fail('This email or phone number does not exist.');
                    }
                }
            ],
            'password' => ['required', 'string'],
        ];
    }
}
