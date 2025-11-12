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
                $product->slug = Str::slug($product->name);
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
