<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrendyolProductMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'trendyol_product_id',
        'status',
        'trendyol_response',
        'sent_at',
        'approved_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sent_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Yerel ürün
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Ürün Trendyol'a gönderilmeye hazır mı kontrol eder
     * (Marka, kategori ve beden eşleştirmeleri yapılmış olmalı)
     */
    public function isReadyToSend(): bool
    {
        $product = $this->product;
        
        // Marka eşleşmesi kontrolü
        if (!$product->brand || !$product->brand->trendyolMapping) {
            return false;
        }

        // Kategori eşleşmesi kontrolü
        if (!$product->category || !$product->category->trendyolMapping) {
            return false;
        }

        // Eğer ürünün bedeni varsa, beden eşleşmesi kontrolü
        if ($product->sizes->count() > 0) {
            foreach ($product->sizes as $size) {
                if (!$size->trendyolMapping) {
                    return false;
                }
            }
        }

        return true;
    }
}
