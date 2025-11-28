<?php
namespace App\Http\Requests\Vendor\Api\Store;

use App\Http\Requests\ResponseShape;

/**
 * @property string $logo
 * @property string $cover
 */
class StoreUpdateImageRequest extends ResponseShape
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }


}
