<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'trendyol_category_id', // Artık string - Trendyol'un kendi ID'si
        'trendyol_category_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Yerel kategori
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
