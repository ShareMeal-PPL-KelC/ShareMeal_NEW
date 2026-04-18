<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'image',
        'category',
        'rating',
        'reviews',
        'address',
        'distance',
        'tags',
        'isFavorite',
        'activeDeals'
    ];

    public function getActiveDealsAttribute()
    {
        return $this->products()->where('status', 'flash-sale')->where('stock', '>', 0)->count();
    }

    public function getImageAttribute()
    {
        if ($this->profile && $this->profile->avatar) {
            return $this->profile->avatar;
        }

        $name = strtolower($this->name);
        if (str_contains($name, 'bakery') || str_contains($name, 'roti')) {
            return 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600&h=400&fit=crop';
        }
        if (str_contains($name, 'healthy') || str_contains($name, 'salad')) {
            return 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&h=400&fit=crop';
        }
        if (str_contains($name, 'nusantara') || str_contains($name, 'dapur')) {
            return 'https://images.unsplash.com/photo-1543352632-fea6d4f83e78?w=600&h=400&fit=crop';
        }

        return 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=600&h=400&fit=crop';
    }

    public function getCategoryAttribute()
    {
        return $this->profile ? ($this->profile->business_type ?? 'Restoran') : 'Restoran';
    }

    public function getRatingAttribute()
    {
        return $this->profile ? ($this->profile->rating ?? 4.8) : 4.8;
    }

    public function getReviewsAttribute()
    {
        return $this->reviewsAsMitra()->count() ?: 125;
    }

    public function getAddressAttribute()
    {
        return $this->profile ? ($this->profile->address ?? 'Alamat tidak tersedia') : 'Alamat tidak tersedia';
    }

    public function getDistanceAttribute()
    {
        return '0.5 km';
    }

    public function getTagsAttribute()
    {
        return ['halal', 'bakery', 'healthy', 'indonesian'];
    }

    public function getIsFavoriteAttribute()
    {
        return false;
    }

    /**
     * Get the products for the mitra.
     */
    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the orders as a mitra.
     */
    public function mitraOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class, 'mitra_id');
    }

    /**
     * Get the orders as a customer.
     */
    public function customerOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function profile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserDocument::class);
    }

    public function articles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    public function donationsAsMitra(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Donation::class, 'mitra_id');
    }

    public function donationsAsLembaga(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Donation::class, 'lembaga_id');
    }

    public function reviewsAsCustomer(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    public function reviewsAsMitra(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class, 'mitra_id');
    }

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
        ];
    }
}
