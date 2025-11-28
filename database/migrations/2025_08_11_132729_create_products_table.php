<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->morphs('creatable'); // creatable_id & creatable_type
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->string('barcode')->unique();
            $table->decimal('wholesale_price', 10, 2);
            $table->decimal('consumer_price', 10, 2);
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();
            $table->foreignId('sub_sub_category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->integer('stock')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->boolean('elwekala_policy')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
