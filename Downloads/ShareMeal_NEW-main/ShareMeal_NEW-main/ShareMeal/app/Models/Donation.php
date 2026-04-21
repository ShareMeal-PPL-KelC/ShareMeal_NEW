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
    }
}
