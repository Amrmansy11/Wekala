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
        Schema::table('orders', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['buyer_vendor_id']);
            $table->dropForeign(['seller_vendor_id']);
            
            // Make buyer_vendor_id and seller_vendor_id nullable
            $table->unsignedBigInteger('buyer_vendor_id')->nullable()->change();
            $table->unsignedBigInteger('seller_vendor_id')->nullable()->change();
            
            // Re-add foreign key constraints
            $table->foreign('buyer_vendor_id')
                ->references('id')
                ->on('vendors')
                ->onDelete('cascade');
            $table->foreign('seller_vendor_id')
                ->references('id')
                ->on('vendors')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['buyer_vendor_id']);
            $table->dropForeign(['seller_vendor_id']);
            
            // Make buyer_vendor_id and seller_vendor_id not nullable again
            $table->unsignedBigInteger('buyer_vendor_id')->nullable(false)->change();
            $table->unsignedBigInteger('seller_vendor_id')->nullable(false)->change();
            
            // Re-add foreign key constraints
            $table->foreign('buyer_vendor_id')
                ->references('id')
                ->on('vendors')
                ->onDelete('cascade');
            $table->foreign('seller_vendor_id')
                ->references('id')
                ->on('vendors')
                ->onDelete('cascade');
        });
    }
};
