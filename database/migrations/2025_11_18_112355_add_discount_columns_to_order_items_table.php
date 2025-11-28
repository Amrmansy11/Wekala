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
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('discount_id')->nullable()->after('product_variant_id')->constrained()->nullOnDelete();
            $table->decimal('discount_percentage', 5)->default(0)->after('unit_price')->nullable();
            $table->decimal('discount_amount', 12)->default(0)->after('discount_percentage')->nullable();
            $table->decimal('price_after_discount', 12)->after('discount_amount')->nullable();
            $table->decimal('line_total_after_discount', 12)->after('line_total')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['discount_id']);
            $table->dropColumn([
                'discount_id',
                'discount_percentage',
                'discount_amount',
                'price_after_discount',
                'line_total_after_discount'
            ]);
        });
    }
};
