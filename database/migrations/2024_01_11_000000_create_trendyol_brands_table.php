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
        Schema::create('trendyol_brands', function (Blueprint $table) {
            $table->id();
            $table->integer('trendyol_brand_id')->unique(); // Trendyol'dan gelen brand ID
            $table->string('name');
            $table->timestamps();
            
            $table->index('trendyol_brand_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trendyol_brands');
    }
};
