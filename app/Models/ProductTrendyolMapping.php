<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTrendyolMapping extends Model
{
    protected $fillable = [
        'product_id',
        'trendyol_category_id',
        'trendyol_category_name',
        'trendyol_brand_id',
        'trendyol_brand_name',
        'attribute_mappings',
        'is_active',
        'trendyol_product_id',
        'batch_request_id',
        'sent_at',
        'status',
    ];

    protected $casts = [
        'attribute_mappings' => 'array',
        'is_active' => 'boolean',
        'sent_at' => 'datetime',
    ];

    /**
     * İlişki: Ürün
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * İlişki: Trendyol Kategori
     */
    public function trendyolCategory(): BelongsTo
    {
        return $this->belongsTo(TrendyolCategory::class);
    }

    /**
     * İlişki: Trendyol Marka
     */
    public function trendyolBrand(): BelongsTo
    {
        return $this->belongsTo(TrendyolBrand::class);
    }
}
