<?php

namespace App\Repositories\Vendor;

use App\Models\Wishlist;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

class WishlistRepository extends BaseRepository
{
    public function __construct(Wishlist $model)
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
            ->exists();

        if (!$exists) {
            return false;
        }

        return parent::delete($modelId);
    }
    public function toggleWishlist($user, int $productId): array
    {
        $existing = $user->wishlist()
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();

            return [
                'message' => 'Product removed from wishlist'
            ];
        }

        $user->wishlist()->create([
            'product_id' => $productId,
        ]);

        return [
            'message' => 'Product added to wishlist'
        ];
    }
}
