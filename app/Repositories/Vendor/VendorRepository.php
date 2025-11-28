<?php

namespace App\Repositories\Vendor;

use App\Models\Vendor;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

class VendorRepository extends BaseRepository
{
    /**
     * @param Vendor $model
     */
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
}
