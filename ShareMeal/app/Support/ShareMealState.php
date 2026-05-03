<?php

namespace App\Support;

use App\Models\User;
use App\Models\Article;
use App\Models\Store;
use App\Models\Booking;
use App\Models\InventoryProduct;
use App\Models\Donation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use App\Notifications\FlashSaleNotification;
use Illuminate\Support\Facades\Notification;

class ShareMealState
{

    public static function boot(): void
    {
        // PERBAIKAN: Fitur auto-login dimatikan sementara agar Anda bisa mengetes
        // form Login & Register dengan benar.
        /*
        if (!Session::has('sharemeal.current_user_id')) {
            $defaultUser = User::query()->where('role', 'consumer')->where('name', 'Budi Santoso')->first();
            if ($defaultUser) {
                Session::put('sharemeal.current_user_id', $defaultUser->id);
            }
        }
        */
    }

    public static function currentUser(): array
    {
        self::boot();

        $user = User::query()->find(Session::get('sharemeal.current_user_id'))
            ?? User::query()->where('role', 'consumer')->first();

        return [
            'id' => $user?->id,
            'name' => $user?->name ?? 'Budi Santoso',
            'type' => $user?->role ?? 'consumer',
        ];
    }

    // PERBAIKAN: Login menggunakan ID yang pasti unik agar tidak salah akun
    public static function login(int $userId): void
    {
        Session::put('sharemeal.current_user_id', $userId);
    }

    public static function logout(): void
    {
        Session::forget('sharemeal.current_user_id');
        self::boot();
    }

    public static function get(string $key, mixed $default = []): mixed
    {
        return match ($key) {
            'stores' => [],
            'bookings' => [],
            'orders' => [],
            'inventory_products' => [],
            'transactions' => [],
            'donations' => \App\Models\Donation::query()->get()->map(function ($donation) {
                return [
                    'id' => $donation->id,
                    'store' => [
                        'name' => $donation->mitra->name ?? 'Mitra Default',
                        'address' => 'Jl. Pahlawan No. 10, Jakarta',
                        'phone' => '081234567890',
                    ],
                    'distance' => '1.5 km',
                    'items' => [
                        ['name' => $donation->title, 'quantity' => $donation->quantity, 'unit' => $donation->unit]
                    ],
                    'available_until' => $donation->expires_at ? \Carbon\Carbon::parse($donation->expires_at)->format('d M, H:i') : '18:00',
                    'claimed_at' => $donation->claimed_at ? \Carbon\Carbon::parse($donation->claimed_at)->format('d M, H:i') : null,
                    'delivered_at' => $donation->delivered_at ? \Carbon\Carbon::parse($donation->delivered_at)->format('d M, H:i') : null,
                    'status' => $donation->status === 'pending' ? 'available' : $donation->status,
                ];
            })->all(),
            'applications' => User::query()->whereIn('role', ['mitra', 'lembaga'])->where('is_verified', false)->orderBy('id')->get()->map(fn (User $user) => self::transformApplication($user))->all(),
            'users' => User::query()->orderBy('id')->get()->map(fn (User $user) => self::transformUser($user))->all(),
            'articles' => \App\Models\Article::query()->latest()->get()->map(fn ($a) => self::transformArticle($a))->all(),
            default => $default,
        };
    }

    public static function createBooking(int $storeId, int $dealId, int $quantity, string $consumerName): ?string
    {
        $store = Store::query()->find($storeId);
        $deal = Deal::query()->where('store_id', $storeId)->find($dealId);

        if (!$store || !$deal || $deal->stock < $quantity) {
            return null;
        }

        $deal->decrement('stock', $quantity);

        $bookingId = 'BK-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
        $consumerId = self::currentUser()['id'];

        Booking::query()->create([
            'id' => $bookingId,
            'store_id' => $storeId,
            'deal_id' => $dealId,
            'consumer_user_id' => $consumerId,
            'store_name' => $store->name,
            'deal_item' => $deal->item,
            'price' => $deal->discount_price,
            'quantity' => $quantity,
            'status' => 'pending',
            'consumer_name' => $consumerName,
            'booking_date' => now(),
            'pickup_time' => 'Hari ini, setelah pembayaran',
            'pickup_code' => 'PICK-' . Str::upper(Str::substr($bookingId, -6)),
        ]);

        if ($consumerId) {
            User::query()->whereKey($consumerId)->increment('transactions_count');
        }

        return $bookingId;
    }

    public static function updateBookingStatus(string $bookingId, string $status): void
    {
        Booking::query()->whereKey($bookingId)->update(['status' => $status]);
    }

    public static function completePayment(string $bookingId): void
    {
        Booking::query()->whereKey($bookingId)->update(['status' => 'ready']);
    }

