<?php

namespace App\Repositories\Vendor;

use App\Helpers\AppHelper;
use App\Models\SizeTemplate;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use App\Models\Size;

class SizeTemplateRepository extends BaseRepository
{
    public function __construct(SizeTemplate $model)
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

    public function patterns(array $sizes, $template) : array
    {
        $orderedSizes = Size::whereIn('name', $sizes)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $baseValues = [
            'chest' => $template->chest,
            'product_length' => $template->product_length,
            'weight_from' => $template->weight_from,
            'weight_to' => $template->weight_to,
        ];

        $patterns = [
            'chest' => (float) $template->chest_pattern,
            'length' => (float) $template->length_pattern,
            'weight_from' => (float) $template->weight_from_pattern,
            'weight_to' => (float) $template->weight_to_pattern,
        ];

        $results = [];
        $index = 0;

        foreach ($orderedSizes as $size) {
            $results[] = [
                'size' => $size->name,
                'chest' => round($baseValues['chest'] + ($patterns['chest'] * $index), 2),
                'length' => round($baseValues['product_length'] + ($patterns['length'] * $index), 2),
                'weight_from' => round($baseValues['weight_from'] + ($patterns['weight_from'] * $index), 2),
                'weight_to' => round($baseValues['weight_to'] + ($patterns['weight_to'] * $index), 2),
            ];

            $index++;
        }

        return $results;
    }
}
