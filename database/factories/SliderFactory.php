<?php

namespace Database\Factories;

use App\Models\Slider;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class SliderFactory extends Factory
{
    protected $model = Slider::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(99),
            'type' => $this->faker->randomElement(['seller', 'consumer']),
        ];
    }

    public function configure(): SliderFactory
    {
        return $this->afterCreating(function (Slider $slider) {
            $products = Product::query()->inRandomOrder()->take(4)->pluck('id');
            $slider->products()->attach($products);

            $slider
                ->addMedia(public_path('category.png'))
                ->preservingOriginal()
                ->toMediaCollection('images');
        });
    }
}
