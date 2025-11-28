<?php

namespace App\Http\Requests\Vendor\Api\Follow;


use App\Http\Requests\ResponseShape;

/**
 * @property int $vendor_id
 */
class FollowStoreRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => [
                'required',
                'integer',
                'exists:vendors,id',
            ],
        ];
    }
}
