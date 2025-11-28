<?php

namespace App\Repositories\Admin;

use App\Models\Vendor;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class VendorRepository extends BaseRepository
{
    public function __construct(Vendor $model)
    {
        parent::__construct($model);
    }


    /**
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        return parent::store($data);
    }

    /**
     * @param array $data
     * @param int|string $modelId
     * @return Model
     */
    public function update(array $data, int|string $modelId): Model
    {
        return parent::update($data, $modelId);
    }

    /**
     * @param int|string $modelId
     * @return bool
     * @throws Exception
     */
    public function delete(int|string $modelId): bool
    {
        if (!$this->find($modelId)) {
            return false;
        }
        return parent::delete($modelId);
    }

    /**
     * @param int|string $modelId
     * @return Model|null
     */
    public function toggleIsActive(int|string $modelId): ?Model
    {
        $Brand = parent::find($modelId);
        if (!$Brand) {
            return null;
        }
        return parent::update([
            'is_active' => !$Brand->is_active,
        ], $modelId);
    }
    public function getFilteredVendors(?string $storeType = null, ?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()

            ->when($storeType, fn($q) => $q->where('store_type', $storeType))
            ->when($status, fn($q) => $q->where('status', $status))
            ->paginate($perPage);
    }
}
