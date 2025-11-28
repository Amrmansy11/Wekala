<?php

namespace App\Http\Requests\Admin\Api\Materials;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $username
 * @property string $password
 */
class MaterialUpdateRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        $materialId = $this->route('material');
        return [
            'name.ar' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\p{Arabic}\p{N}\s]+$/u',
                Rule::unique('materials', 'name->ar')->ignore($materialId)->whereNull('deleted_at'),
            ],
            'name.en' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z0-9\s]+$/u',
                Rule::unique('materials', 'name->en')->ignore($materialId)->whereNull('deleted_at'),
            ],

        ];
    }
}
