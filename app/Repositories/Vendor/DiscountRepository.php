<?php

namespace App\Repositories\Vendor;

use App\Models\Discount;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

class DiscountRepository extends BaseRepository
{
    protected Model $model;

    public function __construct(Discount $model)
    {
        $this->model = $model;
        parent::__construct($model);
    }

    public function store(array $data): Discount
    {
        /** @var Discount $discount */
        $discount = parent::store($data);

        if (!empty($data['product_ids']) && is_array($data['product_ids'])) {
            $discount->products()->sync($data['product_ids']);
        }

        return $discount->load('products');
    }

    public function show($id): Discount
    {
        /** @var Discount $discount */
        $discount = $this->model->query()
            ->with('products')
            ->findOrFail($id);
        return $discount;
    }

    public function update(array $data, int|string $modelId): Model
    {
        /** @var Discount $discount */
        $discount = parent::update($data, $modelId);

        if (isset($data['product_ids']) && is_array($data['product_ids'])) {
            $discount->products()->sync($data['product_ids']);
        }

        return $discount->load('products');
    }

    public function toggleArchive($id): Discount
    {
        $discount = $this->model->query()->findOrFail($id);

        if ($discount->isArchived()) {
            $discount->update(['archived_at' => null]);
        } else {
            $discount->update(['archived_at' => now()]);
        }

        return $discount->load('products');
    }
}




