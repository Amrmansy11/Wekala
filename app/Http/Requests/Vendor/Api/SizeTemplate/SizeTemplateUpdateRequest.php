<?php

namespace App\Http\Requests\Vendor\Api\SizeTemplate;

use App\Helpers\AppHelper;
use Illuminate\Validation\Rule;
use App\Http\Requests\ResponseShape;

/**
 * @property int $vendor_id
 * @property string $template_name
 * @property numeric $chest
 * @property numeric $chest_pattern
 * @property numeric $product_length
 * @property numeric $length_pattern
 * @property numeric $weight_from
 * @property numeric $weight_from_pattern
 * @property numeric $weight_to
 * @property numeric $weight_to_pattern
 */
class SizeTemplateUpdateRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
            'vendor_id' => AppHelper::getVendorId(),
        ]);
    }
    public function rules(): array
    {
        return [
            'vendor_id' => [
                'required',
                'integer',
                Rule::exists('vendors', 'id'),
            ],
            'template_name' => [
                'required',
                'string',
                'max:255',
            ],
            'chest' => [
                'required',
                'numeric',
            ],
            'chest_pattern' => [
                'required',
                'numeric',
            ],
            'product_length' => [
                'required',
                'numeric',
            ],
            'length_pattern' => [
                'required',
                'numeric',
            ],
            'weight_from' => [
                'required',
                'numeric',
            ],
            'weight_from_pattern' => [
                'required',
                'numeric',
            ],
            'weight_to' => [
                'required',
                'numeric',
            ],
            'weight_to_pattern' => [
                'required',
                'numeric',
            ],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
            ],



        ];
    }
}
