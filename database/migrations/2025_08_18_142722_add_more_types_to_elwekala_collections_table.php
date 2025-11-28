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
        Schema::table('elwekala_collections', function (Blueprint $table) {
            $table->enum('type', ['flash_sale', 'feeds', 'best_sellers', 'new_arrivals', 'most_popular'])
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('elwekala_collections', function (Blueprint $table) {
            $table->enum('type', ['feeds', 'best_sellers', 'new_arrivals', 'most_popular'])
                ->nullable()
                ->change();
        });
    }
};
