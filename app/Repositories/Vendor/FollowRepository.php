<?php

namespace App\Repositories\Vendor;

use App\Models\VendorFollow;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

class FollowRepository extends BaseRepository
{
    public function __construct(VendorFollow $model)
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

}
