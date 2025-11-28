<?php

namespace App\Http\Requests\Admin\Api\Vendors;


use Illuminate\Validation\Rule;
use App\Http\Requests\ResponseShape;

/**
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $image
 */
class OwnerInfoUpdateRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('vendorUser');
        return
            [
                'name' => ['required', 'string'],
                'email' => ['required', 'email', Rule::unique('vendor_users')->ignore($id)],
                'phone' => ['required', Rule::unique('vendor_users')->ignore($id)],
                'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            ];
    }
}
