<?php

namespace Database\Factories;

use App\Models\Discount;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true),

            'percentage' => $this->faker->randomFloat(2, 5, 50),

            'vendor_id' => 92,
            'archived_at' => null,
        ];
    }


    public function archived()
    {
        return $this->state(fn() => [
            'archived_at' => now(),
        ]);
    }


    public function withProducts(int $count = 3)
    {
        return $this->afterCreating(function (Discount $discount) use ($count) {

            $products = Product::query()
                ->where('vendor_id', $discount->vendor_id)
                ->inRandomOrder()
                ->B2BB2C()
                ->take($count)
                ->get();

            if ($products->isEmpty()) {
                $products = Product::factory()->count($count)->create([
                    'vendor_id' => $discount->vendor_id,
                ]);
            }

            $discount->products()->attach($products->pluck('id')->toArray());
        });
    }
}
