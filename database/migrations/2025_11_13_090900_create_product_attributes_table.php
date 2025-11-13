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
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('attribute_name'); // 'Renk', 'Beden', 'Malzeme', vb.
            $table->string('attribute_value'); // 'Kırmızı', 'M', 'Pamuk', vb.
            $table->string('attribute_type')->default('text'); // text, color, size, number, select
            $table->string('trendyol_attribute_id')->nullable(); // Trendyol attribute ID
            $table->string('trendyol_attribute_name')->nullable(); // Trendyol attribute name
            $table->integer('display_order')->default(0); // Sıralama
            $table->boolean('is_required')->default(false); // Zorunlu mu?
            $table->boolean('is_variant')->default(false); // Varyant mı? (renk/beden gibi)
            $table->timestamps();
            
            $table->index(['product_id', 'attribute_name']);
            $table->index(['product_id', 'is_variant']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attributes');
    }
};
