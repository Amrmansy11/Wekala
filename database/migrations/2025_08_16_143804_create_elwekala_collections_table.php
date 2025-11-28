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
        Schema::create('elwekala_collections', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['feeds', 'best_sellers', 'new_arrivals', 'most_popular'])->nullable();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elwekala_collections');
    }
};
