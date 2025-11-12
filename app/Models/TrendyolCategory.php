<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrendyolCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'trendyol_category_id',
        'name',
        'parent_id',
    ];

    protected $casts = [
        'trendyol_category_id' => 'integer',
        'parent_id' => 'integer',
    ];

    /**
     * Eşleştirilmiş kategoriler
     */
    public function mappings()
    {
        return $this->hasMany(CategoryMapping::class);
    }

    /**
     * Eşleştirilmiş yerel kategoriler
     */
    public function categories()
    {
        return $this->hasManyThrough(
            Category::class,
            CategoryMapping::class,
            'trendyol_category_id',
            'id',
            'id',
            'category_id'
        );
    }
}
