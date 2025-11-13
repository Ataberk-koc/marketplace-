<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'product_variant_id',
        'type',
        'quantity',
        'balance_after',
        'reference_type',
        'reference_id',
        'note',
        'user_id',
    ];

    /**
     * Varyant ilişkisi
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * İşlemi yapan kullanıcı
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Stok hareketi tipleri
     */
    public static function types(): array
    {
        return [
            'in' => 'Stok Girişi',
            'out' => 'Stok Çıkışı',
            'reserved' => 'Rezerve',
            'released' => 'Rezerv İptali',
            'adjustment' => 'Düzeltme',
        ];
    }

    /**
     * Tip adını al
     */
    public function getTypeNameAttribute(): string
    {
        return self::types()[$this->type] ?? $this->type;
    }
}
