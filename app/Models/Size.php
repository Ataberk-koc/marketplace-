<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Bedene ait ürünler (many-to-many)
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_size')
            ->withPivot('stock_quantity', 'additional_price')
            ->withTimestamps();
    }

    /**
     * Trendyol beden eşleştirmesi
     */
    public function trendyolMapping()
    {
        return $this->hasOne(SizeMapping::class);
    }

    /**
     * Trendyol bedeni (mapping üzerinden)
     */
    public function trendyolSize()
    {
        return $this->hasOneThrough(
            TrendyolSize::class,
            SizeMapping::class,
            'size_id',
            'id',
            'id',
            'trendyol_size_id'
        );
    }
}
