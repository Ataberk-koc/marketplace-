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
        Schema::create('trendyol_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('trendyol_category_id')->unique(); // Trendyol'dan gelen category ID
            $table->string('name');
            $table->integer('parent_id')->nullable();
            $table->timestamps();
            
            $table->index('trendyol_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trendyol_categories');
    }
};
