<?php

namespace App\Http\Requests\Admin\Api\FlashSale;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property int $product_id
 * @property string $type_elwekala
 */
class FlashSaleStoreRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'   => ['required', 'array', 'min:1'],
            'product_id.*' => [
                'integer',
                Rule::exists('products', 'id')
                    ->where(function ($query) {
                        $query->whereIn('vendor_id', function ($sub) {
                            $sub->select('id')
                                ->from('vendors')
                                ->where('store_type', 'seller');
                        });
                    }),
                Rule::unique('elwekala_collections', 'product_id')
                    ->where(function ($query) {
                        $query->where('type', 'flash_sale')->where('type_elwekala', $this->type_elwekala);
                    }),

            ],
            'type_elwekala' => 'required|in:consumer,seller',
        ];
    }
}
