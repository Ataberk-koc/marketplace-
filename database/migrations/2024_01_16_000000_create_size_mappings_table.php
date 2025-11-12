<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Beden eşleştirme tablosu - Bizim bedenlerimizi Trendyol bedenleri ile eşleştiriyoruz
     */
    public function up(): void
    {
        Schema::create('size_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('size_id')->constrained()->onDelete('cascade'); // Bizim beden
            $table->foreignId('trendyol_size_id')->constrained()->onDelete('cascade'); // Trendyol beden
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['size_id', 'trendyol_size_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('size_mappings');
    }
};
