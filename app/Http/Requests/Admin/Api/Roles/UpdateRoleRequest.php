<?php

namespace App\Http\Requests\Admin\Api\Roles;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $username
 * @property string $password
 */
class UpdateRoleRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        $modelId = $this->route('role');
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'admin')
                    ->ignore($modelId),
            ],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => [
                'required',
                Rule::exists('permissions', 'name')->where('guard_name', 'admin')
            ],
        ];
    }
}
