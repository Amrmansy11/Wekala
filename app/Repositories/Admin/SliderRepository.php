<?php

namespace App\Repositories\Admin;

use App\Models\Slider;
use App\Repositories\BaseRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;

class SliderRepository extends BaseRepository
{
    public function __construct(Slider $model)
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
        $exists = $this->query()
            ->where('id', $modelId)
            ->exists();

        if (!$exists) {
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
        $slider = $this->query()
            ->where('id', $modelId)
            ->first();

        if (!$slider) {
            return null;
        }

        return parent::update([
            'is_active' => !$slider->is_active,
        ], $modelId);
    }
}
