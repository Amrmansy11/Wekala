<?php

namespace App\Http\Requests\Admin\Api\Product;

use App\Enums\ProductStatus;
use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $status
 */

class UpdateStatusProduct extends ResponseShape
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
       return [
            'status' => ['required', Rule::in(ProductStatus::toArray())],
        ];
    }
}
