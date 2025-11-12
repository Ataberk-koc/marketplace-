<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SizeMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'size_id',
        'trendyol_size_id', // Foreign key to trendyol_sizes table
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Yerel beden
     */
    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    /**
     * Trendyol beden
     */
    public function trendyolSize()
    {
        return $this->belongsTo(TrendyolSize::class, 'trendyol_size_id');
    }
}
