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
        Schema::table('products', function (Blueprint $table) {
            $table->enum('type', ['b2b', 'b2c', 'b2b_b2c'])->default('b2c')->after('status');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('quantity_b2b')->nullable()->after('total_pieces');
            $table->integer('quantity_b2c')->nullable()->after('quantity_b2b');
        });

        Schema::table('product_measurements', function (Blueprint $table) {
            $table->integer('bundles')->nullable()->after('weight_range');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['quantity_b2b', 'quantity_b2c']);
        });

        Schema::table('product_measurements', function (Blueprint $table) {
            $table->dropColumn('bundles');
        });
    }
};
