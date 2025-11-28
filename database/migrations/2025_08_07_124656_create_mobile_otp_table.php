<?php

use App\Models\VendorUser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mobile_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(VendorUser::class)->nullable();
            $table->enum('otp_type', ['email', 'phone'])->default('phone');
            $table->string('otp_value');
            $table->string('verification_code');
            $table->enum('action', ['login', 'register', 'reset_password', 'change_phone']);
            $table->timestamp('expires_at');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_otp');
    }
};
