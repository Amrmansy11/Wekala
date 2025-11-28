<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('points', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('earned_points')->default(0);
            $table->unsignedBigInteger('redeemed_points')->default(0);
            $table->unsignedBigInteger('vendor_id');
            $table->nullableMorphs('creatable');
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points');
    }
};


