<?php

namespace App\Http\Requests\Admin\Api\Cities;

use Illuminate\Validation\Rule;
use App\Http\Requests\ResponseShape;

/**
 * @property string $username
 * @property string $password
 */
class CityUpdateRequest extends ResponseShape
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
                Rule::unique('cities', 'name->ar')->ignore($this->route('city'))->whereNull('deleted_at'),
            ],
            'name.en' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z0-9\s]+$/u',
                Rule::unique('cities', 'name->en')->ignore($this->route('city'))->whereNull('deleted_at'),
            ],
            'state_id' => [
                'required',
                'exists:states,id',
            ],

        ];
    }
}
