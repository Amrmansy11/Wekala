<?php

namespace App\Http\Requests\Admin\Api\Governments;

use App\Models\Admin;
use Illuminate\Validation\Rule;
use App\Http\Requests\ResponseShape;

/**
 * @property string $name_ar
 * @property string $name_en
 * @property int $city_id
 */
class GovernmentStoreRequest extends ResponseShape
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
                Rule::unique('governments', 'name->ar'),
            ],
            'name.en' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z0-9\s]+$/u',
                Rule::unique('governments', 'name->en'),
            ],
            'city_id' => [
                'required',
                'exists:cities,id',
            ],

        ];
    }
}
