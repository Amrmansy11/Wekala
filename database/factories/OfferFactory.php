<?php

namespace Database\Factories;

use App\Models\Offer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfferFactory extends Factory
{
    protected $model = Offer::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['quantity', 'purchase', 'custom']);

        return [
            'name' => $this->faker->words(3, true),
            'desc' => $this->faker->sentence(),
            'type' => $type,
            'discount' => in_array($type, ['quantity', 'purchase'])
                ? $this->faker->randomFloat(2, 5, 50)
                : null,
            'buy' => $type === 'custom' ? $this->faker->numberBetween(1, 5) : null,
            'get' => $type === 'custom' ? $this->faker->numberBetween(1, 3) : null,
            'start' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'end' => $this->faker->dateTimeBetween('now', '+2 months'),
            'creatable_type' => 'App\Models\Vendor',
            'creatable_id' => 92,

        ];
    }
    public function configure(): OfferFactory
    {
        return $this->afterCreating(function (Offer $offer) {
            $offer->addMedia(public_path('brands/Womens/HM.png'))
                ->preservingOriginal()
                ->toMediaCollection('logo');
            $covers = [
                public_path('offers/offer1.png'),
                public_path('offers/offer2.png'),
                public_path('offers/offer3.png'),
                public_path('offers/offer4.png'),
            ];
            $coverImage = $this->faker->randomElement($covers);
            $offer->addMedia($coverImage)
                ->preservingOriginal()
                ->toMediaCollection('cover');

            $products = Product::where('vendor_id', $offer->creatable_id)
                ->inRandomOrder()->take(5)->pluck('id');
            $offer->products()->attach($products);
        });
    }

    public function quantity(): static
    {
        return $this->state(fn() => [
            'type' => 'quantity',
            'discount' => $this->faker->randomFloat(2, 5, 50),
            'buy' => null,
            'get' => null,
        ]);
    }

    public function purchase(): static
    {
        return $this->state(fn() => [
            'type' => 'purchase',
            'discount' => $this->faker->randomFloat(2, 5, 50),
            'buy' => null,
            'get' => null,
        ]);
    }

    public function custom(): static
    {
        return $this->state(fn() => [
            'type' => 'custom',
            'discount' => null,
            'buy' => $this->faker->numberBetween(1, 5),
            'get' => $this->faker->numberBetween(1, 3),
        ]);
    }
}
