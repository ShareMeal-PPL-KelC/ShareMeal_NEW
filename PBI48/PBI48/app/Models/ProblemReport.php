<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProblemReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'mitra_id',
        'order_id',
        'donation_id',
        'issue_type',
        'description',
        'evidence_image',
        'status',
        'admin_note',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function mitra(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }

    public function getIssueLabelAttribute(): string
    {
        return match ($this->issue_type) {
            'expired' => 'Sudah Kedaluwarsa',
            'bad_quality' => 'Kualitas Buruk/Basi',
            'mismatch' => 'Tidak Sesuai Deskripsi',
            'other' => 'Lainnya',
            default => 'Laporan Masalah',
        };
    }
}
