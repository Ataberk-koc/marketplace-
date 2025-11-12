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
        Schema::table('product_trendyol_mappings', function (Blueprint $table) {
            $table->decimal('custom_price', 10, 2)->nullable()->after('attribute_mappings');
            $table->decimal('custom_sale_price', 10, 2)->nullable()->after('custom_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_trendyol_mappings', function (Blueprint $table) {
            $table->dropColumn(['custom_price', 'custom_sale_price']);
        });
    }
};
