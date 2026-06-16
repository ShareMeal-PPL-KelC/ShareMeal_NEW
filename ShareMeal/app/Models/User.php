<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * User-editable fields (via validated forms) and system-internal fields.
     *
     * SECURITY NOTE: System-only columns (is_verified, warnings_count, blocked_at, etc.)
     * MUST NEVER be populated via $request->all() or unfiltered input.
     * Controllers must always use $request->validate() + only specific keys.
     *
     * @var list<string>
     */
    protected $fillable = [
        // User-facing fields
        'name',
        'email',
        'password',
        'role',
        'phone',
        'status',
        'organization_name',
        'joined_at',
        // Document fields (set only during registration/upload flow)
        'document_ktp',
        'document_siup',
        'document_nib',
        'document_halal',
        'document_legalitas',
        'document_izin',
        'document_identitas',
        // System/admin-only fields (set ONLY via internal controller logic, NEVER from raw request)
        'transactions_count',
        'warnings_count',
        'is_verified',
        'verification_rejection_reason',
        'last_warning_at',
        'warning_reason',
        'blocked_at',
        'block_reason',
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
        'displayName',
        'image',
        'category',
        'rating',
        'reviews',
        'address',
        'distance',
        'tags',
        'isFavorite',
    ];

    public function getDisplayNameAttribute()
    {
        return $this->profile?->business_name ?? $this->organization_name ?? $this->name;
    }

    public function getActiveDealsAttribute()
    {
        return $this->products()->where('status', 'flash-sale')->where('stock', '>', 0)->count();
    }

    public function getImageAttribute()
    {
        if ($this->profile && $this->profile->avatar) {
            if (str_starts_with($this->profile->avatar, 'http://') || str_starts_with($this->profile->avatar, 'https://')) {
                return $this->profile->avatar;
            }

            if ($this->profile->avatar === 'images/profile' || $this->profile->avatar === 'images/profile.png') {
                return asset('images/profile.png');
            }

            return Storage::url($this->profile->avatar);
        }

        if ($this->role === 'consumer') {
            return asset('images/profile.png');
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
        // Check for eager-loaded average (from withAvg)
        if (array_key_exists('reviews_as_mitra_avg_rating', $this->attributes)) {
            $avg = $this->attributes['reviews_as_mitra_avg_rating'];
            return $avg ? number_format($avg, 1) : '4.8';
        }
        
        // Fallback to profile rating if profile is loaded
        if ($this->relationLoaded('profile') && $this->profile) {
            $profileRating = $this->profile->rating ?? 0;
            if ($profileRating > 0) return number_format($profileRating, 1);
        }
        
        return '4.8';
    }

    public function getReviewsAttribute()
    {
        // Check for eager-loaded count (from withCount)
        if (array_key_exists('reviews_as_mitra_count', $this->attributes)) {
            return (int) $this->attributes['reviews_as_mitra_count'];
        }

        // Avoid query if relation is not loaded and we are in a serialization context
        return 0;
    }

    public function getAddressAttribute()
    {
        return $this->profile ? ($this->profile->business_address ?? $this->profile->address ?? 'Alamat tidak tersedia') : 'Alamat tidak tersedia';
    }

    public function getDistanceAttribute()
    {
        return '0.5 km';
    }

    public function getTagsAttribute()
    {
        $tags = [];
        
        // 1. Check from profile business type or category
        $category = strtolower($this->category);
        if (str_contains($category, 'bakery') || str_contains($category, 'roti')) {
            $tags[] = 'bakery';
        }
        if (str_contains($category, 'healthy') || str_contains($category, 'sehat') || str_contains($category, 'salad')) {
            $tags[] = 'healthy';
        }
        if (str_contains($category, 'nusantara') || str_contains($category, 'indonesian') || str_contains($category, 'indonesia') || str_contains($category, 'warung') || str_contains($category, 'warteg')) {
            $tags[] = 'indonesian';
        }

        // 2. Check from products category
        try {
            if ($this->relationLoaded('products')) {
                foreach ($this->products as $product) {
                    $pCat = strtolower($product->category);
                    if (str_contains($pCat, 'bakery') || str_contains($pCat, 'roti')) {
                        $tags[] = 'bakery';
                    }
                    if (str_contains($pCat, 'healthy') || str_contains($pCat, 'sehat') || str_contains($pCat, 'salad')) {
                        $tags[] = 'healthy';
                    }
                    if (str_contains($pCat, 'indonesian') || str_contains($pCat, 'indonesia') || str_contains($pCat, 'warung') || str_contains($pCat, 'warteg')) {
                        $tags[] = 'indonesian';
                    }
                }
            } else {
                $productCategories = $this->products()->pluck('category')->toArray();
                foreach ($productCategories as $pCat) {
                    $pCat = strtolower($pCat);
                    if (str_contains($pCat, 'bakery') || str_contains($pCat, 'roti')) {
                        $tags[] = 'bakery';
                    }
                    if (str_contains($pCat, 'healthy') || str_contains($pCat, 'sehat') || str_contains($pCat, 'salad')) {
                        $tags[] = 'healthy';
                    }
                    if (str_contains($pCat, 'indonesian') || str_contains($pCat, 'indonesia') || str_contains($pCat, 'warung') || str_contains($pCat, 'warteg')) {
                        $tags[] = 'indonesian';
                    }
                }
            }
        } catch (\Exception $e) {
            // Silence relation errors in early migrations/seeding
        }

        // 3. Match based on User/Organization Name
        $name = strtolower($this->name . ' ' . $this->organization_name);
        if (str_contains($name, 'roti') || str_contains($name, 'bakery') || str_contains($name, 'makmur') || str_contains($name, 'barokah')) {
            $tags[] = 'bakery';
        }
        if (str_contains($name, 'sehat') || str_contains($name, 'healthy') || str_contains($name, 'salad')) {
            $tags[] = 'healthy';
        }
        if (str_contains($name, 'nusantara') || str_contains($name, 'dapur') || str_contains($name, 'warung') || str_contains($name, 'warteg')) {
            $tags[] = 'indonesian';
        }

        // 4. Halal status
        if ($this->document_halal || str_contains($name, 'halal') || str_contains($name, 'makmur') || str_contains($name, 'barokah') || count($tags) > 0) {
            $tags[] = 'halal';
        }

        // Default fallback
        if (empty($tags) && $this->role === 'mitra') {
            $tags = ['halal', 'indonesian'];
        }

        return array_values(array_unique($tags));
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
