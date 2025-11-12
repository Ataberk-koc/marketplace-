<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    /**
     * Sepetin sahibi
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Sepetteki ürünler
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Sepet toplam tutarı
     */
    public function getTotalAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    /**
     * Sepet ürün sayısı
     */
    public function getItemCountAttribute()
    {
        return $this->items->sum('quantity');
    }
}
