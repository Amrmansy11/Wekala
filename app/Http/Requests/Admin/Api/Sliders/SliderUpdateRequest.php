<?php
namespace App\Http\Requests\Admin\Api\Sliders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class SliderUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'images' => 'sometimes|array',
            'images.*' => 'image|max:2048', // Max 2MB per image
            'product_ids' => 'sometimes|array',
            'product_ids.*' => 'exists:products,id',
            'is_active' => 'boolean',
            'media_remove_ids' => 'array',
            'media_remove_ids.*' => 'exists:media,id',
            'type_elwekala' => 'required|in:consumer,seller',
        ];
    }
}
