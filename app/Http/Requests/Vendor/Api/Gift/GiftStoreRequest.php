<?php

namespace App\Http\Requests\Vendor\Api\Gift;

use App\Http\Requests\ResponseShape;

class GiftStoreRequest extends ResponseShape
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $giftId = $this->route('gift');
        $vendorId = auth()->user()->vendor_id ?? null;

        return [
            'source_product_id' => [
                'required', 
                'integer', 
                'exists:products,id', 
                'different:gift_product_id',
                function ($attribute, $value, $fail) use ($giftId, $vendorId) {
                    $giftProductId = $this->input('gift_product_id');
                    if ($giftProductId && $vendorId) {
                        $query = \App\Models\Gift::where('vendor_id', $vendorId)
                            ->where('source_product_id', $value)
                            ->where('gift_product_id', $giftProductId);
                        
                        if ($giftId) {
                            $query->where('id', '!=', $giftId);
                        }
                        
                        if ($query->exists()) {
                            $fail('The source product cannot repeat with the same gift product.');
                        }
                    }
                }
            ],
            'gift_product_id' => [
                'required', 
                'integer', 
                'exists:products,id', 
                'different:source_product_id',
                function ($attribute, $value, $fail) use ($giftId, $vendorId) {
                    $sourceProductId = $this->input('source_product_id');
                    if ($sourceProductId && $vendorId) {
                        $query = \App\Models\Gift::where('vendor_id', $vendorId)
                            ->where('source_product_id', $sourceProductId)
                            ->where('gift_product_id', $value);
                        
                        if ($giftId) {
                            $query->where('id', '!=', $giftId);
                        }
                        
                        if ($query->exists()) {
                            $fail('The source product cannot repeat with the same gift product.');
                        }
                    }
                }
            ],
//            'starts_at' => ['nullable', 'date'],
//            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
//            'is_active' => ['sometimes', 'boolean'],
            'vendor_id' => ['sometimes', 'exists:vendors,id'],
        ];
    }
}


