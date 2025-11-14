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
        Schema::table('products', function (Blueprint $table) {
            // Trendyol API zorunlu alanları
            $table->integer('vat_rate')->default(20)->after('discount_price')->comment('KDV Oranı (%)');
            $table->decimal('dimensional_weight', 8, 2)->default(1.0)->after('vat_rate')->comment('Desi (Hacimsel Ağırlık)');
            $table->integer('cargo_company_id')->nullable()->after('dimensional_weight')->comment('Kargo Şirketi ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['vat_rate', 'dimensional_weight', 'cargo_company_id']);
        });
    }
};
