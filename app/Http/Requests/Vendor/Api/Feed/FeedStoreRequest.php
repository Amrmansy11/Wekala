<?php

namespace App\Http\Requests\Vendor\Api\Feed;

use App\Helpers\AppHelper;
use Illuminate\Validation\Rule;
use App\Http\Requests\ResponseShape;

/**
 * @property int $vendor_id
 * @property array $product_ids
 * @property string $media
 */
class FeedStoreRequest extends ResponseShape
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
                'exists:vendors,id',
            ],
            'media' => [
                'required',
                'file',
                'mimes:jpeg,png,jpg,mp4,avi,mov',
                'max:10240',
            ],
            'products_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'products_ids.*' => [
                'integer',
                Rule::exists('products', 'id')->where(function ($query) {
                    $query->where('vendor_id', $this->vendor_id);
                }),
            ],

        ];
    }
}
