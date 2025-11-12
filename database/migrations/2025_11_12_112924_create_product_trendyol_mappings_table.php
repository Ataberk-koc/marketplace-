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
        Schema::create('product_trendyol_mappings', function (Blueprint $table) {
            $table->id();
            
            // Ürün
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            // Trendyol Kategori
            $table->foreignId('trendyol_category_id')->constrained('trendyol_categories')->onDelete('cascade');
            $table->string('trendyol_category_name')->nullable();
            
            // Trendyol Marka
            $table->foreignId('trendyol_brand_id')->constrained('trendyol_brands')->onDelete('cascade');
            $table->string('trendyol_brand_name')->nullable();
            
            // Özellik Eşleştirmeleri (Beden, Renk, vb.)
            // JSON format: {"beden": {"S": 102, "M": 103, "L": 104}, "renk": {"Mavi": 204, "Kırmızı": 203}}
            $table->json('attribute_mappings')->nullable();
            
            // Durum
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Aynı ürün için birden fazla eşleştirme olmaması için unique constraint
            $table->unique('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_trendyol_mappings');
    }
};
