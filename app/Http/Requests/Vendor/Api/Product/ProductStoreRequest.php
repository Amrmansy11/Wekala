<?php

namespace App\Http\Requests\Vendor\Api\Product;

use App\Http\Requests\ResponseShape;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property int $sub_sub_category_id
 * @property int $category_id
 */
class ProductStoreRequest extends ResponseShape
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'material_id' => 'required|string|exists:materials,id',
            'barcode' => 'required|string|unique:products,barcode',
            'wholesale_price' => 'required_if:type,b2b_b2c|numeric|min:0',
            'consumer_price' => 'required|numeric|min:0',
            'profit_percentage' => 'required_if:type,b2b_b2c|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:categories,id',
            'sub_sub_category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            //            'vendor_id' => 'required|exists:vendors,id',
            'type' => 'required|in:b2c,b2b_b2c',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'publish_type' => 'required|boolean',
            'publish_date' => 'nullable|date|required_if:publish_type,later',
            //            'template_id' => ['required_without:template_data', 'integer',
            //                Rule::exists('size_templates')->where('vendor_id', auth()->user()->vendor_id)
            //            ],
            //            'template_data' => 'required_without:template_id|array',
            'template_id' => [
                'nullable',
                'integer',
                Rule::exists('size_templates', 'id')->where('vendor_id', auth()->user()->vendor_id)
            ],
            'template_data' => 'nullable|array',
            'template_data.chest' => 'nullable|numeric',
            'template_data.chest_pattern' => 'nullable|numeric',
            'template_data.product_length' => 'nullable|numeric',
            'template_data.length_pattern' => 'nullable|numeric',
            'template_data.weight_from' => 'nullable|numeric',
            'template_data.weight_from_pattern' => 'nullable|numeric',
            'template_data.weight_to' => 'nullable|numeric',
            'template_data.weight_to_pattern' => 'nullable|numeric',
            'sizes' => 'nullable|array',
            'sizes.*.size' => 'nullable|string',
            'sizes.*.pieces_per_bag' => 'nullable|integer|min:1',
            'colors' => 'nullable|array',
            'colors.*.color' => 'nullable|string',
            'colors.*.bags' => 'nullable|integer|min:1',
            'colors.*.quantity_b2c' => 'nullable|integer|min:0',
            'colors.*.quantity_b2b' => 'nullable|integer|min:0',
            'colors.*.images' => 'nullable|array',
            'colors.*.images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'colors.*.image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // For backward compatibility
            'min_color' => 'nullable|integer|min:1',
            'stock_b2b' => 'nullable|integer|min:0',
            'stock_b2c' => 'nullable|integer|min:0',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }
            try {
                // Get category after basic validation passes
                $categoryId = $this->input('category_id');
                if (!$categoryId) {
                    return;
                }

                $category = Category::query()->find($categoryId);

                if (!$category) {
                    return;
                }

                if ($category->size_required) {
                    $hasTemplateId = $this->filled('template_id');
                    $hasTemplateData = $this->filled('template_data');

                    if (!$hasTemplateId && !$hasTemplateData) {
                        $validator->errors()->add('template_id', 'Either template_id or template_data is required.');
                        $validator->errors()->add('template_data', 'Either template_id or template_data is required.');
                    }
                    // Require sizes if this is a clothing product
                    if (empty($this->sizes) || !is_array($this->sizes)) {
                        $validator->errors()->add('sizes', 'The sizes field is required.');
                    } else {
                        foreach ($this->sizes as $index => $size) {
                            if (empty($size['size'])) {
                                $validator->errors()->add('sizes.' . $index . '.size', 'The size field is required.');
                            }
                            if (!isset($size['pieces_per_bag']) || $size['pieces_per_bag'] < 1) {
                                $validator->errors()->add('sizes.' . $index . '.pieces_per_bag', 'The pieces_per_bag field is required and must be at least 1.');
                            }
                        }
                    }

                    // Validate min_color
                    if (!isset($this->min_color) || $this->min_color < 1) {
                        $validator->errors()->add('min_color', 'The min color field is required and must be at least 1.');
                    }

                    // Validate colors if provided
                    if ($this->has('colors') && is_array($this->colors)) {
                        foreach ($this->colors as $index => $color) {
                            if (empty($color['color'])) {
                                $validator->errors()->add('colors.' . $index . '.color', 'The color field is required.');
                            }
                            if (!isset($color['bags']) || $color['bags'] < 1) {
                                $validator->errors()->add('colors.' . $index . '.bags', 'The bags field is required and must be at least 1.');
                            }
                            if (!isset($color['quantity_b2c']) || $color['quantity_b2c'] < 0) {
                                $validator->errors()->add('colors.' . $index . '.quantity_b2c', 'The quantity_b2c field is required and must be at least 0.');
                            }

                            $type = $this->input('type');
                            if (in_array($type, ['b2b', 'b2b_b2c']) && (!isset($color['quantity_b2b']) || $color['quantity_b2b'] < 0)) {
                                $validator->errors()->add('colors.' . $index . '.quantity_b2b', 'The quantity_b2b field is required for b2b or b2b_b2c type products.');
                            }

                            // Validate bags based on type
                            if (isset($color['bags']) && isset($color['quantity_b2c'])) {
                                if ($type === 'b2b_b2c') {
                                    $expectedBags = ($color['quantity_b2c'] ?? 0) + ($color['quantity_b2b'] ?? 0);
                                    if ($color['bags'] != $expectedBags) {
                                        $validator->errors()->add('colors.' . $index . '.bags', 'The bags must equal the sum of quantity_b2c and quantity_b2b (' . $expectedBags . ') for b2b_b2c type products.');
                                    }
                                } elseif ($type === 'b2c') {
                                    if ($color['bags'] != $color['quantity_b2c']) {
                                        $validator->errors()->add('colors.' . $index . '.bags', 'The bags must equal quantity_b2c (' . $color['quantity_b2c'] . ') for b2c type products.');
                                    }
                                }
                            }
                        }

                        // Validate min_color vs colors count
                        if ($this->has('min_color') && $this->has('colors')) {
                            $colorsCount = count($this->colors);
                            $minColor = $this->min_color;
                            if ($minColor > $colorsCount) {
                                $validator->errors()->add('min_color', 'The min color must be equal to or less than the number of colors (' . $colorsCount . ').');
                            }
                        }
                    }
                } else {
                    if ($this->input('type') == 'b2b') {
                        if (!isset($this->stock_b2b)) {
                            $validator->errors()->add('stock_b2b', 'The stock_b2b field is required.');
                        } elseif ($this->stock_b2b < 0) {
                            $validator->errors()->add('stock_b2b', 'The stock_b2b must be at least 0.');
                        }
                        //                    } elseif ($this->input('type') == 'b2b_b2c') {
                    } else {
                        if (!isset($this->stock_b2b)) {
                            $validator->errors()->add('stock_b2b', 'The stock_b2b field is required.');
                        } elseif ($this->stock_b2b < 0) {
                            $validator->errors()->add('stock_b2b', 'The stock_b2b must be at least 0.');
                        }
                        if (!isset($this->stock_b2c)) {
                            $validator->errors()->add('stock_b2c', 'The stock_b2c field is required.');
                        } elseif ($this->stock_b2c < 0) {
                            $validator->errors()->add('stock_b2c', 'The stock_b2c must be at least 0.');
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore errors in validation callback
            }
        });
    }

    private function checkWholeSalePrice()
    {

    }
}
