<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ürün eşleştirme ve Trendyol'a gönderim durumu tablosu
     */
    public function up(): void
    {
        Schema::create('trendyol_product_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Bizim ürün
            $table->string('trendyol_product_id')->nullable(); // Trendyol'a gönderildikten sonra dönen ID
            $table->enum('status', ['pending', 'mapped', 'sent', 'approved', 'rejected'])->default('pending');
            $table->text('trendyol_response')->nullable(); // Trendyol'dan gelen yanıt
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['product_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trendyol_product_mappings');
    }
};
