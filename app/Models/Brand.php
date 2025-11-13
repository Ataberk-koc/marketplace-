<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
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

        static::creating(function ($brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }

    /**
     * Markaya ait ürünler
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Trendyol marka eşleştirmesi
     */
    public function trendyolMapping()
    {
        return $this->hasOne(BrandMapping::class);
    }

    /**
     * Trendyol marka eşleştirmesi (alias)
     */
    public function brandMapping()
    {
        return $this->hasOne(BrandMapping::class);
    }

    /**
     * Trendyol markası (mapping üzerinden)
     */
    public function trendyolBrand()
    {
        return $this->hasOneThrough(
            TrendyolBrand::class,
            BrandMapping::class,
            'brand_id',
            'id',
            'id',
            'trendyol_brand_id'
        );
    }
}
