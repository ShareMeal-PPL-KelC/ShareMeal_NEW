<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'price',
        'discount_price',
        'stock',
        'expires_at',
        'pickup_start_time',
        'pickup_end_time',
        'status',
        'donatable',
        'image',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $appends = [
        'discount',
        'originalPrice',
        'discountPrice',
        'expiresIn',
        'quantity',
        'item',
        'store',
        'distance',
        'rating',
        'pickupTime',
        ];

        public function getItemAttribute()
        {
        return $this->name;
        }

        public function getStoreAttribute()
        {
        return $this->user ? $this->user->displayName : 'Unknown Store';
        }

        public function getDistanceAttribute()
        {
        return $this->user ? ($this->user->distance ?? '0.5 km') : '0.5 km';
        }

        public function getRatingAttribute()
        {
        return $this->user ? ($this->user->rating ?? 4.8) : 4.8;
        }

        public function getImageAttribute($value)
        {
            if (!$value) {
                return 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=500&h=300&fit=crop';
            }
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                return $value;
            }
            return asset('storage/' . $value);
        }

        public function getQuantityAttribute()
        {
        return $this->stock;
        }

        public function getOriginalPriceAttribute()
        {
        return $this->attributes['price'] ?? 0;
        }

        public function getDiscountPriceAttribute()
        {
            $discountPrice = $this->attributes['discount_price'] ?? 0;
            if (($this->status ?? '') === 'flash-sale' && $discountPrice <= 0) {
                return floor(($this->price ?? 0) * 0.7);
            }
            return $discountPrice;
        }

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
            return $this->expires_at->locale('id')->diffForHumans();
        }
        return '2 jam';
        }

        public function getPickupTimeAttribute()
        {
        if (!empty($this->attributes['pickup_start_time']) && !empty($this->attributes['pickup_end_time'])) {
            return substr($this->attributes['pickup_start_time'], 0, 5) . ' - ' . substr($this->attributes['pickup_end_time'], 0, 5);
        }
        return 'Belum ditentukan';
        }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
