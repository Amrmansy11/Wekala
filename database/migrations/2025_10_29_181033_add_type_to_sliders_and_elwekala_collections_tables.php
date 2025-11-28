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
        Schema::table('sliders', function (Blueprint $table) {
            $table->enum('type', ['seller', 'consumer'])->default('consumer')->after('id');
        });

        // إضافة العمود لجدول elwekala_collections
        Schema::table('elwekala_collections', function (Blueprint $table) {
            $table->enum('type_elwekala', ['seller', 'consumer'])->default('consumer')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('elwekala_collections', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