    public static function submitReview(string $transactionId, int $rating, string $review): void
    {
        Booking::query()->whereKey($transactionId)->update([
            'rating' => $rating,
            'review' => $review,
            'status' => 'completed',
        ]);
    }

    public static function addInventoryProduct(array $payload): void
    {
        $store = Store::query()->where('name', 'Toko Roti Barokah')->first();

        InventoryProduct::query()->create([
            'store_id' => $store?->id,
            'name' => $payload['name'],
            'category' => $payload['category'],
            'price' => (int) $payload['price'],
            'discount_price' => 0,
            'stock' => (int) $payload['stock'],
            'expires_at' => Carbon::parse($payload['expires_at']),
            'status' => 'normal',
            'image' => 'https://images.unsplash.com/photo-1666114170628-b34b0dcc21aa?auto=format&fit=crop&q=80&w=1200',
            'description' => $payload['description'] ?? null,
        ]);
    }

    public static function setFlashSale(int $productId): void
    {
        $product = InventoryProduct::query()->with('store.favoritedBy')->find($productId);
        if (!$product) {
            return;
        }

        $product->update([
            'status' => 'flash-sale',
            'discount_price' => (int) floor($product->price * 0.7),
        ]);

        // Kirim notifikasi ke user yang memfavoritkan toko ini
        if ($product->store) {
            Notification::send(
                $product->store->favoritedBy,
                new FlashSaleNotification(
                    $product->store->name,
                    $product->name,
                    $product->discount_price
                )
            );
        }
    }

    public static function deleteInventoryProduct(int $productId): void
    {
        InventoryProduct::query()->whereKey($productId)->delete();
    }

    public static function updateOrderStatus(string $orderId, string $status): void
    {
        self::updateBookingStatus($orderId, $status);
    }

    public static function claimDonation(string $donationId): void
    {
        Donation::query()->whereKey($donationId)->update([
            'status' => 'claimed',
            'claimed_at' => now(),
            'tracking_status' => 'confirmed',
        ]);
    }

