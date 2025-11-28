<?php

namespace App\Repositories\Admin;

use App\Models\Policy;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

class PolicyRepository extends BaseRepository
{
    public function __construct(Policy $model)
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
}
