<?php

namespace App\Http\Resources\Consumer\Voucher;

use Illuminate\Http\Resources\Json\JsonResource;

class VoucherListingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'percentage' => $this->percentage,
            'amount' => $this->amount,
            'number_of_use' => $this->number_of_use,
            'number_of_use_per_person' => $this->number_of_use_per_person,
            'for_all' => $this->for_all,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->number_of_use > 0 && now()->between($this->start_date, $this->end_date),
        ];
    }
}
