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
        Schema::table('feeds', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('vendor_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('feeds', function (Blueprint $table) {
            $table->dropForeign(['user_id']);

            $table->dropColumn('user_id');

            $table->unsignedBigInteger('vendor_id')->nullable(false)->change();
        });
    }
};
//--- End of file database/migrations/2025_11_21_124804_update_feed_add_user_id_and_make_vendor_nullable.php ---
