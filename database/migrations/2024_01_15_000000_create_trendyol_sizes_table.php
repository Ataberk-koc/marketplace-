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
        Schema::create('trendyol_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('trendyol_size_code')->unique(); // Trendyol'dan gelen size code
            $table->string('name');
            $table->timestamps();
            
            $table->index('trendyol_size_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trendyol_sizes');
    }
};
