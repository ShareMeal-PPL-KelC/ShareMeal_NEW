<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\AutoDonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MitraDashboardController extends Controller
{
    protected function normalizePhone(?string $phone): ?string
    {
        return $phone === null ? null : preg_replace('/\D+/', '', $phone);
    }

    protected function businessContactOtpSessionKey(int $userId): string
    {
        return 'business_contact_otp.' . $userId;
    }

    public function mitraDashboard(): View
    {
        $userId = Auth::id() ?? User::where('role', 'mitra')->value('id');
        app(AutoDonationService::class)->processProducts($userId);

        $donationsCount = Donation::where('mitra_id', $userId)->count();

        $stats = (object) [
            'totalProducts' => Product::where('user_id', $userId)->count(),
            'activeFlashSale' => Product::where('user_id', $userId)->where('status', 'flash-sale')->count(),
            'expiredProducts' => Product::where('user_id', $userId)->where('status', 'expired')->count(),
            'pendingOrders' => \App\Models\Order::where('mitra_id', $userId)->where('status', 'pending')->count(),
            'totalRevenue' => \App\Models\Order::where('mitra_id', $userId)->where('status', 'completed')->sum('total_amount'),
            'foodSaved' => (int) \App\Models\OrderItem::whereHas('order', function ($query) use ($userId) {
                $query->where('mitra_id', $userId)->where('status', 'completed');
            })->sum('quantity'),
            'donationsGiven' => $donationsCount,
            'averageRating' => round(Review::where('mitra_id', $userId)->avg('rating') ?? 0, 1),
            'totalReviews' => Review::where('mitra_id', $userId)->count(),
        ];

        $recentOrders = \App\Models\Order::with(['customer', 'items.product'])
            ->where('mitra_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        $recentReviews = Review::with(['customer', 'order'])
            ->where('mitra_id', $userId)
            ->latest()
            ->take(3)
            ->get();

        $expiringItems = Product::where('user_id', $userId)
            ->whereIn('status', ['normal', 'flash-sale'])
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->orderBy('expires_at')
            ->take(5)
            ->get();

        // PBI #45: Add critical alert for near-expiry products
        $criticalAlerts = [];
        $urgentExpiringCount = $expiringItems->where('expires_at', '<', now()->addHours(4))->count();
        if ($urgentExpiringCount > 0) {
            $criticalAlerts[] = [
                'type' => 'warning',
                'title' => 'Peringatan Kedaluwarsa',
                'message' => "Perhatian: Ada $urgentExpiringCount produk yang akan kedaluwarsa dalam kurang dari 4 jam!",
                'link' => route('mitra.inventory'),
                'link_text' => 'Kelola Sekarang'
            ];
        }

        // Low stock warning (stock < 5 but > 0)
        $lowStockCount = Product::where('user_id', $userId)
            ->whereIn('status', ['normal', 'flash-sale'])
            ->where('stock', '>', 0)
            ->where('stock', '<', 5)
            ->count();
        if ($lowStockCount > 0) {
            $criticalAlerts[] = [
                'type' => 'warning',
                'title' => 'Stok Makanan Menipis',
                'message' => "Perhatian: Ada $lowStockCount produk dengan stok menipis (di bawah 5 porsi)!",
                'link' => route('mitra.inventory'),
                'link_text' => 'Kelola Stok'
            ];
        }
        session()->flash('critical_alerts', $criticalAlerts);

        return view('pages.mitra.dashboard', compact('stats', 'recentOrders', 'recentReviews', 'expiringItems'));
    }

    public function editMitraBusinessProfile(): View|RedirectResponse
    {
        $user = Auth::user()?->load('profile');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login untuk mengelola profil usaha.');
        }

        if ($user->role !== 'mitra') {
            return redirect()->route($user->role . '.dashboard')->with('error', 'Profil usaha hanya tersedia untuk mitra.');
        }

        return view('pages.mitra.profile', [
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    public function updateMitraBusinessProfile(Request $request): RedirectResponse
    {
        $user = Auth::user()?->load('profile');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login untuk mengelola profil usaha.');
        }

        if ($user->role !== 'mitra') {
            return redirect()->route($user->role . '.dashboard')->with('error', 'Profil usaha hanya tersedia untuk mitra.');
        }

        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string', 'max:100'],
            'business_address' => ['required', 'string', 'max:1000'],
            'business_contact' => ['required', 'string', 'regex:/^(08|62)\d{8,13}$/'],
            'opening_start' => ['required', 'date_format:H:i'],
            'opening_end' => ['required', 'date_format:H:i', 'after:opening_start'],
            'business_description' => ['required', 'string', 'max:1000'],
            'store_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'can_delivery' => ['nullable', 'boolean'],
            'delivery_fee' => ['nullable', 'required_if:can_delivery,1', 'integer', 'min:0'],
            'delivery_slot_limit' => ['nullable', 'required_if:can_delivery,1', 'integer', 'min:1'],
        ], [
            'business_name.required' => 'Nama usaha wajib diisi.',
            'business_type.required' => 'Kategori usaha wajib diisi.',
            'business_address.required' => 'Alamat usaha wajib diisi.',
            'business_contact.required' => 'Kontak usaha wajib diisi.',
            'business_contact.regex' => 'Kontak usaha harus berupa angka valid dengan awalan 08 atau 62 dan panjang 10-15 digit.',
            'opening_start.required' => 'Jam buka wajib diisi.',
            'opening_end.required' => 'Jam tutup wajib diisi.',
            'opening_end.after' => 'Jam tutup harus lebih akhir dari jam buka.',
            'business_description.required' => 'Deskripsi usaha wajib diisi.',
            'store_image.image' => 'Gambar toko harus berupa gambar.',
            'store_image.mimes' => 'Gambar toko harus berformat JPG, JPEG, atau PNG.',
            'store_image.max' => 'Ukuran gambar toko maksimal 2 MB.',
            'delivery_fee.required_if' => 'Biaya ongkir wajib diisi jika jasa kirim diaktifkan.',
            'delivery_slot_limit.required_if' => 'Limit slot wajib diisi jika jasa kirim diaktifkan.',
        ]);

        $openingHours = $data['opening_start'] . ' - ' . $data['opening_end'];
        $profile = $user->profile ?: $user->profile()->create([]);
        $businessContact = $this->normalizePhone($data['business_contact']);
        $currentBusinessContact = $this->normalizePhone($profile->business_contact);
        $businessContactChanged = $businessContact !== $currentBusinessContact;

        if ($businessContactChanged && $profile->business_contact_change_available_at && $profile->business_contact_change_available_at->isFuture()) {
            return back()
                ->withErrors(['business_contact' => 'Kontak usaha baru bisa diganti lagi pada ' . $profile->business_contact_change_available_at->format('H:i:s') . '.'])
                ->withInput();
        }

        $profileData = [
            'business_name' => $data['business_name'],
            'business_type' => $data['business_type'],
            'business_address' => $data['business_address'],
            'business_opening_hours' => $openingHours,
            'business_description' => $data['business_description'],
            'opening_hours' => $openingHours,
            'description' => $data['business_description'],
            'can_delivery' => (bool) ($data['can_delivery'] ?? false),
            'delivery_fee' => (int) ($data['delivery_fee'] ?? 0),
            'delivery_slot_limit' => (int) ($data['delivery_slot_limit'] ?? 10),
        ];

        if ($businessContactChanged) {
            $otp = (string) random_int(100000, 999999);
            $profileData['business_pending_contact'] = $businessContact;
            $profileData['business_contact_otp_hash'] = Hash::make($otp);
            $profileData['business_contact_otp_expires_at'] = now()->addMinutes(5);
            $request->session()->put($this->businessContactOtpSessionKey($user->id), $otp);
        } else {
            $profileData['business_contact'] = $businessContact;
        }

        if ($request->hasFile('store_image')) {
            $oldImage = $profile->avatar;
            $profileData['avatar'] = $request->file('store_image')->store('stores', 'public');

            if ($oldImage && !str_starts_with($oldImage, 'http://') && !str_starts_with($oldImage, 'https://')) {
                Storage::disk('public')->delete($oldImage);
            }
        }

        $user->update([
            'organization_name' => $data['business_name'],
        ]);

        $profile->update($profileData);

        if ($businessContactChanged) {
            return back()->with('success', 'Profil usaha berhasil diperbarui. Masukkan kode OTP untuk memverifikasi kontak usaha baru.');
        }

        return back()->with('success', 'Profil usaha berhasil diperbarui.');
    }

    public function verifyMitraBusinessContact(Request $request): RedirectResponse
    {
        $user = Auth::user()?->load('profile');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login untuk mengelola profil usaha.');
        }

        if ($user->role !== 'mitra') {
            return redirect()->route($user->role . '.dashboard')->with('error', 'Profil usaha hanya tersedia untuk mitra.');
        }

        $data = $request->validate([
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.digits' => 'Kode OTP harus 6 digit angka.',
        ]);

        $profile = $user->profile;

        if (!$profile || !$profile->business_pending_contact || !$profile->business_contact_otp_hash) {
            return back()->with('error', 'Tidak ada kontak usaha yang menunggu verifikasi.');
        }

        if (!$profile->business_contact_otp_expires_at || $profile->business_contact_otp_expires_at->isPast()) {
            $request->session()->forget($this->businessContactOtpSessionKey($user->id));
            return back()->with('error', 'Kode OTP sudah kedaluwarsa. Simpan ulang profil usaha untuk meminta kode baru.');
        }

        if (!Hash::check($data['otp'], $profile->business_contact_otp_hash)) {
            return back()->withErrors(['otp' => 'Kode OTP tidak sesuai.']);
        }

        $profile->update([
            'business_contact' => $profile->business_pending_contact,
            'business_pending_contact' => null,
            'business_contact_otp_hash' => null,
            'business_contact_otp_expires_at' => null,
            'business_contact_verified_at' => now(),
            'business_contact_change_available_at' => now()->addMinute(),
        ]);

        $request->session()->forget($this->businessContactOtpSessionKey($user->id));

        return back()->with('success', 'Kontak usaha berhasil diverifikasi.');
    }
}
