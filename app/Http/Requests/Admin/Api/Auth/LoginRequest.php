<?php

namespace App\Http\Requests\Admin\Api\Auth;

use App\Http\Requests\ResponseShape;
use App\Models\Admin;

/**
 * @property string $username
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
            'username' => ['required', 'string',
                function ($attribute, $value, $fail) {
                    $exists = Admin::query()
                        ->where('email', $value)
                        ->orWhere('username', $value)
                        ->exists();
                    if (!$exists) {
                        $fail('This email or username does not exist.');
                    }
                }
            ],
            'password' => ['required', 'string'],
        ];
    }
}
