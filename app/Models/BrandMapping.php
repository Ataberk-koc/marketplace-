<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'trendyol_brand_id', // Artık string - Trendyol'un kendi ID'si
        'trendyol_brand_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Yerel marka
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
