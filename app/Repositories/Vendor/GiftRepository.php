<?php

namespace App\Repositories\Vendor;

use App\Models\Gift;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

class GiftRepository extends BaseRepository
{
    protected Model $model;

    public function __construct(Gift $model)
    {
        $this->model = $model;
        parent::__construct($model);
    }

    public function queryWithRelations()
    {
        return $this->model->newQuery()->with(['sourceProduct', 'giftProduct']);
    }

    public function store(array $data): Gift
    {
        /** @var Gift $gift */
        $gift = parent::store($data);
        return $gift->load(['sourceProduct', 'giftProduct']);
    }

    public function show($id): Gift
    {
        /** @var Gift $gift */
        $gift = $this->queryWithRelations()->findOrFail($id);
        return $gift;
    }

    public function update(array $data, int|string $modelId): Model
    {
        return parent::update($data, $modelId);
    }

    public function toggleArchive($id): Gift
    {
        $gift = $this->model->query()->findOrFail($id);
        
        if ($gift->isArchived()) {
            $gift->update(['archived_at' => null]);
        } else {
            $gift->update(['archived_at' => now()]);
        }
        
        return $gift->load(['sourceProduct', 'giftProduct']);
    }
}


