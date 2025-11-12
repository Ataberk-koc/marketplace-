<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'subtotal',
        'tax',
        'shipping_cost',
        'total',
        'payment_method',
        'payment_status',
        'shipping_address',
        'billing_address',
        'notes',
        'sms_sent',
        'sms_sent_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'sms_sent' => 'boolean',
        'sms_sent_at' => 'datetime',
    ];

    /**
     * Sipariş numarası otomatik oluşturma
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
        });
    }

    /**
     * Siparişi veren müşteri
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Sipariş kalemleri
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Siparişteki satıcılar
     */
    public function sellers()
    {
        return $this->hasManyThrough(
            User::class,
            OrderItem::class,
            'order_id',
            'id',
            'id',
            'seller_id'
        )->distinct();
    }
}
