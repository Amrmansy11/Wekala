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
            // Drop the foreign key constraint first
            $table->dropForeign(['vendor_user_id']);
            
            // Make vendor_user_id nullable
            $table->unsignedBigInteger('vendor_user_id')->nullable()->change();
            
            // Re-add the foreign key constraint
            $table->foreign('vendor_user_id')
                ->references('id')
                ->on('vendor_users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['vendor_user_id']);
            
            // Make vendor_user_id not nullable again
            $table->unsignedBigInteger('vendor_user_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint
            $table->foreign('vendor_user_id')
                ->references('id')
                ->on('vendor_users')
                ->onDelete('cascade');
        });
    }
};
