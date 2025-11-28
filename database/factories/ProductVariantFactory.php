<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'color' => $this->faker->safeColorName(),
            'bags' => $this->faker->numberBetween(10, 50),
            'total_pieces' => $this->faker->numberBetween(50, 500),
        ];
    }
}
