<?php

namespace App\Repositories\Vendor;

use App\Models\Voucher;
use App\Repositories\BaseRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VoucherRepository extends BaseRepository
{
    protected Model $model;

    public function __construct(Voucher $model)
    {
        $this->model = $model;
        parent::__construct($model);
    }

    /**
     * @throws Exception
     */
    public function store(array $data): Voucher
    {
        return DB::transaction(function () use ($data) {
            $voucher = new Voucher(array_merge($data, [
                'vendor_id' => $data['vendor_id'],
                'start_date' => Carbon::parse($data['start_date']),
                'end_date' => Carbon::parse($data['end_date']),
            ]));
            $voucher->creatable()->associate(auth()->user());
            $voucher->save();

            // Sync products only if for_all is false, otherwise empty the relationship
            if ($data['for_all'] === false || $data['for_all'] === '0') {
                if (!empty($data['products']) && is_array($data['products'])) {
                    $voucher->products()->sync($data['products']);
                } else {
                    $voucher->products()->sync([]); // Ensure no products if none provided and for_all is false
                }
            } else {
                $voucher->products()->sync([]); // Empty products if for_all is true
            }

            return $voucher->load('products');
        });
    }

    public function show($id): Voucher
    {
        $voucher = $this->model->query()->findOrFail($id);
        return $voucher->load('products');
    }

    /**
     * @param $id
     * @param $data
     * @return Voucher
     * @throws \Throwable
     */
    public function update($id, $data): Voucher
    {
        return DB::transaction(function () use ($id, $data) {
            /** @var Voucher $voucher */
            $voucher = $this->model->query()->findOrFail($id);
            $voucher->update(array_merge($data, [
                'start_date' => Carbon::parse($data['start_date']),
                'end_date' => Carbon::parse($data['end_date']),
            ]));

            // Sync products only if for_all is false, otherwise empty the relationship
            if ($data['for_all'] === false || $data['for_all'] === '0') {
                if (!empty($data['products']) && is_array($data['products'])) {
                    $voucher->products()->sync($data['products']);
                } else {
                    $voucher->products()->sync([]); // Ensure no products if none provided and for_all is false
                }
            } else {
                $voucher->products()->sync([]); // Empty products if for_all is true
            }

            return $voucher->load('products');
        });
    }

    /**
     * @throws Exception
     * @throws \Throwable
     */
    public function delete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $voucher = $this->model->query()->findOrFail($id);
            $voucher->products()->detach();
            return $voucher->delete();
        });
    }

    /**
     * Decrease number of uses when a voucher is applied
     */
    public function decreaseUse(Voucher $voucher, int $quantity = 1): bool
    {
        if ($voucher->number_of_use >= $quantity && now()->between($voucher->start_date, $voucher->end_date)) {
            $voucher->number_of_use -= $quantity;
            $voucher->save();
            return true;
        }
        return false;
    }

    /**
     * Check if voucher is applicable for a person
     */
    public function isApplicableForPerson(Voucher $voucher, int $userId): bool
    {
        $usageCount = $voucher->users()->where('user_id', $userId)->count();
        return $usageCount < $voucher->number_of_use_per_person;
    }
}
