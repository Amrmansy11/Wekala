<?php

namespace App\Http\Requests\Vendor\Api\Discount;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

class DiscountStoreRequest extends ResponseShape
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0|max:100',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'vendor_id' => 'sometimes|exists:vendors,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'percentage.required' => 'The percentage field is required.',
            'percentage.min' => 'The percentage must be at least 0.',
            'percentage.max' => 'The percentage must not exceed 100.',
            'product_ids.required' => 'At least one product is required.',
            'product_ids.min' => 'At least one product is required.',
            'product_ids.*.exists' => 'One or more selected products do not exist.',
        ];
    }
}




