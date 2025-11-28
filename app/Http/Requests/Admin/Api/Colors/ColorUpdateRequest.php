<?php

namespace App\Http\Requests\Admin\Api\Colors;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $username
 * @property string $password
 */
class ColorUpdateRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        $colorId = $this->route('color');
        return [
            'name.ar' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\p{Arabic}\p{N}\s]+$/u',
                Rule::unique('colors', 'name->ar')->ignore($colorId)->whereNull('deleted_at'),
            ],
            'name.en' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z0-9\s]+$/u',
                Rule::unique('colors', 'name->en')->ignore($colorId)->whereNull('deleted_at'),
            ],
            'hex_code' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('colors', 'hex_code')->ignore($colorId)->whereNull('deleted_at'),
            ],
            'color' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
        ];
    }
}
