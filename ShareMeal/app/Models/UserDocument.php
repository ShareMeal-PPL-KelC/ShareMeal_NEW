<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDocument extends Model
{
    protected $fillable = [
        'user_id',
        'document_name',
        'file_path',
        'status',
        'rejection_reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
