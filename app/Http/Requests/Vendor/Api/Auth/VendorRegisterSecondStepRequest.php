<?php

namespace App\Http\Requests\Vendor\Api\Auth;

use App\Http\Requests\ResponseShape;
use App\Models\VendorUser;

/**
 * @property string $national_id_file
 * @property string $tax_card_file
 */
class VendorRegisterSecondStepRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        /** @var VendorUser $vendorUser */
        $vendorUser = auth()->user();
        $vendor = $vendorUser->vendor;
        return [
            'national_id_file' => ['required', 'file', 'mimes:jpeg,jpg,png', 'max:2048'],
            'tax_card_file' => [$vendor->store_type === 'seller' ? 'required' : 'nullable', 'required', 'file', 'mimes:jpeg,jpg,png', 'max:2048',],
        ];
    }
}
