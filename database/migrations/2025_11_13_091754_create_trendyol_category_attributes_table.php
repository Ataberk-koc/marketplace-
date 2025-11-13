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
        Schema::create('trendyol_category_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('trendyol_category_id'); // Trendyol kategori ID
            $table->string('attribute_id'); // Trendyol attribute ID
            $table->string('attribute_name'); // Örn: 'Renk', 'Beden', 'Malzeme Bileşimi'
            $table->string('attribute_type')->default('text'); // text, select, multiSelect, numeric
            $table->boolean('is_required')->default(false); // Zorunlu mu?
            $table->boolean('allows_custom_value')->default(false); // Özel değer girebilir mi?
            $table->boolean('is_variant_based')->default(false); // Varyant bazlı mı? (renk/beden)
            $table->json('allowed_values')->nullable(); // İzin verilen değerler listesi
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index(['trendyol_category_id'], 'tca_category_idx');
            $table->index(['trendyol_category_id', 'is_required'], 'tca_category_required_idx');
            $table->unique(['trendyol_category_id', 'attribute_id'], 'tca_category_attr_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trendyol_category_attributes');
    }
};
