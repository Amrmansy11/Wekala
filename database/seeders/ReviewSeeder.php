<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::query()
            ->where('type', 'b2b_b2c')
            ->get();

        $defaultImages = [
            public_path('products/product1.jpg'),
            public_path('products/product2.jpg'),
            public_path('products/product3.jpg'),
            public_path('products/product4.jpg'),
            public_path('products/product5.jpg'),
            public_path('products/product6.jpg'),
            public_path('products/product7.jpg'),
            public_path('products/product8.jpg'),
            public_path('products/product9.jpg'),
            public_path('products/product10.jpg'),
        ];


        foreach ($products as $product) {
            $reviewsCount = 10;

            for ($i = 0; $i < $reviewsCount; $i++) {
                $review = Review::create([
                    'product_id' => $product->id,
                    'rating' => rand(1, 5),
                    'comment' => 'This is a sample review for ' . $product->name,
                    'has_images_or_videos' => rand(0, 1) ? true : false,
                    'is_repeat_customer' => rand(0, 1) ? true : false,
                    'reviewable_type' => 'App\Models\User',
                    'reviewable_id' => User::inRandomOrder()->first()->id,
                ]);
                if ($review->getMedia('images_videos')->isEmpty()) {
                    $randomImage = $defaultImages[array_rand($defaultImages)];
                    $review->addMedia($randomImage)
                        ->usingName('Review Image')
                        ->preservingOriginal()
                        ->toMediaCollection('images_videos');
                }
            }
        }
    }
}
