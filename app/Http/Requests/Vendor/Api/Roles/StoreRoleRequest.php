<?php

namespace App\Http\Requests\Vendor\Api\Roles;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $name
 */
class StoreRoleRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'vendor')
            ],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => [
                'required',
                Rule::exists('permissions', 'name')->where('guard_name', 'vendor')
            ],
        ];
    }
}
