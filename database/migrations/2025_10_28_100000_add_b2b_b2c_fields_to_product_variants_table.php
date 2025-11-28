<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('total_pieces_b2c')->nullable()->after('quantity_b2c');
            $table->integer('total_pieces_b2b')->nullable()->after('total_pieces_b2c');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['total_pieces_b2c', 'total_pieces_b2b']);
        });
    }
};

