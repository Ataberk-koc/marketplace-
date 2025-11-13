<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrendyolCategoryAttribute extends Model
{
    protected $fillable = [
        'trendyol_category_id',
        'attribute_id',
        'attribute_name',
        'attribute_type',
        'is_required',
        'allows_custom_value',
        'is_variant_based',
        'allowed_values',
        'display_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'allows_custom_value' => 'boolean',
        'is_variant_based' => 'boolean',
        'allowed_values' => 'array',
        'display_order' => 'integer',
    ];

    /**
     * Kategoriye ait zorunlu attribute'ları getir
     */
    public static function getRequiredAttributes($categoryId)
    {
        return static::where('trendyol_category_id', $categoryId)
            ->where('is_required', true)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Varyant bazlı attribute'ları getir (renk, beden)
     */
    public static function getVariantAttributes($categoryId)
    {
        return static::where('trendyol_category_id', $categoryId)
            ->where('is_variant_based', true)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Tüm attribute'ları düzenli getir
     */
    public static function getCategoryAttributes($categoryId)
    {
        return static::where('trendyol_category_id', $categoryId)
            ->orderBy('is_required', 'desc')
            ->orderBy('display_order')
            ->get()
            ->groupBy('is_required');
    }
}
