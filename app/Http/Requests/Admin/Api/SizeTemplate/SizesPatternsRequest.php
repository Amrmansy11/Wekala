<?php

namespace App\Http\Requests\Admin\Api\SizeTemplate;

use Illuminate\Validation\Rule;
use App\Http\Requests\ResponseShape;

/**
 * @property array $sizes
 */
class SizesPatternsRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sizes' => [
                'required',
                'array',
                'min:1',
            ],
            'sizes.*' => [
                'string',
                Rule::exists('sizes', 'name')->where('is_active', true)->whereNull('deleted_at'),
            ],

        ];
    }
}
