<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Trendyol eşleştirmelerini basitleştir
     * Trendyol verilerini tutmak yerine sadece ID eşleştirmesi yapacağız
     */
    public function up(): void
    {
        // Eski tabloları drop et
        Schema::dropIfExists('size_mappings');
        Schema::dropIfExists('category_mappings');
        Schema::dropIfExists('brand_mappings');
        Schema::dropIfExists('trendyol_sizes');
        Schema::dropIfExists('trendyol_categories');
        Schema::dropIfExists('trendyol_brands');

        // YENİ: Basitleştirilmiş Brand Mappings
        Schema::create('brand_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->onDelete('cascade');
            $table->string('trendyol_brand_id'); // Trendyol'un kendi brand ID'si (integer veya string olabilir)
            $table->string('trendyol_brand_name')->nullable(); // İsim sadece gösterim için
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique('brand_id'); // Bir brand sadece bir kez eşleştirilir
        });

        // YENİ: Basitleştirilmiş Category Mappings
        Schema::create('category_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('trendyol_category_id'); // Trendyol'un kendi category ID'si
            $table->string('trendyol_category_name')->nullable(); // İsim sadece gösterim için
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique('category_id');
        });

        // YENİ: Basitleştirilmiş Size Mappings
        Schema::create('size_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('size_id')->constrained()->onDelete('cascade');
            $table->string('trendyol_size_name'); // Trendyol'un beden adı (S, M, L, XL vs.)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique('size_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('size_mappings');
        Schema::dropIfExists('category_mappings');
        Schema::dropIfExists('brand_mappings');

        // Eski tabloları geri getir (opsiyonel)
        Schema::create('trendyol_brands', function (Blueprint $table) {
            $table->id();
            $table->string('trendyol_brand_id')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('brand_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->onDelete('cascade');
            $table->foreignId('trendyol_brand_id')->constrained()->onDelete('cascade');
            $table->string('trendyol_brand_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['brand_id', 'trendyol_brand_id']);
        });

        Schema::create('trendyol_categories', function (Blueprint $table) {
            $table->id();
            $table->string('trendyol_category_id')->unique();
            $table->string('name');
            $table->string('parent_id')->nullable();
            $table->timestamps();
        });

        Schema::create('category_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('trendyol_category_id')->constrained()->onDelete('cascade');
            $table->string('trendyol_category_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['category_id', 'trendyol_category_id']);
        });

        Schema::create('trendyol_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('size_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('size_id')->constrained()->onDelete('cascade');
            $table->foreignId('trendyol_size_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['size_id', 'trendyol_size_id']);
        });
    }
};
