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
        Schema::create('size_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Vendor::class)->constrained()->onDelete('cascade');
            $table->string('template_name');
            $table->float('chest', 8, 2);
            $table->float('chest_pattern', 8, 2);
            $table->float('product_length', 8, 2);
            $table->float('length_pattern', 8, 2);
            $table->float('weight_from', 8, 2);
            $table->float('weight_from_pattern', 8, 2);
            $table->float('weight_to', 8, 2);
            $table->float('weight_to_pattern', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('size_templates');
    }
};
