<?php

namespace App\Http\Requests\Admin\Api\Brands;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $username
 * @property string $password
 */
class BrandUpdateRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        $brandId = $this->route('brand');
        return [
            'name.ar' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\p{Arabic}\p{N}\s]+$/u',
                Rule::unique('brands', 'name->ar')->ignore($brandId)->whereNull('deleted_at'),
            ],
            'name.en' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z0-9\s]+$/u',
                Rule::unique('brands', 'name->en')->ignore($brandId)->whereNull('deleted_at'),
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->whereNull('parent_id'),
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048',
            ],

        ];
    }
}
