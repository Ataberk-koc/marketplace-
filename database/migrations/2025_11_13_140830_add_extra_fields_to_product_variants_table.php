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
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('cost', 10, 2)->nullable()->after('discount_price')->comment('Maliyet fiyatı');
            $table->string('tny_code', 100)->nullable()->after('barcode')->comment('Trendyol ürün kodu');
            $table->string('integration_code', 100)->nullable()->after('tny_code')->comment('Entegrasyon kodu');
            $table->string('variant_name')->nullable()->after('option_values')->comment('Varyant adı (örn: Beden: M - Renk: Siyah)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['cost', 'tny_code', 'integration_code', 'variant_name']);
        });
    }
};
