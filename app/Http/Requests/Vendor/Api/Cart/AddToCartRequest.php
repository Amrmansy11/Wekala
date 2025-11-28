<?php

namespace App\Http\Requests\Vendor\Api\Cart;

use App\Http\Requests\ResponseShape;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VendorUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property integer $product_id
 * @property array $product_variant_id
 */
class AddToCartRequest extends ResponseShape
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'product_variant_id' => ['required_without:quantity', 'array'],
            'product_variant_id.*.id' => [
                'required_with:product_variant_id',
                'integer',
                Rule::exists('product_variants', 'id')
                    ->where('product_id', $this->input('product_id')),
            ],
            'product_variant_id.*.quantity' => ['required_with:product_variant_id', 'integer', 'min:1'],
            'quantity' => ['required_without:product_variant_id', 'integer', 'min:1'],
        ];
    }

    /**
     * @param $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var Product $product */
            $product = Product::query()->find($this->input('product_id'));

            if (!$product->vendor || $product->vendor->store_type !== 'seller') {
                $validator->errors()->add('product_variant_id', __('validation.custom.product.not_seller'));
            }

            /** @var VendorUser $vendorUser */
            $vendorUser = Auth::guard('vendor-api')->user();
            $buyerVendor = $vendorUser->vendor;

            if (!$buyerVendor || !in_array($buyerVendor->store_type, ['seller', 'retailer'])) {
                $validator->errors()->add('product_variant_id', __('validation.custom.buyer.invalid_type'));
            }

            if ($product->variants()->count()) {
                $variantIds = collect($this->input('product_variant_id'))
                    ->pluck('id')
                    ->unique();

                if ($product->min_color > $variantIds->count()) {
                    $validator->errors()->add('product_variant_id', __('validation.custom.product.min_color', [
                        'min_color' => $product->min_color
                    ]));
                }
                if ($variantIds->count()) {
                    foreach ($this->input('product_variant_id') as $variant) {
                        /** @var ProductVariant $productVariant */
                        $productVariant = ProductVariant::query()->where('product_id', $product->id)->find($variant['id']);
                        if (!$productVariant) {
                            $validator->errors()->add('product_variant_id', __('validation.custom.product.not_found'));
                            continue;
                        }
                        $availableQuantity = $productVariant->quantity_b2b ?? 0;
                        if ($availableQuantity < $variant['quantity']) {
                            $validator->errors()->add('product_variant_id', __('validation.custom.product.quantity', [
                                'color' => $productVariant->color,
                                'stock' => $availableQuantity
                            ]));
                        }
                    }
                } else {
                    $validator->errors()->add('product_variant_id', __('validation.required', ['attribute' => 'product_variant_id']));
                }
            } else {
                if ($product->stock_b2b < $this->input('quantity')) {
                    $validator->errors()->add('quantity', __('validation.custom.product.stock', ['stock' => $product->stock_b2b]));
                }
                if ($this->input('product_variant_id')) {
                    $validator->errors()->add('product_variant_id', __('validation.custom.product.additional_variant'));
                }
            }
        });
    }
}
