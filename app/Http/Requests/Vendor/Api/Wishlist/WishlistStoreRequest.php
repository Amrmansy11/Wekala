<?php

namespace App\Http\Requests\Vendor\Api\Wishlist;

use Illuminate\Validation\Rule;
use App\Http\Requests\ResponseShape;

/**
 * @property int $product_id
 */
class WishlistStoreRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id'),
            ],
        ];
    }
}
