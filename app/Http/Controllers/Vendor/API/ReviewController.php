<?php

namespace App\Http\Controllers\Vendor\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    // List reviews for a product
    public function index(Product $product)
    {
        $reviews = $product->reviews()->with('reviewable')->latest()->paginate(10);
        return ReviewResource::collection($reviews);
    }

    // Store a new review
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
            'images_videos' => 'array',
            'images_videos.*' => 'file|mimes:jpg,png,jpeg,gif,mp4,mov,avi|max:20480',
        ]);

        $user = Auth::user();
        if (!$user) {
            throw ValidationException::withMessages(['user' => 'You must be logged in to submit a review.']);
        }
        return \DB::transaction(function () use ($request, $product, $user) {

            $review = $user->reviews()->create([
                'product_id' => $product->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'has_images_or_videos' => $request->hasFile('images_videos'),
            ]);


            // Update is_repeat_customer after saving
            $existingReviewsCount = Review::where('product_id', $product->id)
                ->where('reviewable_id', $user->id)
                ->where('reviewable_type', get_class($user) ?? 'App\Models\VendorUser')
                ->count();
            $isRepeatCustomer = $existingReviewsCount > 0; // True if this is the second or more review

            $review->update(['is_repeat_customer' => $isRepeatCustomer]);

            if ($request->hasFile('images_videos')) {
                foreach ($request->file('images_videos') as $file) {
                    $review->addMedia($file)->toMediaCollection('images_videos');
                }
            }

            return new ReviewResource($review);
        });
    }

}
