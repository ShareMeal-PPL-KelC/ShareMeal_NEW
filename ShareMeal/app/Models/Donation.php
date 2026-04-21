<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    protected $fillable = [
        'mitra_id',
        'lembaga_id',
        'title',
        'description',
        'quantity',
        'unit',
        'expires_at',
        'status',
        'pickup_time',
        'image',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'pickup_time' => 'datetime',
    ];

    public function mitra(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lembaga_id');
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'store_id',
        'distance',
        'available_until',
        'status',
        'claimed_at',
        'tracking_status',
        'delivered_at',
    ];

    protected $casts = [
        'available_until' => 'datetime',
        'claimed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function items()
    {
        return $this->hasMany(DonationItem::class, 'donation_id');
    }
}
