<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('points', function (Blueprint $table) {
            // Add archived_at field
            $table->timestamp('archived_at')->nullable()->after('points');
            
            // Remove title field
            $table->dropColumn('title');
        });
    }

    public function down(): void
    {
        Schema::table('points', function (Blueprint $table) {
            // Add back title field
            $table->string('title')->after('id');
            
            // Remove archived_at field
            $table->dropColumn('archived_at');
        });
    }
};
