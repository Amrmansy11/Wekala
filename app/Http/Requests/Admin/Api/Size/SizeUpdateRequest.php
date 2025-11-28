<?php

namespace App\Http\Requests\Admin\Api\Size;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $username
 * @property string $password
 */
class SizeUpdateRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        $sizeId = $this->route('size');
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('sizes', 'name')->ignore($sizeId)->whereNull('deleted_at'),
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->whereNull('parent_id'),
            ],

        ];
    }
}
