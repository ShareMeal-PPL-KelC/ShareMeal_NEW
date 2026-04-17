<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'category',
        'price',
        'discount_price',
        'stock',
        'expires_at',
        'status',
        'image',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $appends = [
        'discount',
        'expiresIn',
    ];

    public function getDiscountAttribute()
    {
        $price = $this->attributes['price'] ?? 0;
        $discountPrice = $this->attributes['discount_price'] ?? 0;
        
        if ($price > 0 && $discountPrice > 0) {
            return round((($price - $discountPrice) / $price) * 100);
        }
        return 0;
    }

    public function getExpiresInAttribute()
    {
        if (isset($this->attributes['expires_at']) && $this->expires_at) {
            return $this->expires_at->diffForHumans();
        }
        return 'N/A';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
