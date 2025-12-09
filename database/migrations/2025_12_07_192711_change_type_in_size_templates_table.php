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
        Schema::table('size_templates', function (Blueprint $table) {
            try {
                $table->dropColumn('type');
            }
            catch (\Exception $e) {
                // Do nothing if column does not exist
            }
            $table->enum('type', ['tshirt', 'pants'])->default('tshirt')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('size_templates', function (Blueprint $table) {
        });
    }
};
