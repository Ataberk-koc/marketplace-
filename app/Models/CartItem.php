<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'size_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * Ürün kalemi hangi sepete ait
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Ürün bilgisi
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Beden bilgisi
     */
    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    /**
     * Satır toplamı
     */
    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }
}
