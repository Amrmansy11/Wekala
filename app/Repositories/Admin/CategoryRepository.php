<?php

namespace App\Repositories\Admin;

use App\Models\Category;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

class CategoryRepository extends BaseRepository
{
    public function __construct(Category $model)
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

    //delete
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

    public function getByLevel(?string $level = 'main', int $perPage = 15, ?string $search = null, ?int $categoryId = null)
    {
        $query = $this->query()->with(['children.children'])
            ->when($categoryId, fn($q) => $q->where('parent_id', $categoryId))
            ->withCount([
                'vendors as seller_count' => function ($query) {
                    $query->where('store_type', 'seller');
                },
                'vendors as retail_count' => function ($query) {
                    $query->where('store_type', 'retailer');
                }
            ]);

        match ($level) {
            'main' => $query->whereNull('parent_id'),
            'sub' => $query->whereHas('parent', fn($q) => $q->whereNull('parent_id')),
            'sub-sub' => $query->whereHas('parent', fn($q) => $q->whereNotNull('parent_id')),
            default => $query->whereNull('parent_id'),
        };

        $query->when(
            $search,
            fn($q) => $q->whereAny(
                ['name->ar', 'name->en'],
                'like',
                '%' . $search . '%'
            )
        );

        return $query->paginate($perPage);
    }

    public function findByLevel(?string $level = 'main', int $id)
    {
        $query = $this->with(['children.children']);

        match ($level) {
            'main' => $query->whereNull('parent_id'),
            'sub' => $query->whereHas('parent', fn($q) => $q->whereNull('parent_id')),
            'sub-sub' => $query->whereHas('parent', fn($q) => $q->whereNotNull('parent_id')),
            default => $query->whereNull('parent_id'),
        };

        return $query->find($id);
    }


    /**
     * @param int|string $modelId
     * @return Model|null
     */
    public function toggleIsActive(int|string $modelId): ?Model
    {
        $category = parent::find($modelId);
        if (! $category) {
            return null;
        }

        return parent::update([
            'is_active' => !$category->is_active,
        ], $modelId);
    }
}
