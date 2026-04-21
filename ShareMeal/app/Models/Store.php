<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'name',
        'category',
        'distance',
        'rating',
        'reviews_count',
        'address',
        'image',
        'is_favorite',
        'tags',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
        'tags' => 'array',
        'rating' => 'decimal:1',
    ];

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorite_stores');
    }
}
