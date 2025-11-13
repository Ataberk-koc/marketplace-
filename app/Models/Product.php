<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'brand_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'model_code',
        'description',
        'price',
        'discount_price',
        'stock_quantity',
        'images',
        'attributes',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'images' => 'array',
        'attributes' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Slug otomatik oluşturma
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $baseSlug = Str::slug($product->name);
                $slug = $baseSlug;
                $counter = 1;
                
                // Benzersiz slug oluştur
                while (self::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                
                $product->slug = $slug;
            }
        });
    }

    /**
     * Ürünün satıcısı
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Ürünün markası
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Ürünün kategorisi
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Ürünün bedenleri (many-to-many)
     */
    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'product_size')
            ->withPivot('stock_quantity', 'additional_price')
            ->withTimestamps();
    }

    /**
     * Ürün özellikleri (attributes)
     */
    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class)->orderBy('display_order');
    }

    /**
     * Ürün varyantları (YENİ SİSTEM)
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Aktif varyantlar
     */
    public function activeVariants()
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    /**
     * Ürün opsiyonları (many-to-many)
     */
    public function options()
    {
        return $this->belongsToMany(\App\Models\Option::class, 'product_options')
            ->withPivot('is_required', 'sort_order')
            ->withTimestamps();
    }

    /**
     * Varyant özellikleri (renk, beden vb.)
     */
    public function variantAttributes()
    {
        return $this->hasMany(ProductAttribute::class)
            ->where('is_variant', true)
            ->orderBy('display_order');
    }

    /**
     * Zorunlu özellikler
     */
    public function requiredAttributes()
    {
        return $this->hasMany(ProductAttribute::class)
            ->where('is_required', true)
            ->orderBy('display_order');
    }

    /**
     * Sepet kalemleri
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Sipariş kalemleri
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Trendyol ürün eşleştirmesi (YENİ - Tek tablo sistemi)
     */
    public function trendyolMapping()
    {
        return $this->hasOne(ProductTrendyolMapping::class);
    }

    /**
     * ESKİ Trendyol ürün eşleştirmesi (Compatibility için korundu)
     */
    public function trendyolProductMapping()
    {
        return $this->hasOne(TrendyolProductMapping::class);
    }

    /**
     * İndirimli fiyat hesaplama
     */
    public function getFinalPriceAttribute()
    {
        return $this->discount_price ?? $this->price;
    }

    /**
     * Ana görsel
     */
    public function getMainImageAttribute()
    {
        return $this->images[0] ?? null;
    }
}
