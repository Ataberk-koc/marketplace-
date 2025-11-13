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
            // attributes JSON'dan option_values'a geçiyoruz
            // Örnek: [{"option_id": 1, "option_value_id": 5}, {"option_id": 2, "option_value_id": 12}]
            $table->json('option_values')->nullable()->after('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('option_values');
        });
    }
};
