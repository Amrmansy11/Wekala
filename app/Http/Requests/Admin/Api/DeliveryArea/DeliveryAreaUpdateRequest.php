<?php

namespace App\Http\Requests\Admin\Api\DeliveryArea;

use App\Helpers\AppHelper;
use Illuminate\Validation\Rule;
use App\Http\Requests\ResponseShape;

/**
 * @property int $vendor_id
 * @property int $state_id
 * @property int $city_id
 * @property string $district
 * @property float $price
 */
class DeliveryAreaUpdateRequest extends ResponseShape
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
            'state_id' => [
                'required',
                'integer',
                Rule::exists('states', 'id'),
            ],
            'city_id' => [
                'required',
                'integer',
                Rule::exists('cities', 'id')->where(function ($query) {
                    $query->where('state_id', $this->state_id);
                }),
            ],
            'district' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ]

        ];
    }
}
