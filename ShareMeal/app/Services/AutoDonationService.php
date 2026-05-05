<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\Product;
use App\Models\User;
use App\Notifications\DonationAvailableNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class AutoDonationService
{
    public function processProducts(?int $mitraId = null): array
    {
        return [
            'expired' => $this->markExpiredProducts($mitraId),
            'donated' => $this->moveDueProducts($mitraId),
        ];
    }

    public function markExpiredProducts(?int $mitraId = null): int
    {
        return DB::transaction(function () use ($mitraId) {
            $products = Product::whereIn('status', ['normal', 'flash-sale', 'donation'])
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->when($mitraId, fn ($query) => $query->where('user_id', $mitraId))
                ->lockForUpdate()
                ->get();

            foreach ($products as $product) {
                $product->update([
                    'status' => 'expired',
                    'stock' => 0,
                ]);
            }

            return $products->count();
        });
    }

    public function moveDueProducts(?int $mitraId = null): int
    {
        $movedDonations = collect();

        $count = DB::transaction(function () use ($mitraId, $movedDonations) {
            $products = Product::whereIn('status', ['normal', 'flash-sale'])
                ->where('stock', '>', 0)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now()->addHours(2))
                ->where('expires_at', '>', now())
                ->when($mitraId, fn ($query) => $query->where('user_id', $mitraId))
                ->lockForUpdate()
                ->get();

            foreach ($products as $product) {
                $donation = Donation::create([
                    'mitra_id' => $product->user_id,
                    'title' => 'Otomatis: ' . $product->name,
                    'quantity' => $product->stock,
                    'unit' => 'pcs',
                    'expires_at' => $product->expires_at,
                    'description' => 'Didonasikan otomatis oleh sistem karena mendekati batas waktu kelayakan (2 jam).',
                    'status' => 'pending',
                    'image' => $product->getRawOriginal('image'),
                ]);

                $product->update([
                    'status' => 'donation',
                    'stock' => 0,
                ]);

                $movedDonations->push($donation);
            }

            return $products->count();
        });

        $this->notifyLembagas($movedDonations);

        return $count;
    }

    private function notifyLembagas(Collection $donations): void
    {
        if ($donations->isEmpty()) {
            return;
        }

        $lembagas = User::where('role', 'lembaga')->get();

        if ($lembagas->isEmpty()) {
            return;
        }

        foreach ($donations as $donation) {
            $mitraName = User::find($donation->mitra_id)?->name ?? 'Resto Mitra';

            Notification::send(
                $lembagas,
                new DonationAvailableNotification($mitraName, $donation->title, $donation->quantity . ' ' . $donation->unit)
            );
        }
    }
}
