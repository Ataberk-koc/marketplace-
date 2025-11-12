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
            $table->unsignedBigInteger('sent_by')->nullable()->after('sent_at');
            $table->text('error_message')->nullable()->after('sent_by');
            $table->json('trendyol_response')->nullable()->after('error_message');
            $table->enum('status', ['pending', 'sent', 'approved', 'rejected', 'error'])->default('pending')->after('trendyol_response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_trendyol_mappings', function (Blueprint $table) {
            $table->dropColumn(['trendyol_product_id', 'batch_request_id', 'sent_at', 'sent_by', 'error_message', 'trendyol_response', 'status']);
        });
    }
};
