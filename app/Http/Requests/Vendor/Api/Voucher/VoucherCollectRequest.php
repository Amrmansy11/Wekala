<?php

namespace App\Http\Requests\Vendor\Api\Voucher;

use Illuminate\Validation\Rule;
use App\Http\Requests\ResponseShape;

/**
 * @property int $voucher_id
 */
class VoucherCollectRequest extends ResponseShape
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'voucher_id' => ['required', 'integer', Rule::exists('vouchers', 'id')],
        ];
    }
}
