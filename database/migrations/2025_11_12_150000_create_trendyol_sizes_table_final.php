<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Trendyol Sizes Tablosu
     * Kategoriye göre dinamik olarak Trendyol'dan çekilen özellikleri (attributeValueId) saklar
     */
    public function up(): void
    {
        // Trendyol Size/Attribute Values
        Schema::create('trendyol_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('trendyol_attribute_id'); // Trendyol'daki Attribute ID (Beden özelliğinin ID'si)
            $table->string('trendyol_attribute_value_id'); // Trendyol'daki Value ID (örn: "L" bedeninin ID'si)
            $table->string('attribute_name'); // Özellik adı (Beden, Renk vb.)
            $table->string('value_name'); // Değer adı (S, M, L, XL vb.)
            $table->string('trendyol_category_id')->nullable(); // Hangi kategoriye ait
            $table->timestamps();
            
            // Aynı değer tekrar kaydedilmesin
            $table->unique(['trendyol_attribute_id', 'trendyol_attribute_value_id'], 'unique_trendyol_attribute');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trendyol_sizes');
    }
};
