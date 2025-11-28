<?php

namespace App\Http\Requests\Admin\Api\Sliders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class SliderStoreRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'images' => 'required|array',
            'images.*' => 'image|max:2048', // Max 2MB per image
            'product_ids' => 'sometimes|array',
            'product_ids.*' => 'exists:products,id',
            'is_active' => 'boolean',
            'type' => 'required|in:consumer,seller',
        ];
    }
}
