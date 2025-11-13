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
        Schema::create('trendyol_attribute_mappings', function (Blueprint $table) {
            $table->id();
            
            // Local Option & Value
            $table->foreignId('option_id')->constrained('options')->onDelete('cascade');
            $table->foreignId('option_value_id')->constrained('option_values')->onDelete('cascade');
            
            // Trendyol Attribute & Value
            $table->string('trendyol_attribute_id')->comment('Trendyol API Attribute ID');
            $table->string('trendyol_attribute_name')->comment('e.g., Beden, Renk');
            $table->string('trendyol_value_id')->comment('Trendyol API Value ID');
            $table->string('trendyol_value_name')->comment('e.g., Kırmızı, M');
            
            // Metadata
            $table->string('trendyol_category_id')->nullable()->comment('Which category this mapping is for');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique constraint: One local value can only map to one Trendyol value per category
            $table->unique(['option_value_id', 'trendyol_category_id'], 'unique_value_category_mapping');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trendyol_attribute_mappings');
    }
};
