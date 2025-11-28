<?php

namespace Database\Factories;

use App\Models\Vendor;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoucherFactory extends Factory
{
    protected $model = Voucher::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'code' => 'VOUCHER-' . strtoupper(uniqid()),
            'percentage' => $this->faker->numberBetween(5, 50),
            'amount' => $this->faker->numberBetween(10, 200),
            'number_of_use' => $this->faker->numberBetween(10, 100),
            'number_of_use_per_person' => $this->faker->numberBetween(1, 5),
            'for_all' => $this->faker->boolean(),
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(30),
            'creatable_type' => 'App\Models\Vendor',
            'creatable_id' => 92,
        ];
    }
    public function configure(): self
    {
        return $this->afterCreating(function (Voucher $voucher) {
            $products = Product::where('vendor_id', $voucher->creatable_id)
                ->inRandomOrder()
                ->B2BB2C()
                ->take(10)->pluck('id');

            $voucher->products()->attach($products);
        });
    }
}
