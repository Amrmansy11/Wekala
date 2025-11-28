<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Consumer\API\AuthController;
use App\Http\Controllers\Consumer\API\CartController;
use App\Http\Controllers\Consumer\API\FeedController;
use App\Http\Controllers\Consumer\API\GiftController;
use App\Http\Controllers\Consumer\API\HomeController;
use App\Http\Controllers\Consumer\API\OfferController;
use App\Http\Controllers\Consumer\API\OrderController;
use App\Http\Controllers\Consumer\API\PointController;
use App\Http\Controllers\Consumer\API\StoreController;
use App\Http\Controllers\Consumer\API\SearchController;
use App\Http\Controllers\Consumer\API\ProductController;
use App\Http\Controllers\Consumer\API\VoucherController;
use App\Http\Controllers\Consumer\API\DiscountController;
use App\Http\Controllers\Consumer\API\DropDownController;
use App\Http\Controllers\Consumer\API\WishlistController;
use App\Http\Controllers\Consumer\API\RequestOTPController;

Route::post('request-otp', [RequestOTPController::class, 'requestOTP']);
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::get('search', [SearchController::class, 'globalSearch'])->name('consumer.search');

Route::get('dropdown/{model}', [DropDownController::class, 'index']);
Route::prefix('home')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('consumer.home');
    Route::get('just-for-you', [HomeController::class, 'getJustForYou'])->name('consumer.just-for-you');
    Route::get('sliders', [HomeController::class, 'getSliders'])->name('consumer.sliders');
    Route::get('slider-details/{id}', [HomeController::class, 'getSliderDetails'])->name('consumer.slider-details');
    Route::get('{slug}', [HomeController::class, 'getByCategory'])->name('consumer.category');
});
Route::prefix('discounts')->group(function () {
    Route::get('/', [DiscountController::class, 'index'])->name('consumer.discounts.index');
    Route::get('{id}', [DiscountController::class, 'show'])->name('consumer.discounts.show');
});
Route::prefix('points')->group(function () {
    Route::get('/', [PointController::class, 'index'])->name('consumer.points.index');
});
Route::prefix('offers')->group(function () {
    Route::get('/', [OfferController::class, 'index'])->name('consumer.offers.index');
    Route::get('{id}', [OfferController::class, 'show'])->name('consumer.offers.show');
});
Route::prefix('vouchers')->group(function () {
    Route::get('/', [VoucherController::class, 'index'])->name('consumer.vouchers.index');
    Route::get('{id}', [VoucherController::class, 'show'])->name('consumer.vouchers.show');
});
Route::prefix('gifts')->group(function () {
    Route::get('/', [GiftController::class, 'index'])->name('consumer.gifts.index');
});
Route::resource('product', ProductController::class)->only(['index', 'show'])->names('consumer.product');


Route::prefix('store')->group(function () {
    Route::get('{id}/info', [StoreController::class, 'storeInfo']);
    Route::get('{id}/best-selling', [StoreController::class, 'getBestSellingProductsByStoreId']);
    Route::get('{id}/trending', [StoreController::class, 'getTrendingProductsByStoreId']);
    Route::get('{id}/new-arrival', [StoreController::class, 'getNewArrivalProductsByStoreId']);
    Route::get('{id}/points', [StoreController::class, 'getStorePointsById']);
    Route::get('{id}/offers', [StoreController::class, 'getOffer']);
    Route::get('{id}/offer/{offer_id}/products', [StoreController::class, 'getOfferProducts']);
    Route::get('{id}/feed/{feed_id}/details', [StoreController::class, 'getFeedDetails']);
    Route::get('{id}/feed/adjacent', [StoreController::class, 'getAdjacent']);
    Route::get('{id}/products', [StoreController::class, 'getStoreProductsById']);
    Route::get('{id}/category', [StoreController::class, 'getCategoryStoresById']);
    Route::get('{id}/voucher/{voucher_id}/products', [StoreController::class, 'getVoucherProducts']);
    Route::get('{id}/gifts', [StoreController::class, 'getGiftsByStoreId']);
});

Route::middleware('auth:consumer-api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::resource('wishlists', WishlistController::class)->only(['index', 'store'])->names('consumer.wishlists');
    Route::resource('feeds', FeedController::class)->except('show')->names('consumer.feeds');
    
    Route::prefix('store')->group(function () {
        Route::post('{id}/toggle_follow', [StoreController::class, 'toggleFollow']);
    });

    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::post('/shipping-address', [CartController::class, 'shippingAddress']);
        Route::delete('item/{id}', [CartController::class, 'destroy']);
        Route::delete('item/all/{vendor_id}', [CartController::class, 'destroyAll']);
    });
    Route::post('checkout', [OrderController::class, 'checkout']);
    Route::get('orders/buyer', [OrderController::class, 'getBuyerOrders']);
    // Route::get('order/{id}', [OrderController::class, 'show']);
});
