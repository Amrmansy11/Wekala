<?php

namespace App\Repositories\Vendor;

use App\Models\Policy;
use App\Repositories\BaseRepository;

class PolicyRepository extends BaseRepository
{
    public function __construct(Policy $model)
    {
        parent::__construct($model);
    }
}
