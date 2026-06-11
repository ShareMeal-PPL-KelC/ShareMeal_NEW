<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedback';

    protected $fillable = [
        'user_id',
        'category',
        'subject',
        'description',
        'rating',
        'screenshots',
        'status',
    ];

    protected $casts = [
        'screenshots' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'fitur' => 'Fitur Baru / Saran Fitur',
            'bug' => 'Laporan Bug / Error',
            'ui_ux' => 'Tampilan / UI/UX',
            'other' => 'Lain-lain',
            default => ucfirst($this->category),
        };
    }
}
