<?php

namespace App\Repositories\Vendor;

use App\Models\Point;
use App\Repositories\BaseRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PointRepository extends BaseRepository
{
    protected Model $model;

    public function __construct(Point $model)
    {
        $this->model = $model;
        parent::__construct($model);
    }

    /**
     * @throws Exception
     */
    public function store(array $data): Point
    {
        return DB::transaction(function () use ($data) {
            $point = new Point([
                'type' => $data['type'],
                'points' => $data['points'] ?? 0,
                'vendor_id' => $data['vendor_id'],
            ]);
            $point->creatable()->associate(auth()->user());
            $point->save();

            if (!empty($data['products']) && is_array($data['products'])) {
                $point->products()->sync($data['products']);
            }

            return $point->load('products');
        });
    }

    public function show($id): Point
    {
        $point = $this->model->query()->findOrFail($id);
        return $point->load('products');
    }

    public function update($id, $data): Point
    {
        return DB::transaction(function () use ($id, $data) {
            /** @var Point $point */
            $point = $this->model->query()->findOrFail($id);
            $point->update([
                'type' => $data['type'] ?? $point->type,
                'points' => $data['points'] ?? $point->points,
                'vendor_id' => $data['vendor_id'] ?? $point->vendor_id,
            ]);

            if (array_key_exists('products', $data)) {
                $point->products()->sync(is_array($data['products']) ? $data['products'] : []);
            }

            return $point->load('products');
        });
    }

    public function toggleArchive($id): Point
    {
        $point = $this->model->query()->findOrFail($id);
        
        if ($point->isArchived()) {
            $point->update(['archived_at' => null]);
        } else {
            $point->update(['archived_at' => now()]);
        }
        
        return $point->load('products');
    }

    /**
     * @throws Exception
     */
    public function delete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $point = $this->model->query()->findOrFail($id);
            $point->products()->detach();
            return $point->delete();
        });
    }
}


