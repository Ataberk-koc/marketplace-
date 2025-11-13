<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptionValue extends Model
{
    protected $fillable = [
        'option_id',
        'value',
        'color_code',
        'image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Bu değerin ait olduğu opsiyon
     */
    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}
