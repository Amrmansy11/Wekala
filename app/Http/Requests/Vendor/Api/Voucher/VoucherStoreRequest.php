<?php
namespace App\Http\Requests\Vendor\Api\Voucher;

use App\Http\Requests\ResponseShape;
use Illuminate\Validation\Rule;

class VoucherStoreRequest extends ResponseShape
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            // Unique code validation except for the current voucher being updated
            'code' => ['required','string',Rule::unique('vouchers', 'code')->ignore($this->route('id')),'max:255'],
            'percentage' => 'nullable|numeric|min:0|max:100',
            'amount' => 'nullable|numeric|min:0',
            'number_of_use' => 'required|integer|min:1',
            'number_of_use_per_person' => 'required|integer|min:1',
            'for_all' => 'required|boolean',
            'start_date' => 'required|date|after_or_equal:' . now()->toDateTimeString(),
            'end_date' => 'required|date|after:start_date',
            'products' => 'required_if:for_all,0|false|array',
            'products.*' => 'exists:products,id',
            'vendor_id' => 'sometimes|exists:vendors,id',
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.after_or_equal' => 'The start date must be a date after or equal to today.',
            'end_date.after' => 'The end date must be a date after the start date.',
            'products.required_if' => 'Products are required when the voucher is not for all.',
        ];
    }
}
