<?php

namespace App\Repositories\Admin;

use App\Repositories\BaseRepository;
use App\Models\Brand;
use Exception;
use Illuminate\Database\Eloquent\Model;

class BrandRepository extends BaseRepository
{
    public function __construct(Brand $model)
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
}
