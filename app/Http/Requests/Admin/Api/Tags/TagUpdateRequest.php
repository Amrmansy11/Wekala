<?php

namespace App\Http\Requests\Admin\Api\Tags;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $username
 * @property string $password
 */
class TagUpdateRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        $tagId = $this->route('tag');
        return [
            'name.ar' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\p{Arabic}\p{N}\s]+$/u',
                Rule::unique('tags', 'name->ar')->ignore($tagId)->whereNull('deleted_at'),
            ],
            'name.en' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z0-9\s]+$/u',
                Rule::unique('tags', 'name->en')->ignore($tagId)->whereNull('deleted_at'),
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->whereNull('parent_id'),
            ],

        ];
    }
}
