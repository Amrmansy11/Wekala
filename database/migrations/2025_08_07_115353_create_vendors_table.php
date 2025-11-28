<?php

use App\Models\Category;
use App\Models\City;
use App\Models\State;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->enum('store_type', ['retailer', 'seller']);
            $table->json('store_name');
            $table->json('description')->nullable();
            $table->string('phone')->unique();
            $table->foreignIdFor(Category::class);
            $table->foreignIdFor(State::class);
            $table->foreignIdFor(City::class);
            $table->json('address')->nullable();
            $table->string('national_id_path')->nullable();
            $table->string('tax_card_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('parent_id')->nullable()->constrained('vendors');
            $table->string('logo')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
