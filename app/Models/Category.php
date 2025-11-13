<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Slug otomatik oluşturma
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Üst kategori
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Alt kategoriler
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Kategoriye ait ürünler
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Trendyol kategori eşleştirmesi
     */
    public function trendyolMapping()
    {
        return $this->hasOne(CategoryMapping::class);
    }

    /**
     * Trendyol kategori eşleştirmesi (alias)
     */
    public function categoryMapping()
    {
        return $this->hasOne(CategoryMapping::class);
    }

    /**
     * Trendyol kategorisi (mapping üzerinden)
     */
    public function trendyolCategory()
    {
        return $this->hasOneThrough(
            TrendyolCategory::class,
            CategoryMapping::class,
            'category_id',
            'id',
            'id',
            'trendyol_category_id'
        );
    }
}
