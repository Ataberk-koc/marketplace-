<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $fillable = [
        'name',
        'type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Opsiyonun değerleri
     */
    public function values()
    {
        return $this->hasMany(OptionValue::class)->orderBy('sort_order');
    }

    /**
     * Aktif değerler
     */
    public function activeValues()
    {
        return $this->hasMany(OptionValue::class)->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Bu opsiyonu kullanan ürünler
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_options')
            ->withPivot('is_required', 'sort_order')
            ->withTimestamps();
    }
}
