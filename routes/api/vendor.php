<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Vendor\API\AuthController;
use App\Http\Controllers\Vendor\API\CartController;
use App\Http\Controllers\Vendor\API\FeedController;
use App\Http\Controllers\Vendor\API\GiftController;
use App\Http\Controllers\Vendor\API\HomeController;
use App\Http\Controllers\Vendor\API\BrandController;
use App\Http\Controllers\Vendor\API\OfferController;
use App\Http\Controllers\Vendor\API\OrderController;
use App\Http\Controllers\Vendor\API\PointController;
use App\Http\Controllers\Vendor\API\RolesController;
use App\Http\Controllers\Vendor\API\StoreController;
use App\Http\Controllers\Vendor\API\StoryController;
use App\Http\Controllers\Vendor\API\PolicyController;
use App\Http\Controllers\Vendor\API\ReviewController;
use App\Http\Controllers\Vendor\API\SearchController;
use App\Http\Controllers\Vendor\API\MyStoreController;
use App\Http\Controllers\Vendor\API\ProductController;
use App\Http\Controllers\Vendor\API\VoucherController;
use App\Http\Controllers\Vendor\API\CategoryController;
use App\Http\Controllers\Vendor\API\DiscountController;
use App\Http\Controllers\Vendor\API\DropDownController;
use App\Http\Controllers\Vendor\API\FollowerController;
use App\Http\Controllers\Vendor\API\RegisterController;
use App\Http\Controllers\Vendor\API\WishlistController;
use App\Http\Controllers\Vendor\API\RequestOTPController;
use App\Http\Controllers\Vendor\API\VendorUserController;
use App\Http\Controllers\Vendor\API\DeliveryAreaController;
use App\Http\Controllers\Vendor\API\SizeTemplateController;
use App\Http\Controllers\Vendor\API\VendorBranchController;
use App\Http\Controllers\Vendor\API\ElwekalaCollectionController;


