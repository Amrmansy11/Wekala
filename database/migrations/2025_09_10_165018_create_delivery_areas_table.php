<?php

use App\Models\City;
use App\Models\State;
use App\Models\Vendor;
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
        Schema::create('delivery_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Vendor::class)->constrained('vendors')->cascadeOnDelete();
            $table->foreignIdFor(State::class)->constrained('states')->cascadeOnDelete();
            $table->foreignIdFor(City::class)->constrained('cities')->cascadeOnDelete();
            $table->string('district');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_areas');
    }
};
