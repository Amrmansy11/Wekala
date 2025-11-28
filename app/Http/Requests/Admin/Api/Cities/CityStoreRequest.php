<?php

namespace App\Http\Requests\Admin\Api\Cities;

use App\Models\Admin;
use Illuminate\Validation\Rule;
use App\Http\Requests\ResponseShape;

/**
 * @property string $username
 * @property string $password
 */
class CityStoreRequest extends ResponseShape
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
                Rule::unique('cities', 'name->ar')->whereNull('deleted_at'),
            ],
            'name.en' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z0-9\s]+$/u',
                Rule::unique('cities', 'name->en')->whereNull('deleted_at'),
            ],
            'state_id' => [
                'required',
                'exists:states,id',
            ],

        ];
    }
}
