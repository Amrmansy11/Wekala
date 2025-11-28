<?php

namespace App\Http\Requests\Consumer\Api\Auth;

use App\Models\User;
use App\Http\Requests\ResponseShape;

/**
 * @property string $phone
 * @property string $password
 * @property string $password_confirmation
 */
class RestPasswordRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $exists = User::query()
                        ->where('phone', $value)
                        ->exists();
                    if (!$exists) {
                        $fail('This phone number does not exist.');
                    }
                }
            ],
            'password' => ['required', 'string', 'min:8', 'max:50', 'regex:/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/u', 'confirmed'],
        ];
    }
}
