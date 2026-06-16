<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\User;
use App\Services\AutoDonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class MitraDonationController extends Controller
{
    public function mitraDonations(): View
    {
        $userId = Auth::id() ?? Session::get('sharemeal.current_user_id') ?? User::where('role', 'mitra')->value('id');
        app(AutoDonationService::class)->processProducts($userId);

        $donations = Donation::with(['lembaga', 'mitra.profile'])
            ->where('mitra_id', $userId)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->get();

        return view('pages.mitra.donations', compact('donations'));
    }

    public function mitraDonationStore(Request $request): RedirectResponse
    {
        if (!Auth::user()?->is_verified) {
            return back()->with('error', 'Akun Anda belum terverifikasi. Anda tidak dapat menambahkan donasi baru.');
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit' => ['required', 'string'],
            'expires_at' => ['required', 'date'],
            'pickup_start_time' => ['required', 'date_format:H:i'],
            'pickup_end_time' => ['required', 'date_format:H:i', 'after:pickup_start_time'],
            'description' => ['nullable', 'string'],
        ], [
            'pickup_start_time.required' => 'Jam mulai pengambilan wajib diisi.',
            'pickup_end_time.required' => 'Jam akhir pengambilan wajib diisi.',
            'pickup_end_time.after' => 'Jam akhir pengambilan harus lebih akhir dari jam mulai.',
        ]);

        $user = Auth::user()?->load('profile');
        $profile = $user->profile;

        $openingHours = $profile?->business_opening_hours ?? $profile?->opening_hours;
        if ($openingHours && str_contains($openingHours, ' - ')) {
            [$opStart, $opEnd] = explode(' - ', $openingHours, 2);

            if ($data['pickup_start_time'] < $opStart || $data['pickup_start_time'] > $opEnd) {
                return back()->withErrors(['pickup_start_time' => "Jam mulai pengambilan harus di dalam jam operasional ($openingHours)."])->withInput();
            }
            if ($data['pickup_end_time'] > $opEnd) {
                return back()->withErrors(['pickup_end_time' => "Jam akhir pengambilan harus di dalam jam operasional ($openingHours)."])->withInput();
            }
        }

        $userId = Auth::id() ?? Session::get('sharemeal.current_user_id');

        $donation = Donation::create([
            'mitra_id' => $userId,
            'title' => $data['title'],
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'expires_at' => $data['expires_at'],
            'pickup_start_time' => $data['pickup_start_time'],
            'pickup_end_time' => $data['pickup_end_time'],
            'description' => $data['description'],
            'status' => 'pending',
        ]);

        $lembagas = User::where('role', 'lembaga')->get();
        if ($lembagas->count() > 0) {
            $mitraName = Auth::user()->name ?? User::find($userId)?->name ?? 'Resto Mitra';
            Notification::send($lembagas, new \App\Notifications\DonationAvailableNotification($mitraName, $donation->title, $donation->quantity . ' ' . $donation->unit));
        }

        return back()->with('success', 'Donasi berhasil didaftarkan.');
    }

    public function mitraDonationPrepare(int $donationId): RedirectResponse
    {
        $userId = Auth::id() ?? Session::get('sharemeal.current_user_id');
        $donation = Donation::where('mitra_id', $userId)->findOrFail($donationId);

        if ($donation->status !== 'claimed') {
            return back()->with('error', 'Hanya donasi yang sudah diklaim yang bisa disiapkan.');
        }

        $donation->update([
            'status' => 'prepared',
            'tracking_status' => 'prepared',
        ]);

        if ($donation->lembaga) {
            $mitraName = Auth::user()->name ?? User::find($userId)?->name ?? 'Resto Mitra';
            Notification::send(
                $donation->lembaga,
                new \App\Notifications\DonationPreparedNotification($mitraName, $donation->title, $donation->quantity . ' ' . $donation->unit)
            );
        }

        return back()->with('success', 'Donasi berhasil ditandai sebagai siap diambil.');
    }

    public function mitraDonationComplete(int $donationId): RedirectResponse
    {
        $userId = Auth::id() ?? Session::get('sharemeal.current_user_id');
        $donation = Donation::where('mitra_id', $userId)->findOrFail($donationId);

        if (!in_array($donation->status, ['claimed', 'prepared'])) {
            return back()->with('error', 'Hanya donasi yang sudah diklaim atau disiapkan yang bisa diselesaikan.');
        }

        $donation->update([
            'status' => 'completed',
            'delivered_at' => now(),
            'tracking_status' => 'delivered',
        ]);

        if ($donation->lembaga) {
            $mitraName = Auth::user()->name ?? User::find($userId)->name ?? 'Resto Mitra';
            Notification::send(
                $donation->lembaga,
                new \App\Notifications\DonationHandedOverNotification($mitraName, $donation->title, $donation->quantity . ' ' . $donation->unit)
            );
        }

        return back()->with('success', 'Donasi dikonfirmasi telah diserahkan.');
    }

    public function mitraDonationCancel(int $donationId): RedirectResponse
    {
        $userId = Auth::id() ?? Session::get('sharemeal.current_user_id');
        $donation = Donation::where('mitra_id', $userId)->findOrFail($donationId);

        if ($donation->status === 'completed') {
            return back()->with('error', 'Donasi yang sudah selesai tidak bisa dibatalkan.');
        }

        if (in_array($donation->status, ['claimed', 'prepared'])) {
            if ($donation->lembaga) {
                $mitraName = Auth::user()->name ?? User::find($userId)->name ?? 'Resto Mitra';
                Notification::send(
                    $donation->lembaga,
                    new \App\Notifications\DonationCancelledNotification($mitraName, $donation->title, $donation->quantity . ' ' . $donation->unit)
                );
            }
        }

        $donation->delete();

        return back()->with('success', 'Donasi berhasil dibatalkan/dihapus.');
    }
}
