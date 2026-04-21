<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'store_id',
        'deal_id',
        'consumer_user_id',
        'store_name',
        'deal_item',
        'price',
        'quantity',
        'status',
        'consumer_name',
        'booking_date',
        'pickup_time',
        'rating',
        'review',
        'pickup_code',
    ];

    protected $casts = [
        'booking_date' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function consumer()
    {
        return $this->belongsTo(User::class, 'consumer_user_id');
    }
}
