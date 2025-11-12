<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrendyolBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'trendyol_brand_id',
        'name',
    ];

    protected $casts = [
        'trendyol_brand_id' => 'integer',
    ];

    /**
     * Eşleştirilmiş markalar
     */
    public function mappings()
    {
        return $this->hasMany(BrandMapping::class);
    }

    /**
     * Eşleştirilmiş yerel markalar
     */
    public function brands()
    {
        return $this->hasManyThrough(
            Brand::class,
            BrandMapping::class,
            'trendyol_brand_id',
            'id',
            'id',
            'brand_id'
        );
    }
}
