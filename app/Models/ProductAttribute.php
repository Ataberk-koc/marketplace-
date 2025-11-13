<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttribute extends Model
{
    protected $fillable = [
        'product_id',
        'attribute_name',
        'attribute_value',
        'attribute_type',
        'trendyol_attribute_id',
        'trendyol_attribute_name',
        'display_order',
        'is_required',
        'is_variant',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_variant' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Ürün ilişkisi
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Attribute type'a göre icon döndür
     */
    public function getIconAttribute(): string
    {
        return match($this->attribute_type) {
            'color' => 'bi-palette',
            'size' => 'bi-rulers',
            'number' => 'bi-123',
            'select' => 'bi-list-ul',
            default => 'bi-tag',
        };
    }

    /**
     * Varyant attribute'ları getir
     */
    public static function getVariantAttributes($productId)
    {
        return static::where('product_id', $productId)
            ->where('is_variant', true)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Trendyol için format
     */
    public function toTrendyolFormat(): array
    {
        return [
            'attributeId' => $this->trendyol_attribute_id,
            'attributeName' => $this->trendyol_attribute_name ?? $this->attribute_name,
            'attributeValue' => $this->attribute_value,
        ];
    }
}
