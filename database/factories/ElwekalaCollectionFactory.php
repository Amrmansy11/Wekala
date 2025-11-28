<?php

namespace Database\Factories;

use App\Models\ElwekalaCollection;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ElwekalaCollectionFactory extends Factory
{
    protected $model = ElwekalaCollection::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['feeds', 'best_sellers', 'new_arrivals', 'most_popular', 'flash_sale']),
            'product_id' => Product::query()
                ->whereHas('vendor', function ($q) {
                    $q->where('store_type', 'seller')
                        ->whereHas('media', function ($q2) {
                            $q2->where('collection_name', 'vendor_national_id');
                        });
                })
                ->inRandomOrder()->first()->id,
            'type_elwekala' => $this->faker->randomElement(['seller', 'consumer']),
        ];
    }
}
