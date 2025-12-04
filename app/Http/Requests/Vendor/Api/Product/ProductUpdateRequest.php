<?php

namespace App\Http\Requests\Vendor\Api\Product;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int $sub_sub_category_id
 * @property int $category_id
 */

class ProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product');

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'material_id' => 'required|string|exists:materials,id',
            'barcode' => 'required|string|unique:products,barcode,' . $productId,
            'wholesale_price' => 'required_if:type,b2b_b2c|numeric|min:0',
            'consumer_price' => 'required|numeric|min:0',
            'profit_percentage' => 'required_if:type,b2b_b2c|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:categories,id',
            'sub_sub_category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'vendor_id' => 'required|exists:vendors,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'publish_type' => ['required', $this->boolean()],
            'elwekala_policy' => ['required', $this->boolean()],
            'publish_date' => 'nullable|date|required_if:publish_type,later',
        ];

        $subSubCategory = Category::query()->find($this->sub_sub_category_id);
        $isTshirtOrPants = $subSubCategory && in_array($subSubCategory->size, ['tshirt', 'pants']);

        if ($isTshirtOrPants) {
            $rules['measurements'] = 'required|array|min:1';
            $rules['measurements.*.size'] = 'required|string';
            $rules['measurements.*.waist'] = 'required_if:sub_sub_category.size,pants|nullable|numeric|min:0';
            $rules['measurements.*.length'] = 'required|numeric|min:0';
            $rules['measurements.*.weight_range'] = 'required|string';
            $rules['measurements.*.chest'] = 'required_if:sub_sub_category.size,tshirt|nullable|numeric|min:0';
        }

        $category = Category::query()->find($this->category_id);
        if ($category && $category->size_required) {
            $rules['sizes'] = 'required|array|min:1';
            $rules['sizes.*.size'] = 'required|string';
            $rules['sizes.*.pieces_per_bag'] = 'required|integer|min:1';
            $rules['colors'] = 'nullable|array';
            $rules['colors.*.color'] = 'required_with:colors|string';
            $rules['colors.*.bags'] = 'required_with:colors|integer|min:1';
            $rules['colors.*.image'] = 'nullable|image|mimes:jpeg,png,jpg|max:2048';
            $rules['min_color'] = 'required|integer|min:1';
        } else {
            $rules['stock'] = 'required|integer|min:0';
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $category = Category::query()->find($this->category_id);

            if ($category && $category->size_required && $this->has('min_color') && $this->has('colors')) {
                $colorsCount = is_array($this->colors) ? count($this->colors) : 0;
                $minColor = $this->min_color;

                if ($minColor > $colorsCount) {
                    $validator->errors()->add('min_color', 'The min color must be equal to or less than the number of colors added to the product (' . $colorsCount . ').');
                }
            }
        });
    }
}

