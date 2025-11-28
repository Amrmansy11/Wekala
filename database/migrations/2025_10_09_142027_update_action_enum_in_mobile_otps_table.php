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
        Schema::table('mobile_otps', function (Blueprint $table) {

            $table->enum('action', ['login', 'register', 'reset_password', 'change_phone', 'update_owner_info'])
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobile_otps', function (Blueprint $table) {
            $table->enum('action', ['login', 'register', 'reset_password', 'change_phone'])
                ->change();
        });
    }
};
