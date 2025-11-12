<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Kullanıcının admin olup olmadığını kontrol eder
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Kullanıcının satıcı olup olmadığını kontrol eder
     */
    public function isSeller(): bool
    {
        return $this->role === 'seller';
    }

    /**
     * Kullanıcının müşteri olup olmadığını kontrol eder
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Kullanıcının ürünleri (satıcı için)
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Kullanıcının sepeti
     */
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Kullanıcının siparişleri
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Satıcının sattığı ürünler (order items üzerinden)
     */
    public function soldItems()
    {
        return $this->hasMany(OrderItem::class, 'seller_id');
    }
}

