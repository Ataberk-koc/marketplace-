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
            $table->string('trendyol_brand_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_trendyol_mappings', function (Blueprint $table) {
            $table->string('trendyol_brand_id')->nullable(false)->change();
        });
    }
};
