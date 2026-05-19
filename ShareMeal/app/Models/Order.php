<?php

namespace App\Models;

use App\Notifications\OrderStatusUpdated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Notification;

class Order extends Model
{
    protected static function booted()
    {
        static::created(function ($order) {
            $order->customerRelation->notify(new OrderStatusUpdated($order));
        });

        static::updated(function ($order) {
            if ($order->wasChanged('status')) {
                $order->customerRelation->notify(new OrderStatusUpdated($order));
            }
        });
    }

    protected $fillable = [
        'customer_id',
        'mitra_id',
        'total_amount',
        'status',
        'pickup_code',
        'pickup_time',
        'pickup_start_time',
        'pickup_end_time',
        'receiving_method',
        'delivery_fee',
        'delivery_time_slot',
    ];

    protected $casts = [
        'pickup_time' => 'datetime',
        'delivery_fee' => 'integer',
    ];

    protected $appends = [
        'total',
        'subtotal',
        'discount',
        'savedAmount',
        'store',
        'storeAddress',
        'orderId',
        'pickupCode',
        'rating',
        'review',
        'amount',
        'time',
        'items_string',
        'orderTime',
        'completedTime',
        'pickupTime',
        'expires_at',
    ];

    public function getExpiresAtAttribute()
    {
        if ($this->relationLoaded('items') && $this->items->count() > 0) {
            $minExpiresAt = $this->items->min(function($item) {
                return $item->product ? $item->product->expires_at : null;
            });
            
            if ($minExpiresAt) {
                return \Carbon\Carbon::parse($minExpiresAt);
            }
        }
        
        // Fallback if relation not loaded or no product data
        return $this->created_at ? $this->created_at->addHours(2) : now()->addHours(2);
    }

    public function getAmountAttribute()
    {
        return $this->attributes['total_amount'] ?? 0;
    }

    public function getTimeAttribute()
    {
        return $this->created_at ? $this->created_at->diffForHumans() : '-';
    }

    public function getOrderTimeAttribute()
    {
        return $this->created_at ? $this->created_at->format('d/m/Y H:i') : '-';
    }

    public function getCompletedTimeAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('d M Y, H:i') : '-';
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

    public function getPickupTimeAttribute()
    {
        if (!empty($this->attributes['pickup_start_time']) && !empty($this->attributes['pickup_end_time'])) {
            return substr($this->attributes['pickup_start_time'], 0, 5) . ' - ' . substr($this->attributes['pickup_end_time'], 0, 5);
        }

        $pickupTime = $this->attributes['pickup_time'] ?? null;

        return $pickupTime
            ? \Carbon\Carbon::parse($pickupTime)->format('H:i')
            : '-';
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
                $currentOriginalPrice = $item->product ? ($item->product->getRawOriginal('price') ?? $item->price) : $item->price;
                $effectiveOriginalPrice = max((int) $currentOriginalPrice, (int) $item->price);

                return $effectiveOriginalPrice * $item->quantity;
            });
        }

        return $this->getTotalAttribute();
    }

    public function getDiscountAttribute()
    {
        return max(0, $this->getSubtotalAttribute() - $this->getTotalAttribute());
    }

    public function getSavedAmountAttribute()
    {
        return $this->getTotalAttribute();
    }

    public function getStoreAttribute()
    {
        return $this->mitra ? $this->mitra->displayName : 'Unknown Store';
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
