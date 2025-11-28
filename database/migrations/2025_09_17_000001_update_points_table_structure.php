<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('points', function (Blueprint $table) {
            // Add new columns
            $table->enum('type', ['earned', 'redeemed'])->after('title');
            $table->unsignedBigInteger('points')->default(0)->after('type');
            
            // Drop old columns
            $table->dropColumn(['earned_points', 'redeemed_points']);
        });
    }

    public function down(): void
    {
        Schema::table('points', function (Blueprint $table) {
            // Add back old columns
            $table->unsignedBigInteger('earned_points')->default(0)->after('title');
            $table->unsignedBigInteger('redeemed_points')->default(0)->after('earned_points');
            
            // Drop new columns
            $table->dropColumn(['type', 'points']);
        });
    }
};


