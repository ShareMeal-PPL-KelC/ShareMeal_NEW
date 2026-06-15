<?php

namespace App\Http\Controllers\Lembaga;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\User;
use App\Support\ShareMealState;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class LembagaDonationController extends Controller
{
    public function lembagaDonations(): View
    {
        $userId = Auth::id() ?? Session::get('sharemeal.current_user_id');

        $donationsQuery = Donation::query()
            ->where(function ($q) {
                $q->where('status', 'pending')
                  ->where(function ($q2) {
                      $q2->whereNull('expires_at')
                         ->orWhere('expires_at', '>', now());
                  });
            })
            ->orWhere('lembaga_id', $userId)
            ->orderBy('created_at', 'desc');

        $donations = ShareMealState::getDonationsQuery($donationsQuery);

        return view('pages.lembaga.donations', $this->dashboardData('lembaga', 'Kelola Donasi', 'Klaim & tracking donasi makanan') + [
            'donations' => $donations,
            'activeTab' => request('tab', 'available'),
        ]);
    }

    public function lembagaHistory(): View
    {
        $userId = Auth::id() ?? Session::get('sharemeal.current_user_id');

        $completedDonationsQuery = Donation::query()
            ->where('lembaga_id', $userId)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc');

        $completedDonations = ShareMealState::getDonationsQuery($completedDonationsQuery);

        return view('pages.lembaga.history', $this->dashboardData('lembaga', 'Riwayat Penerimaan Donasi', 'Daftar donasi makanan yang berhasil diterima') + [
            'completedDonations' => $completedDonations,
        ]);
    }

    public function lembagaClaimDonation(Request $request, string $donationId): RedirectResponse
    {
        $userId = Auth::id() ?? Session::get('sharemeal.current_user_id');
        $user = User::find($userId);

        if (!$user || !$user->is_verified) {
            return back()->with('error', 'Klaim donasi gagal. Akun Lembaga Anda belum terverifikasi atau telah ditolak oleh admin.');
        }

        $request->validate([
            'pickup_time' => ['required', 'string'],
        ]);

        $donation = Donation::with('mitra')->findOrFail($donationId);

        if ($donation->status !== 'pending' || ($donation->expires_at && Carbon::parse($donation->expires_at)->isPast())) {
            return back()->with('error', 'Donasi sudah tidak tersedia atau telah kedaluwarsa.');
        }

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $request->pickup_time)) {
                $pickupTime = Carbon::parse($request->pickup_time);
            } else {
                $pickupTime = Carbon::today()->setTimeFromTimeString($request->pickup_time);
                if ($pickupTime->isPast()) {
                    $pickupTime->addDay();
                }
            }
        } catch (\Exception $e) {
            $pickupTime = Carbon::today()->setTimeFromTimeString($request->pickup_time);
        }

        $donation->update([
            'status' => 'claimed',
            'claimed_at' => now(),
            'pickup_time' => $pickupTime,
            'tracking_status' => 'confirmed',
            'lembaga_id' => $userId
        ]);

        User::query()->whereKey($userId)->increment('transactions_count');
        User::query()->whereKey($donation->mitra_id)->increment('transactions_count');

        if ($donation->mitra) {
            $lembagaName = Auth::user()->name ?? User::find($userId)?->name ?? 'Lembaga Sosial';
            Notification::send(
                $donation->mitra,
                new \App\Notifications\DonationClaimedNotification($lembagaName, $donation->title, $donation->quantity . ' ' . $donation->unit)
            );
        }

        return back()->with('success', 'Donasi berhasil diklaim. Jadwal penjemputan: ' . $pickupTime->format('H:i'));
    }

    public function lembagaCompleteDonation(string $donationId): RedirectResponse
    {
        $donation = Donation::findOrFail($donationId);

        if ($donation->status !== 'prepared') {
            return back()->with('error', 'Hanya donasi yang sudah disiapkan yang bisa diselesaikan.');
        }

        $donation->update([
            'status' => 'completed',
            'delivered_at' => now(),
            'tracking_status' => 'delivered',
        ]);

        if ($donation->mitra) {
            $userId = Auth::id() ?? Session::get('sharemeal.current_user_id');
            $lembagaName = Auth::user()->name ?? User::find($userId)?->name ?? 'Lembaga Sosial';
            Notification::send(
                $donation->mitra,
                new \App\Notifications\DonationCompletedNotification($lembagaName, $donation->title, $donation->quantity . ' ' . $donation->unit)
            );
        }

        return back()->with('success', 'Donasi dikonfirmasi sudah diterima.');
    }
}
