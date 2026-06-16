<?php

namespace App\Support;

use App\Models\User;
use App\Models\Article;
use App\Models\Donation;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
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

        $userId = Session::get('sharemeal.current_user_id') ?? auth()->id();
        if ($userId && !Session::has('sharemeal.current_user_id')) {
            Session::put('sharemeal.current_user_id', $userId);
        }

        $user = User::query()->find($userId)
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
            'donations' => self::getDonationsQuery(\App\Models\Donation::query()),
            'applications' => User::query()->whereIn('role', ['mitra', 'lembaga'])->where('is_verified', false)->whereNull('verification_rejection_reason')->orderBy('id')->get()->map(fn (User $user) => self::transformApplication($user))->all(),
            'users' => User::query()->orderBy('id')->get()->map(fn (User $user) => self::transformUser($user))->all(),
            'articles' => \App\Models\Article::query()->orderByDesc('id')->get()->map(fn (\App\Models\Article $article) => self::transformArticle($article))->all(),
            default => $default,
        };
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

    public static function warnUser(int $userId, ?string $reason = null): void
    {
        $user = User::query()->find($userId);
        if (!$user) {
            return;
        }

        $user->update([
            'status' => 'warned',
            'warnings_count' => $user->warnings_count + 1,
            'last_warning_at' => now()->toDateString(),
            'warning_reason' => $reason ?? 'Pelanggaran ketentuan sistem.',
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
            'title'        => $payload['title'],
            'category'     => $payload['category'],
            'status'       => $payload['status'],
            'content'      => $payload['content'],
            'author'       => 'Admin System',
            'published_on' => now()->toDateString(),
            'read_time'    => '4 min read',
        ];

        // Jika ada upload baru, pakai path yang disimpan controller
        if (!empty($payload['image_path'])) {
            $attributes['image'] = $payload['image_path'];
        } elseif (!$articleId) {
            // Artikel baru tanpa gambar → pakai default
            $attributes['image'] = 'https://images.unsplash.com/photo-1593113702251-272b1bc414a9?auto=format&fit=crop&q=80&w=800';
        }
        // Update tanpa gambar baru → tidak ubah kolom image (biarkan gambar lama)

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



    protected static function transformApplication(User $user): array
    {
        $documents = [];
        if ($user->role === 'mitra') {
            $documents = [
                'ktp' => $user->document_ktp,
                'siup' => $user->document_siup,
                'nib' => $user->document_nib,
                'halal' => $user->document_halal,
            ];
        } elseif ($user->role === 'lembaga') {
            $documents = [
                'legalitas' => $user->document_legalitas,
                'izin' => $user->document_izin,
                'identitas' => $user->document_identitas,
            ];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'type' => $user->role,
            'email' => $user->email,
            'phone' => $user->phone,
            'submitted_at' => optional($user->created_at)->format('Y-m-d H:i'),
            'documents' => $documents,
            'status' => 'pending', // Applications are always pending if is_verified is false
            'rejection_reason' => $user->verification_rejection_reason,
        ];
    }

    public static function transformUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'type' => $user->role,
            'status' => $user->status,
            'joined_at' => optional($user->joined_at ?? $user->created_at)->format('d M Y') ?? '-',
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
        // Jika image adalah path lokal (bukan URL eksternal), ubah ke URL publik
        $image = $article->image;
        if ($image && !str_starts_with($image, 'http')) {
            $image = Storage::url($image);
        }

        return [
            'id'        => $article->id,
            'title'     => $article->title,
            'category'  => $article->category,
            'status'    => $article->status,
            'date'      => optional($article->published_on)->format('Y-m-d') ?? optional($article->created_at)->format('Y-m-d'),
            'author'    => $article->author,
            'content'   => $article->content,
            'image'     => $image,
            'read_time' => $article->read_time,
        ];
    }

    public static function getDonationsQuery(\Illuminate\Database\Eloquent\Builder $query): array
    {
        return $query->with(['mitra.profile'])->get()->map(function ($donation) {
            $openingHours = $donation->mitra?->profile?->business_opening_hours ?? $donation->mitra?->profile?->opening_hours ?? '08:00 - 20:00';
            $parts = explode('-', $openingHours);
            $fallbackStart = trim($parts[0] ?? '');
            $fallbackStart = (empty($fallbackStart) || strlen($fallbackStart) < 5) ? '08:05' : $fallbackStart;
            $fallbackEnd = trim($parts[1] ?? '');
            $fallbackEnd = (empty($fallbackEnd) || strlen($fallbackEnd) < 5) ? '20:05' : $fallbackEnd;

            return [
                'id' => $donation->id,
                'mitra_id' => $donation->mitra_id,
                'lembaga_id' => $donation->lembaga_id,
                'store' => [
                    'name' => $donation->mitra?->displayName ?? 'Mitra Default',
                    'address' => $donation->mitra?->profile?->business_address ?? $donation->mitra?->profile?->address ?? 'Jl. Pahlawan No. 10, Jakarta',
                    'phone' => $donation->mitra?->profile?->business_contact ?? $donation->mitra?->profile?->phone ?? $donation->mitra?->phone ?? '081234567890',
                ],
                'distance' => '1.5 km',
                'items' => [
                    ['name' => $donation->title, 'quantity' => $donation->quantity, 'unit' => $donation->unit]
                ],
                'available_until' => $donation->expires_at ? \Carbon\Carbon::parse($donation->expires_at)->format('d M, H:i') : '18:00',
                'expires_at' => $donation->expires_at ? $donation->expires_at->toIso8601String() : null,
                'pickup_start' => $donation->pickup_start_time ?: $fallbackStart,
                'pickup_end' => $donation->pickup_end_time ?: $fallbackEnd,
                'pickup_time' => $donation->pickup_time ? $donation->pickup_time->format('H:i') : null,
                'pickup_time_window' => $donation->pickup_time_window,
                'claimed_at' => $donation->claimed_at ? \Carbon\Carbon::parse($donation->claimed_at)->format('d M, H:i') : null,
                'delivered_at' => $donation->delivered_at ? \Carbon\Carbon::parse($donation->delivered_at)->format('d M, H:i') : null,
                'status' => ($donation->status === 'pending' && $donation->expires_at && \Carbon\Carbon::parse($donation->expires_at)->isPast()) ? 'expired' : ($donation->status === 'pending' ? 'available' : $donation->status),
            ];
        })->all();
    }
}