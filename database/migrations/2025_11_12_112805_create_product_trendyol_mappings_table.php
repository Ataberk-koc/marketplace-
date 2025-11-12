<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Basitleştirilmiş yapı: Trendyol ID'lerini doğrudan string olarak saklıyoruz
     */
    public function up(): void
    {
        Schema::create('product_trendyol_mappings', function (Blueprint $table) {
            $table->id();
            
            // Ürün
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            // Trendyol Kategori ID (string - Trendyol'un kendi ID'si)
            $table->string('trendyol_category_id');
            $table->string('trendyol_category_name')->nullable();
            
            // Trendyol Marka ID (string - Trendyol'un kendi ID'si)
            $table->string('trendyol_brand_id');
            $table->string('trendyol_brand_name')->nullable();
            
            // Özellik Eşleştirmeleri (Beden, Renk, vb.)
            // JSON format: {"Beden": "S", "Renk": "Kırmızı", "Kumaş": "Pamuk"}
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
