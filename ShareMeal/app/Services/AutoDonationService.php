<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\Product;
use App\Models\User;
use App\Notifications\DonationAvailableNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Cache;

class AutoDonationService
{
    public function processProducts(?int $mitraId = null): array
    {
        // Add a throttle to prevent running on every single page load
        $cacheKey = 'auto_donation_last_run_' . ($mitraId ?? 'global');
        if (Cache::has($cacheKey)) {
            return ['status' => 'skipped', 'message' => 'Recently processed'];
        }

        try {
            $results = [
                'expired' => $this->markExpiredProducts($mitraId),
                'donated' => $this->moveDueProducts($mitraId),
            ];

            // Set throttle for 5 minutes
            Cache::put($cacheKey, true, now()->addMinutes(5));

            return $results;
        } catch (\Exception $e) {
            \Log::error('Error in AutoDonationService processProducts: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function markExpiredProducts(?int $mitraId = null): int
    {
        return DB::transaction(function () use ($mitraId) {
            $products = Product::whereIn('status', ['normal', 'flash-sale', 'donation'])
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->when($mitraId, fn ($query) => $query->where('user_id', $mitraId))
                ->get();

            if ($products->isEmpty()) return 0;

            foreach ($products as $product) {
                $product->update([
                    'status' => 'expired',
                    'stock' => 0,
                ]);

                if ($product->user) {
                    $product->user->notify(new \App\Notifications\ProductExpiredNotification($product));
                }
            }

            return $products->count();
        });
    }

    public function moveDueProducts(?int $mitraId = null): int
    {
        $movedDonations = collect();
        $mitra = $mitraId ? User::find($mitraId) : null;
        $mitraName = $mitra?->name ?? 'Resto Mitra';

        $count = DB::transaction(function () use ($mitraId, &$movedDonations) {
            $products = Product::whereIn('status', ['normal', 'flash-sale'])
                ->where('stock', '>', 0)
                ->where('donatable', true)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now()->addHours(2))
                ->where('expires_at', '>', now())
                ->when($mitraId, fn ($query) => $query->where('user_id', $mitraId))
                ->get();

            if ($products->isEmpty()) return 0;

            foreach ($products as $product) {
                $originalStock = $product->stock;

                $donation = Donation::create([
                    'mitra_id' => $product->user_id,
                    'title' => 'Otomatis: ' . $product->name,
                    'quantity' => $product->stock,
                    'unit' => 'pcs',
                    'expires_at' => $product->expires_at,
                    'pickup_start_time' => $product->pickup_start_time,
                    'pickup_end_time' => $product->pickup_end_time,
                    'description' => 'Didonasikan otomatis oleh sistem karena mendekati batas waktu kelayakan (2 jam).',
                    'status' => 'pending',
                    'image' => $product->getRawOriginal('image'),
                ]);

                $product->update([
                    'status' => 'donation',
                    'stock' => 0,
                ]);

                if ($product->user) {
                    $product->user->notify(new \App\Notifications\AutoDonatedNotification($product, $originalStock));
                }

                $movedDonations->push($donation);
            }

            return $products->count();
        });

        if ($movedDonations->isNotEmpty()) {
            $this->notifyLembagasSummary($mitraName, $movedDonations);
        }

        return $count;
    }

    private function notifyLembagasSummary(string $mitraName, Collection $donations): void
    {
        // Limit notification to first 5 lembagas for stability in demo/local environment
        $lembagas = User::where('role', 'lembaga')->take(5)->get();

        if ($lembagas->isEmpty()) {
            return;
        }

        // Increase time limit slightly for notification processing
        @set_time_limit(30);

        // If multiple items, send one summary notification instead of individual ones
        if ($donations->count() > 1) {
            $summaryTitle = $donations->count() . ' jenis makanan';
            $summaryQty = $donations->sum('quantity') . ' total item';
            
            Notification::send(
                $lembagas,
                new DonationAvailableNotification($mitraName, $summaryTitle, $summaryQty)
            );
        } else {
            $donation = $donations->first();
            Notification::send(
                $lembagas,
                new DonationAvailableNotification($mitraName, $donation->title, $donation->quantity . ' ' . $donation->unit)
            );
        }
    }

    public function releaseExpiredCartReservations(): int
    {
        return DB::transaction(function () {
            $expiredCarts = \App\Models\CartItem::with('product')->where('expires_at', '<=', now())->get();
            if ($expiredCarts->isEmpty()) {
                return 0;
            }

            foreach ($expiredCarts as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
                $item->delete();
            }

            return $expiredCarts->count();
        });
    }
}
