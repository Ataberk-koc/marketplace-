<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'seller_id',
        'size_id',
        'product_name',
        'product_sku',
        'quantity',
        'price',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Sipariş
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Ürün
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Satıcı
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Beden
     */
    public function size()
    {
        return $this->belongsTo(Size::class);
    }
}
