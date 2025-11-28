<?php

namespace App\Http\Requests\Admin\Api\Brands;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $name
 * @property string $logo
 * @property int $category_id
 */
class BrandStoreRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name.ar' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\p{Arabic}\p{N}\s]+$/u',
                Rule::unique('brands', 'name->ar')->whereNull('deleted_at'),
            ],
            'name.en' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z0-9\s]+$/u',
                Rule::unique('brands', 'name->en')->whereNull('deleted_at'),
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->whereNull('parent_id'),
            ],
            'logo' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048',
            ],

        ];
    }
}
