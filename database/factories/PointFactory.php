<?php

namespace Database\Factories;

use App\Models\Point;
use App\Models\Vendor;
use App\Models\Product;
use App\Enums\PointType;
use App\Models\VendorUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class PointFactory extends Factory
{
    protected $model = Point::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(PointType::cases()),
            'points' => $this->faker->numberBetween(5, 200),
            'vendor_id' => 92,
            'archived_at' => null,
            'creatable_type' => 'App\Models\VendorUser',
            'creatable_id' => 149, // VendorUser::inRandomOrder()->value('id'),
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
        return $this->afterCreating(function (Point $point) use ($count) {
            $products = Product::query()
                ->where('vendor_id', 92)
                ->inRandomOrder()
                ->B2BB2C()
                ->take($count)
                ->get();

            if ($products->isEmpty()) {
                $products = Product::factory()
                    ->count($count)
                    ->create([
                        'vendor_id' => $point->vendor_id
                    ]);
            }

            $point->products()->attach($products->pluck('id')->toArray());
        });
    }
}
