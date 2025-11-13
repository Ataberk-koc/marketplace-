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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->onDelete('cascade');
            
            // Hareket tipi: in (giriş), out (çıkış), reserved (rezerve), released (rezerv iptali), adjustment (düzeltme)
            $table->enum('type', ['in', 'out', 'reserved', 'released', 'adjustment'])->default('in');
            
            $table->integer('quantity')->comment('Miktar (pozitif veya negatif)');
            $table->integer('balance_after')->comment('İşlem sonrası stok durumu');
            
            // Referans bilgileri
            $table->string('reference_type')->nullable()->comment('Order, Return, Manual vb.');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('İlgili sipariş ID vb.');
            
            $table->text('note')->nullable()->comment('İşlem notu/açıklaması');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->comment('İşlemi yapan kullanıcı');
            
            $table->timestamps();
            
            // Index'ler
            $table->index('product_variant_id');
            $table->index('type');
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
