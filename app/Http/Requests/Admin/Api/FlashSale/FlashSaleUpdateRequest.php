<?php

namespace App\Http\Requests\Admin\Api\FlashSale;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $product_id
 */
class FlashSaleUpdateRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('flash_sale');

        return [
            'product_id' => [
                'required',
                'integer',
                Rule::unique('elwekala_collections', 'product_id')->ignore($id),
                Rule::exists('products', 'id')->where(function ($query) {
                    $query->whereIn('vendor_id', function ($sub) {
                        $sub->select('id')
                            ->from('vendors')
                            ->where('store_type', 'seller');
                    });
                }),
            ],
        ];
    }
}
