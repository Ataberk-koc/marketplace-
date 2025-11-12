<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Marka eşleştirme tablosu - Bizim markalarımızı Trendyol markaları ile eşleştiriyoruz
     */
    public function up(): void
    {
        Schema::create('brand_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->onDelete('cascade'); // Bizim marka
            $table->foreignId('trendyol_brand_id')->constrained()->onDelete('cascade'); // Trendyol marka (Laravel ID)
            $table->string('trendyol_brand_name')->nullable(); // Trendyol marka adı (bilgi amaçlı)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['brand_id', 'trendyol_brand_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_mappings');
    }
};
