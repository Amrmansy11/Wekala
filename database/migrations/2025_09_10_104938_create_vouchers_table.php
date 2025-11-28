<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->unsignedInteger('number_of_use')->default(1);
            $table->unsignedInteger('number_of_use_per_person')->default(1);
            $table->boolean('for_all')->default(false);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->morphs('creatable'); // Adds creatable_id and creatable_type columns
            $table->timestamps();
        });
        Schema::create('voucher_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_product');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('products');
    }
}
