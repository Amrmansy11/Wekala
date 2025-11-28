<?php

namespace App\Http\Requests\Vendor\Api\Point;

use Illuminate\Foundation\Http\FormRequest;

class PointStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:earned,redeemed'],
            'points' => ['required', 'integer', 'min:0'],
            'products' => ['nullable', 'array'],
            'products.*' => ['integer', 'exists:products,id'],
            'vendor_id' => 'sometimes|exists:vendors,id',
        ];
    }
}


