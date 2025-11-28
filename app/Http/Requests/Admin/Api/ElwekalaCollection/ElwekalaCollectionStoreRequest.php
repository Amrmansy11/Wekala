<?php

namespace App\Http\Requests\Admin\Api\ElwekalaCollection;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

/**
 * @property string $type
 * @property int $product_id
 */
class ElwekalaCollectionStoreRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                'max:255',
                Rule::in(['feeds', 'best_sellers', 'new_arrivals', 'most_popular', 'flash_sale']),
            ],
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
                        $query->where('type', $this->type);
                    }),
            ],
            'type_elwekala' => 'required|in:consumer,seller',
        ];
    }
}
