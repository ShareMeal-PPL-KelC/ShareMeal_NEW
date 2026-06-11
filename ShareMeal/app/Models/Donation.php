<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    use HasFactory;

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
        'pickup_start_time',
        'pickup_end_time',
        'image',
        'claimed_at',
        'delivered_at',
        'tracking_status',
    ];

    protected $appends = [
        'pickup_time_window',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'pickup_time' => 'datetime',
        'claimed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function mitra(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lembaga_id');
    }

    public function getPickupTimeWindowAttribute(): string
    {
        if (!empty($this->attributes['pickup_start_time']) && !empty($this->attributes['pickup_end_time'])) {
            return substr($this->attributes['pickup_start_time'], 0, 5) . ' - ' . substr($this->attributes['pickup_end_time'], 0, 5);
        }

        if ($this->mitra) {
            $openingHours = $this->mitra->profile?->business_opening_hours ?? $this->mitra->profile?->opening_hours;
            if ($openingHours) {
                return $openingHours;
            }
        }

        return 'Belum ditentukan';
    }
}
