<?php

namespace App\Http\Requests\Admin\Api\policies;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $name
 * @property string $title
 * @property string $desc
 * @property string $type
 */
class PolicyUpdateRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        $policyId = $this->route('policy');
        return [
            'name.ar' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\p{Arabic}\p{N}\s]+$/u',
                Rule::unique('policies', 'name->ar')->ignore($policyId)->whereNull('deleted_at'),
            ],
            'name.en' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z0-9\s]+$/u',
                Rule::unique('policies', 'name->en')->ignore($policyId)->whereNull('deleted_at'),
            ],
            'title.ar' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\p{Arabic}\p{N}\s]+$/u',
            ],
            'title.en' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z0-9\s]+$/u',
            ],
            'desc.ar' => [
                'required',
                'string',
                'min:2',
                'regex:/^[\p{Arabic}\p{N}\s]+$/u',
            ],
            'desc.en' => [
                'required',
                'string',
                'min:2',
                'regex:/^[a-zA-Z0-9\s]+$/u',
            ],
            'type' => [
                'required',
                'string',
                'max:255',
                Rule::in(['within_elwekala', 'without_elwekala']),
            ],

        ];
    }
}