    public static function completeDonation(string $donationId): void
    {
        Donation::query()->whereKey($donationId)->update([
            'status' => 'completed',
            'tracking_status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public static function approveApplication(int $userId): void
    {
        User::query()->whereKey($userId)->update([
            'is_verified' => true,
            'verification_rejection_reason' => null,
        ]);
    }

    public static function rejectApplication(int $userId, string $reason): void
    {
        User::query()->whereKey($userId)->update([
            'is_verified' => false,
            'verification_rejection_reason' => $reason,
        ]);
    }

    public static function warnUser(int $userId): void
    {
        $user = User::query()->find($userId);
        if (!$user) {
            return;
        }

        $user->update([
            'status' => 'warned',
            'warnings_count' => $user->warnings_count + 1,
            'last_warning_at' => now()->toDateString(),
        ]);
    }

    public static function blockUser(int $userId, string $reason): void
    {
        User::query()->whereKey($userId)->update([
            'status' => 'blocked',
            'blocked_at' => now()->toDateString(),
            'block_reason' => $reason,
        ]);
    }

    public static function unblockUser(int $userId): void
    {
        User::query()->whereKey($userId)->update([
            'status' => 'active',
            'blocked_at' => null,
            'block_reason' => null,
        ]);
    }

    public static function saveArticle(array $payload, ?int $articleId = null): void
    {
        $attributes = [
            'title' => $payload['title'],
            'category' => $payload['category'],
            'status' => $payload['status'],
            'content' => $payload['content'],
            'author' => 'Admin System',
            'published_on' => now()->toDateString(),
            'image' => 'https://images.unsplash.com/photo-1593113702251-272b1bc414a9?auto=format&fit=crop&q=80&w=800',
            'read_time' => '4 min read',
        ];

        if ($articleId) {
            Article::query()->whereKey($articleId)->update($attributes);
            return;
        }

        Article::query()->create($attributes);
    }

    public static function deleteArticle(int $articleId): void
    {
        Article::query()->whereKey($articleId)->delete();
    }

    protected static function transformStore(Store $store): array
    {
        return [
            'id' => $store->id,
            'name' => $store->name,
            'category' => $store->category,
            'distance' => $store->distance,
            'rating' => (float) $store->rating,
            'reviews' => $store->reviews_count,
            'address' => $store->address,
            'deals' => $store->deals->map(fn (Deal $deal) => [
                'id' => $deal->id,
                'item' => $deal->item,
                'original_price' => $deal->original_price,
                'discount_price' => $deal->discount_price,
                'stock' => $deal->stock,
                'expires_in' => $deal->expires_in,
            ])->all(),
            'tags' => $store->tags ?? [],
            'is_favorite' => $store->is_favorite,
            'image' => $store->image,
        ];
    }

    protected static function transformBooking(Booking $booking): array
    {
        return [
            'id' => $booking->id,
            'booking_date' => optional($booking->booking_date)->toDateTimeString(),
            'store_id' => $booking->store_id,
            'store_name' => $booking->store_name,
            'deal_id' => $booking->deal_id,
            'deal_item' => $booking->deal_item,
            'price' => $booking->price,
            'quantity' => $booking->quantity,
            'status' => $booking->status,
            'pickup_time' => $booking->pickup_time,
            'consumer_name' => $booking->consumer_name,
            'rating' => $booking->rating,
            'review' => $booking->review,
            'pickup_code' => $booking->pickup_code,
        ];
    }

    protected static function transformOrder(Booking $booking): array
    {
        return [
            'id' => $booking->id,
            'order_date' => optional($booking->booking_date)->toDateTimeString(),
            'store_id' => $booking->store_id,
            'store_name' => $booking->store_name,
            'deal_id' => $booking->deal_id,
            'deal_item' => $booking->deal_item,
            'price' => $booking->price,
            'quantity' => $booking->quantity,
            'status' => $booking->status,
            'consumer_name' => $booking->consumer_name,
        ];
    }

    protected static function transformInventoryProduct(InventoryProduct $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category,
            'price' => $product->price,
            'discount_price' => $product->discount_price,
            'stock' => $product->stock,
            'expires_at' => optional($product->expires_at)->format('Y-m-d H:i'),
            'status' => $product->status,
            'image' => $product->image,
            'description' => $product->description,
        ];
    }

    protected static function transformTransaction(Booking $booking): array
    {
        $subtotal = $booking->price * $booking->quantity;

        return [
            'id' => $booking->id,
            'order_id' => $booking->id,
            'date' => optional($booking->booking_date)->format('Y-m-d H:i'),
            'store' => $booking->store_name,
            'store_address' => $booking->store?->address ?? 'Lihat detail di toko',
            'items' => [[
                'name' => $booking->deal_item,
                'quantity' => $booking->quantity,
                'price' => $booking->price,
            ]],
            'subtotal' => $subtotal,
            'discount' => 0,
            'total' => $subtotal,
            'status' => $booking->status,
            'pickup_code' => $booking->pickup_code,
            'rating' => $booking->rating,
            'review' => $booking->review,
        ];
    }

    protected static function transformDonation(Donation $donation): array
    {
        return [
            'id' => $donation->id,
            'store' => [
                'name' => $donation->store?->name ?? '-',
                'address' => $donation->store?->address ?? '-',
                'phone' => $donation->store?->owner?->phone ?? '021-00000000',
            ],
            'items' => $donation->items->map(fn ($item) => ['name' => $item->name, 'quantity' => $item->quantity])->all(),
            'distance' => $donation->distance,
            'available_until' => optional($donation->available_until)->format('Y-m-d H:i'),
            'status' => $donation->status,
            'claimed_at' => optional($donation->claimed_at)->format('Y-m-d H:i'),
            'tracking_status' => $donation->tracking_status,
            'delivered_at' => optional($donation->delivered_at)->format('Y-m-d H:i'),
        ];
    }

    protected static function transformApplication(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'type' => $user->role,
            'email' => $user->email,
            'phone' => $user->phone,
            'submitted_at' => optional($user->created_at)->format('Y-m-d H:i'),
            'documents' => [
                'ktp' => $user->document_ktp,
                'siup' => $user->document_siup,
                'nib' => $user->document_nib,
                'halal' => $user->document_halal,
            ],
            'status' => 'pending', // Applications are always pending if is_verified is false
            'rejection_reason' => $user->verification_rejection_reason,
        ];
    }

    protected static function transformUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'type' => $user->role,
            'status' => $user->status,
            'joined_at' => optional($user->joined_at)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'transactions' => $user->transactions_count,
            'warnings' => $user->warnings_count,
            'verified' => $user->is_verified,
            'last_warning' => optional($user->last_warning_at)->format('Y-m-d'),
            'warning_reason' => $user->warning_reason,
            'blocked_at' => optional($user->blocked_at)->format('Y-m-d'),
            'block_reason' => $user->block_reason,
        ];
    }

    protected static function transformArticle(Article $article): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'category' => $article->category,
            'status' => $article->status,
            'date' => optional($article->published_on)->format('Y-m-d') ?? optional($article->created_at)->format('Y-m-d'),
            'author' => $article->author,
            'content' => $article->content,
            'image' => $article->image,
            'read_time' => $article->read_time,
        ];
    }
}