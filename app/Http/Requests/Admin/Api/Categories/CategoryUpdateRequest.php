<?php

namespace App\Http\Requests\Admin\Api\Categories;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $username
 * @property string $password
 */
class CategoryUpdateRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category');
        return [
            'name.ar' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[\p{Arabic}\p{N}\s]+$/u',
                Rule::unique('categories', 'name->ar')->ignore($categoryId)->whereNull('deleted_at'),
            ],
            'name.en' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9\s]+$/u',
                Rule::unique('categories', 'name->en')->ignore($categoryId)->whereNull('deleted_at'),
            ],
            'parent_id' => [
                'nullable',
                'exists:categories,id',
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048',
            ],
            'size' => [
                'nullable',
                'string',
                Rule::in(['tshirt', 'pants']),
            ],
            'size_required' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}
