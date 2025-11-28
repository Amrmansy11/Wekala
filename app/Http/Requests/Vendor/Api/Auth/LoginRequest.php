<?php

namespace App\Http\Requests\Vendor\Api\Auth;

use App\Models\VendorUser;
use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $phone
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
            'phone' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $exists = VendorUser::query()
                        ->where('phone', $value)
                        ->exists();
                    if (!$exists) {
                        $fail('This phone number does not exist.');
                    }
                }
            ],
            'password' => ['required', 'string'],
        ];
    }
}
