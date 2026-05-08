<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'item',
        'original_price',
        'discount_price',
        'stock',
        'expires_in',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
