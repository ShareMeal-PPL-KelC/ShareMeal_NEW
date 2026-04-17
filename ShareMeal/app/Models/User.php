<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'status',
        'organization_name',
        'joined_at',
        'transactions_count',
        'warnings_count',
        'is_verified',
        'verification_rejection_reason',
        'document_ktp',
        'document_siup',
        'document_nib',
        'document_halal',
        'last_warning_at',
        'warning_reason',
        'blocked_at',
        'block_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'status',
        'organization_name',
        'joined_at',
        'transactions_count',
        'warnings_count',
        'is_verified',
        'document_ktp',
        'document_siup',
        'document_nib',
        'document_halal',
        'last_warning_at',
        'warning_reason',
        'blocked_at',
        'block_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'joined_at' => 'date',
            'last_warning_at' => 'date',
            'blocked_at' => 'date',
            'is_verified' => 'boolean',
        ];
    }
}
