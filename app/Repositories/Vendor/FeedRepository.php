<?php

namespace App\Repositories\Vendor;

use App\Models\Feed;
use App\Helpers\AppHelper;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

    class FeedRepository extends BaseRepository
{
    public function __construct(Feed $model)
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

    public function delete(int|string $modelId): bool
    {
        $exists = $this->query()
            ->where('id', $modelId)
            ->where('vendor_id',  AppHelper::getVendorId())
            ->exists();

        if (!$exists) {
            return false;
        }

        return parent::delete($modelId);
    }
}