Route::post('request-otp', [RequestOTPController::class, 'requestOTP']);
Route::post('register', [RegisterController::class, 'store']);
Route::post('login', [AuthController::class, 'login']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::get('dropdown/{model}', [DropDownController::class, 'index']);
Route::get('search', [SearchController::class, 'globalSearch']);

Route::prefix('home')->group(function () {
    Route::get('/', [HomeController::class, 'index']);
    Route::get('top-brands', [HomeController::class, 'getTopBrands']);
    Route::get('just-for-you', [HomeController::class, 'getJustForYou']);
    Route::get('recent-products', [HomeController::class, 'getRecentProducts']);
    Route::get('sliders', [HomeController::class, 'getSliders']);
    Route::get('slider-details/{id}', [HomeController::class, 'getSliderDetails']);
    Route::get('feeds', [HomeController::class, 'getFeeds']);
    Route::get('{slug}', [HomeController::class, 'getByCategory']);
    Route::get('products/{brand}', [HomeController::class, 'getBrandProductsById']);
    Route::get('products/{sub_sub_category_id}', [HomeController::class, 'getSubSubCategoryProductsById']);
    Route::get('feed/adjacent', [HomeController::class, 'getAdjacent']);
});
Route::middleware('auth:vendor-api')->group(function () {
    Route::post('register/first-step', [RegisterController::class, 'firstStep']);
    Route::post('register/second-step', [RegisterController::class, 'secondStep']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::middleware('registration.complete')->group(function () {
        Route::resource('brands', BrandController::class)->names('vendor.brands');
        Route::patch('brands/{brand}/toggle', [BrandController::class, 'toggleIsActive']);
        Route::resource('product', ProductController::class)->names('vendor.product');
        Route::get('product/{product}/sales', [ProductController::class, 'sales'])->name('vendor.product.sales');
        Route::resource('voucher', VoucherController::class)->names('vendor.voucher');
        Route::resource('gifts', GiftController::class)->names('vendor.gifts');
        Route::patch('gifts/{gift}/toggle-archive', [GiftController::class, 'toggleArchive']);
        Route::resource('points', PointController::class)->names('vendor.points');
        Route::patch('points/{point}/toggle-archive', [PointController::class, 'toggleArchive']);
        Route::resource('offer', OfferController::class)->names('vendor.offer');
        Route::resource('discounts', DiscountController::class)->names('vendor.discounts');
        Route::patch('discounts/{discount}/toggle-archive', [DiscountController::class, 'toggleArchive']);
        Route::prefix('product/{product}')->group(function () {
            Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
            Route::post('reviews', [ReviewController::class, 'store'])->middleware('auth')->name('reviews.store');
        });
        Route::middleware('main.account')->group(function () {
            Route::post('branch/store', [VendorBranchController::class, 'store']);
        });
        Route::resource('roles', RolesController::class)->names('vendor.roles');
        Route::resource('users', VendorUserController::class)->names('vendor.users');
        Route::get('profile', [VendorUserController::class, 'show']);
        Route::get('policies', [PolicyController::class, 'index']);
        Route::get('policies/{policy}', [PolicyController::class, 'show']);
        Route::resource('elwekala-collections', ElwekalaCollectionController::class)->names('vendor.elwekala-collections');
        Route::get('categories', [CategoryController::class, 'index'])->name('vendor.categories');
        Route::get('switch-branch', [VendorBranchController::class, 'switchBranch']);
        Route::get('branch-details', [VendorBranchController::class, 'branchDetails']);

        Route::prefix('store')->group(function () {
            Route::get('{id}/info', [StoreController::class, 'storeInfo']);
            Route::get('{id}/best-selling', [StoreController::class, 'getBestSellingProductsByStoreId']);
            Route::post('{id}/voucher/collect', [StoreController::class, 'collectVoucher']);
            Route::get('{id}/trending', [StoreController::class, 'getTrendingProductsByStoreId']);
            Route::get('{id}/new-arrival', [StoreController::class, 'getNewArrivalProductsByStoreId']);
            Route::get('{id}/offers', [StoreController::class, 'getOffer']);
            Route::get('{id}/offer/{offer_id}/products', [StoreController::class, 'getOfferProducts']);
            Route::get('{id}/feed/{feed_id}/details', [StoreController::class, 'getFeedDetails']);
            Route::get('{id}/feed/adjacent', [StoreController::class, 'getAdjacent']);
            Route::get('{id}/products', [StoreController::class, 'getStoreProductsById']);
            Route::get('{id}/category', [StoreController::class, 'getCategoryStoresById']);
            Route::get('{id}/followers', [FollowerController::class, 'index']);
            Route::post('{id}/follow', [FollowerController::class, 'store']);
        });


        Route::prefix('mystore')->group(function () {
            Route::get('/info', [MyStoreController::class, 'storeInfo']);
            Route::get('followers', [MyStoreController::class, 'followers']);
            Route::get('/best-selling', [MyStoreController::class, 'getBestSellingProductsByStoreId']);
            Route::get('/trending', [MyStoreController::class, 'getTrendingProductsByStoreId']);
            Route::get('/new-arrival', [MyStoreController::class, 'getNewArrivalProductsByStoreId']);
            Route::get('offers', [MyStoreController::class, 'getOffer']);
            Route::get('/offer/{offer_id}/products', [MyStoreController::class, 'getOfferProducts']);
            Route::get('/feed/{feed_id}/details', [MyStoreController::class, 'getFeedDetails']);
            Route::get('/feed/adjacent', [MyStoreController::class, 'getAdjacent']);
            Route::get('/products', [MyStoreController::class, 'getStoreProductsById']);
            Route::get('/category', [MyStoreController::class, 'getCategoryStoresById']);
            Route::post('request-otp', [MyStoreController::class, 'requestOTP']);
            Route::post('update/owner-info', [MyStoreController::class, 'updateOwnerInfo'])->name('vendor.owner-info.update');
            Route::post('update/store-info', [MyStoreController::class, 'updateStoreInfo'])->name('vendor.store-info.update');
        });


        Route::resource('feeds', FeedController::class)->except('index');
        Route::get('feeds/all/{id}', [FeedController::class, 'index']);
        Route::resource('stories', StoryController::class)->except('index');
        Route::resource('delivery-areas', DeliveryAreaController::class)->names('vendor.delivery-areas');
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index']);
            Route::post('/', [CartController::class, 'store']);
            Route::post('/shipping-address', [CartController::class, 'shippingAddress']);
            Route::get('/shipping-address', [CartController::class, 'shippingAddresses']);
            Route::delete('item/{id}', [CartController::class, 'destroy']);
            Route::delete('item/all/{vendor_id}', [CartController::class, 'destroyAll']);
        });
        Route::post('checkout', [OrderController::class, 'checkout']);
        Route::get('orders/seller', [OrderController::class, 'getSellerOrders']);
        Route::get('orders/buyer', [OrderController::class, 'getBuyerOrders']);
        Route::get('orders/consumer', [OrderController::class, 'listConsumerOrders']);
        Route::get('order/{id}', [OrderController::class, 'show']);
        Route::post('order/{order}/verify-code', [OrderController::class, 'verifyOrder']);
        Route::post('order/{id}/change-status', [OrderController::class, 'changeStatus']);
        Route::post('order/{id}/cancel', [OrderController::class, 'cancelOrder']);
        Route::resource('sizes-templates', SizeTemplateController::class);
        Route::post('sizes-patterns/{id}', [SizeTemplateController::class, 'getSizePatternsBySizeTemplateId'])
            ->name('vendor.sizes-patterns');
        Route::resource('wishlists', WishlistController::class)->only(['index', 'store'])->names('vendor.wishlists');
    });
});
