<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('buyer_vendor_id');
            $table->unsignedBigInteger('seller_vendor_id');
            $table->unsignedBigInteger('vendor_user_id');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('delivery', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->enum('status', ['pending', 'paid', 'shipped', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('buyer_vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('seller_vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('vendor_user_id')->references('id')->on('vendor_users')->onDelete('cascade');

            $table->index(['buyer_vendor_id']);
            $table->index(['seller_vendor_id']);
            $table->index(['vendor_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};


