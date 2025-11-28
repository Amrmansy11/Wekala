<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_size_id')->nullable()->after('product_variant_id');
            $table->string('color')->nullable()->after('product_size_id');
            
            $table->foreign('product_size_id')->references('id')->on('product_sizes')->onDelete('set null');
            $table->index(['product_size_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['product_size_id']);
            $table->dropIndex(['product_size_id']);
            $table->dropColumn(['product_size_id', 'color']);
        });
    }
};
