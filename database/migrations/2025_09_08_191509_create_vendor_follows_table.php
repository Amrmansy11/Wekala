<?php

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
        Schema::create('vendor_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Vendor::class ,'vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignIdFor(Vendor::class ,'follower_id')->constrained('vendors')->cascadeOnDelete();
            $table->unique(['vendor_id', 'follower_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
   public function down(): void
    {
        Schema::dropIfExists('vendor_follows');
    }
};
