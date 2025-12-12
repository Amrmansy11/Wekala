<?php

use App\Models\Offer;
use App\Models\Voucher;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('discount_type')->nullable()->after('parent_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignIdFor(Voucher::class)->nullable()->after('discount_id');
            $table->foreignIdFor(Offer::class)->nullable()->after('voucher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('discount_type');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['voucher_id']);
            $table->dropForeign(['offer_id']);
            $table->dropColumn(['voucher_id', 'offer_id']);
        });
    }
};
