<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffersTable extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('desc')->nullable();
            $table->dateTime('start');
            $table->dateTime('end');
            $table->enum('type', ['quantity', 'purchase', 'custom'])->default('quantity');
            $table->decimal('discount', 10, 2)->nullable();
            $table->unsignedInteger('buy')->nullable();
            $table->unsignedInteger('get')->nullable();
            $table->morphs('creatable'); // For polymorphic relationship with creator
            $table->timestamps();
        });

        Schema::create('offer_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_products');
        Schema::dropIfExists('offers');
        Schema::dropIfExists('products');
    }
}
