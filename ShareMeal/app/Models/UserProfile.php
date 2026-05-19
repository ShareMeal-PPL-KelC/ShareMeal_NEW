<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'pending_phone',
        'phone_otp_hash',
        'phone_otp_expires_at',
        'phone_verified_at',
        'phone_change_available_at',
        'address',
        'latitude',
        'longitude',
        'business_type',
        'business_name',
        'business_address',
        'business_contact',
        'business_pending_contact',
        'business_contact_otp_hash',
        'business_contact_otp_expires_at',
        'business_contact_verified_at',
        'business_contact_change_available_at',
        'business_opening_hours',
        'business_description',
        'description',
        'rating',
        'opening_hours',
        'avatar',
        'is_verified',
        'can_delivery',
        'delivery_fee',
    ];

    protected $casts = [
        'phone_otp_expires_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'phone_change_available_at' => 'datetime',
        'business_contact_otp_expires_at' => 'datetime',
        'business_contact_verified_at' => 'datetime',
        'business_contact_change_available_at' => 'datetime',
        'is_verified' => 'boolean',
        'can_delivery' => 'boolean',
        'delivery_fee' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
