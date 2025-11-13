<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrendyolAttributeMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'option_id',
        'option_value_id',
        'trendyol_attribute_id',
        'trendyol_attribute_name',
        'trendyol_value_id',
        'trendyol_value_name',
        'trendyol_category_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Local Option (e.g., Color, Size)
     */
    public function option()
    {
        return $this->belongsTo(Option::class);
    }

    /**
     * Local Option Value (e.g., Red, M)
     */
    public function optionValue()
    {
        return $this->belongsTo(OptionValue::class);
    }

    /**
     * Scope: Active mappings only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By category
     */
    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('trendyol_category_id', $categoryId);
    }
}
