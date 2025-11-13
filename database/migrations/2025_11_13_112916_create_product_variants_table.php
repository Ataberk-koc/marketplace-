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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique()->comment('Varyant SKU/Model Kodu');
            $table->string('barcode')->unique()->nullable()->comment('Barkod');
            
            // Varyant özellikleri (renk, beden, vb.)
            $table->json('attributes')->nullable()->comment('Varyant özellikleri: renk, beden vb.');
            
            // Fiyat bilgileri
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount_price', 10, 2)->nullable();
            
            // Stok bilgileri
            $table->integer('stock_quantity')->default(0);
            $table->integer('reserved_quantity')->default(0)->comment('Rezerve edilen/bekleyen siparişlerdeki miktar');
            $table->integer('low_stock_threshold')->default(5)->comment('Düşük stok eşiği');
            
            // Görsel ve durum
            $table->string('image')->nullable()->comment('Varyanta özel görsel');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Index'ler
            $table->index('product_id');
            $table->index('sku');
            $table->index('barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
