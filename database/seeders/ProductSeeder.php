<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // الصور المتاحة

        Product::all()->each(function ($product) {
            /** @var Product $product */
            $images = [
                'products/product1.jpg',
                'products/product2.jpg',
                'products/product3.jpg',
                'products/product4.jpg',
                'products/product5.jpg',
                'products/product6.jpg',
                'products/product7.jpg',
                'products/product8.jpg',
                'products/product9.jpg',
                'products/product10.jpg',
            ];
            $product->clearMediaCollection('images');
            $randomImage = Arr::random($images);

            $product
                ->addMedia(public_path($randomImage))
                ->preservingOriginal()
                ->toMediaCollection('images', 'public');
        });
    }
}
