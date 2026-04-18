<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'mitra_id',
        'total_amount',
        'status',
        'pickup_code',
        'pickup_time',
    ];

    protected $casts = [
        'pickup_time' => 'datetime',
    ];

    protected $appends = [
        'total',
        'subtotal',
        'discount',
        'store',
        'storeAddress',
        'orderId',
        'pickupCode',
        'rating',
        'review',
        'amount',
        'customer',
        'time',
        'items_string',
    ];

    public function getAmountAttribute()
    {
        return $this->attributes['total_amount'] ?? 0;
    }

    public function getCustomerAttribute()
    {
        return $this->customerRelation ? $this->customerRelation->name : 'Unknown Customer';
    }

    public function getTimeAttribute()
    {
        return $this->created_at ? $this->created_at->diffForHumans() : '-';
    }

    public function getItemsStringAttribute()
    {
        if ($this->relationLoaded('items')) {
            return $this->items->map(function($item) {
                return ($item->product ? $item->product->name : 'Item') . ' (' . $item->quantity . ' pcs)';
            })->implode(', ');
        }
        return '-';
    }

    public function getOrderIdAttribute()
    {
        return 'ORD-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getPickupCodeAttribute()
    {
        return $this->attributes['pickup_code'] ?? '-';
    }

    public function getRatingAttribute()
    {
        return $this->reviewRelation ? $this->reviewRelation->rating : 0;
    }

    public function getReviewAttribute()
    {
        return $this->reviewRelation ? $this->reviewRelation->comment : null;
    }

    public function getTotalAttribute()
    {
        return $this->attributes['total_amount'] ?? 0;
    }

    public function getSubtotalAttribute()
    {
        if ($this->relationLoaded('items')) {
            return $this->items->sum(function($item) {
                $originalPrice = $item->product ? ($item->product->getRawOriginal('price') ?? $item->price) : $item->price;
                return $originalPrice * $item->quantity;
            });
        }
        return $this->attributes['total_amount'] ?? 0;
    }

    public function getDiscountAttribute()
    {
        return $this->getSubtotalAttribute() - $this->getTotalAttribute();
    }

    public function getStoreAttribute()
    {
        return $this->mitra ? $this->mitra->name : 'Unknown Store';
    }

    public function getStoreAddressAttribute()
    {
        return ($this->mitra && $this->mitra->profile) ? $this->mitra->profile->address : '-';
    }

    public function customerRelation(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function mitra(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviewRelation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Review::class);
    }
}
