<?php

namespace App\Http\Requests\Admin\Api\Size;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $username
 * @property string $password
 */
class SizeStoreRequest extends ResponseShape
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
                'min:2',
                'max:255',
                Rule::unique('sizes', 'name')->whereNull('deleted_at'),
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->whereNull('parent_id'),
            ],

        ];
    }
}
