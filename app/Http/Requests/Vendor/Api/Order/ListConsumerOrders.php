<?php

namespace App\Http\Requests\Vendor\Api\Order;

use App\Models\VendorUser;
use App\Http\Requests\ResponseShape;
use Illuminate\Support\Facades\Auth;

class ListConsumerOrders extends ResponseShape
{
    public function authorize(): bool
    {
        /** @var VendorUser $vendorUser */
        $vendorUser = Auth::guard('vendor-api')->user();

        return !is_null($vendorUser);
    }

    public function rules(): array
    {
        return [
            'per_page'       => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort_by'        => ['nullable', 'string', 'in:id,name,created_at'],
            'sort_type'      => ['nullable', 'string', 'in:asc,desc'],
            'status'         => ['nullable', 'string', 'in:pending,confirmed,shipped,completed,cancelled'],
            'promotion_type' => ['nullable', 'string', 'in:discount,voucher,offer'],
        ];
    }
}
