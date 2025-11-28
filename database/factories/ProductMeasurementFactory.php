<?php

namespace Database\Factories;

use App\Models\ProductMeasurement;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductMeasurementFactory extends Factory
{
    protected $model = ProductMeasurement::class;

    public function definition(): array
    {
        return [
            'size' => $this->faker->randomElement(['S', 'M', 'L']),
            'waist' => $this->faker->numberBetween(28, 40),
            'length' => $this->faker->numberBetween(60, 80),
            'chest' => $this->faker->numberBetween(36, 50),
            'weight_range' => $this->faker->randomElement(['50-60kg', '60-70kg', '70-80kg']),
        ];
    }
}
