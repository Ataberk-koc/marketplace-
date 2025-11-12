<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'trendyol_category_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Yerel kategori
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Trendyol kategorisi
     */
    public function trendyolCategory()
    {
        return $this->belongsTo(TrendyolCategory::class);
    }
}
