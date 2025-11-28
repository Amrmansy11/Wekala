<?php

namespace App\Http\Requests\Vendor\Api\VendorBranch;

use App\Http\Requests\ResponseShape;
/**
 * @property string $national_id_file
 * @property string $tax_card_file
 */
class VendorBranchRegisterSecondStepRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'national_id_file' => ['required', 'file', 'mimes:jpeg,jpg,png', 'max:2048'],
            'tax_card_file' => ['required', 'file', 'mimes:jpeg,jpg,png', 'max:2048'],
        ];
    }
}
