<?php

namespace App\Http\Requests\Admin\Api\AdminUsers;

use App\Http\Requests\ResponseShape;
use App\Models\Admin;
use Illuminate\Validation\Rule;

/**
 * @property string $username
 * @property string $password
 */
class StoreAdminUserRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', Rule::unique('admins')],
            'email' => ['required', 'string', Rule::unique('admins')],
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
