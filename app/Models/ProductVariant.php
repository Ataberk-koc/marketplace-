<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'attributes',
        'option_values',
        'price',
        'discount_price',
        'cost',
        'stock_quantity',
        'reserved_quantity',
        'low_stock_threshold',
        'image',
        'is_active',
        'sort_order',
        'tny_code',
        'integration_code',
        'variant_name',
    ];

    protected $casts = [
        'attributes' => 'array',
        'option_values' => 'array',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Ana ürün ilişkisi
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Stok hareketleri ilişkisi
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Kullanılabilir stok miktarı (toplam - rezerve)
     */
    public function getAvailableStockAttribute(): int
    {
        return max(0, $this->stock_quantity - $this->reserved_quantity);
    }

    /**
     * Stoğun düşük olup olmadığını kontrol et
     */
    public function isLowStock(): bool
    {
        return $this->available_stock <= $this->low_stock_threshold;
    }

    /**
     * Stoğun bitmiş olup olmadığını kontrol et
     */
    public function isOutOfStock(): bool
    {
        return $this->available_stock <= 0;
    }

    /**
     * Varyant adı (attributes'dan oluşturulur)
     */
    public function getNameAttribute(): string
    {
        if (!$this->attributes) {
            return 'Standart';
        }

        $parts = [];
        foreach ($this->attributes as $key => $value) {
            $parts[] = $value;
        }

        return implode(' - ', $parts);
    }

    /**
     * İndirimli fiyat varsa onu, yoksa normal fiyatı döndür
     */
    public function getFinalPriceAttribute()
    {
        return $this->discount_price ?? $this->price;
    }
}
