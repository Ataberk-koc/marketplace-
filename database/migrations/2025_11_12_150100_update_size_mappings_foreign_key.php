<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Size Mappings tablosunu yeniden düzenle
     * Artık trendyol_sizes tablosuna foreign key ile bağlanacak
     */
    public function up(): void
    {
        Schema::table('size_mappings', function (Blueprint $table) {
            // Eski kolonu sil
            if (Schema::hasColumn('size_mappings', 'trendyol_size_name')) {
                $table->dropColumn('trendyol_size_name');
            }
            
            // Yeni foreign key ekle
            if (!Schema::hasColumn('size_mappings', 'trendyol_size_id')) {
                $table->foreignId('trendyol_size_id')
                    ->after('size_id')
                    ->constrained('trendyol_sizes')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('size_mappings', function (Blueprint $table) {
            if (Schema::hasColumn('size_mappings', 'trendyol_size_id')) {
                $table->dropForeign(['trendyol_size_id']);
                $table->dropColumn('trendyol_size_id');
            }
            
            if (!Schema::hasColumn('size_mappings', 'trendyol_size_name')) {
                $table->string('trendyol_size_name')->nullable();
            }
        });
    }
};
