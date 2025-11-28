<?php
namespace App\Http\Requests\Vendor\Api\Offer;

use App\Http\Requests\ResponseShape;

class OfferStoreRequest extends ResponseShape
{
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'start' => 'required|date|after_or_equal:' . now()->toDateTimeString(),
            'end' => 'required|date|after:start',
            'type' => 'required|in:quantity,purchase,custom',
            'discount' => 'required|numeric|min:0',
            'quantity' => 'required_if:type,quantity|nullable|integer|min:1',
            'amount' => 'required_if:type,purchase|nullable|numeric|min:0.01',
            'buy' => 'required_if:type,custom|nullable|integer|min:1',
            'get' => 'required_if:type,custom|nullable|integer|min:1',
            'products' => 'required|array',
            'products.*' => 'exists:products,id',
            'logo' => 'nullable|image|max:2048', // Max 2MB
            'cover' => 'nullable|image|max:2048', // Max 2MB
            'vendor_id' => 'sometimes|exists:vendors,id',
        ];
    }

    public function messages(): array
    {
        return [
            'start.after_or_equal' => 'The start date must be a date after or equal to today.',
            'end.after' => 'The end date must be a date after the start date.',
            'discount.required' => 'Discount is required for all offer types.',
            'quantity.required_if' => 'Quantity is required when type is quantity.',
            'amount.required_if' => 'Amount is required when type is purchase.',
            'buy.required_if' => 'Buy quantity is required for custom offer type.',
            'get.required_if' => 'Get quantity is required for custom offer type.',
            'logo.image' => 'The logo must be an image file.',
            'cover.image' => 'The cover must be an image file.',
            'logo.max' => 'The logo may not be greater than 2MB.',
            'cover.max' => 'The cover may not be greater than 2MB.',
        ];
    }
}
