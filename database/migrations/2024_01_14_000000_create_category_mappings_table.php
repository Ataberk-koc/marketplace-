<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Kategori eşleştirme tablosu - Bizim kategorilerimizi Trendyol kategorileri ile eşleştiriyoruz
     */
    public function up(): void
    {
        Schema::create('category_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // Bizim kategori
            $table->foreignId('trendyol_category_id')->constrained()->onDelete('cascade'); // Trendyol kategori (Laravel ID)
            $table->string('trendyol_category_name')->nullable(); // Trendyol kategori adı (bilgi amaçlı)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['category_id', 'trendyol_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_mappings');
    }
};
