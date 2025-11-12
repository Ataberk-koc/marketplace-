<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrendyolSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'trendyol_size_code',
        'name',
    ];

    /**
     * Eşleştirilmiş bedenler
     */
    public function mappings()
    {
        return $this->hasMany(SizeMapping::class);
    }

    /**
     * Eşleştirilmiş yerel bedenler
     */
    public function sizes()
    {
        return $this->hasManyThrough(
            Size::class,
            SizeMapping::class,
            'trendyol_size_id',
            'id',
            'id',
            'size_id'
        );
    }
}
