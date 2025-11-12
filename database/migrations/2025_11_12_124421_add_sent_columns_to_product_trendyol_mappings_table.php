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
            $table->string('trendyol_product_id')->nullable()->after('attribute_mappings');
            $table->string('batch_request_id')->nullable()->after('trendyol_product_id');
            $table->timestamp('sent_at')->nullable()->after('batch_request_id');
            $table->enum('status', ['pending', 'sent', 'approved', 'rejected'])->default('pending')->after('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_trendyol_mappings', function (Blueprint $table) {
            $table->dropColumn(['trendyol_product_id', 'batch_request_id', 'sent_at', 'status']);
        });
    }
};
