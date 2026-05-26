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
        'image',
        'claimed_at',
        'delivered_at',
        'tracking_status',
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
}
