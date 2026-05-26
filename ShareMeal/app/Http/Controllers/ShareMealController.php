<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Donation;
use App\Models\Review;
use App\Services\AutoDonationService;
use App\Support\ShareMealState;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ShareMealController extends Controller
{
    protected function currentUser(): array
    {
        return ShareMealState::currentUser();
    }

    protected function dashboardNavigation(string $type): array
    {
        return match ($type) {
            'mitra' => [
                ['label' => 'Dashboard', 'route' => 'mitra.dashboard', 'icon' => 'layout-dashboard'],
                ['label' => 'Inventaris', 'route' => 'mitra.inventory', 'icon' => 'package'],
                ['label' => 'Pesanan', 'route' => 'mitra.orders', 'icon' => 'shopping-cart'],
                ['label' => 'Donasi', 'route' => 'mitra.donations', 'icon' => 'heart'],
            ],
            'consumer' => [
                ['label' => 'Dashboard', 'route' => 'consumer.dashboard', 'icon' => 'layout-dashboard'],
                ['label' => 'Cari Makanan', 'route' => 'consumer.search', 'icon' => 'search'],
                ['label' => 'Riwayat', 'route' => 'consumer.history', 'icon' => 'history'],
                ['label' => 'Edukasi', 'route' => 'consumer.education', 'icon' => 'book-open'],
            ],
            'lembaga' => [
                ['label' => 'Dashboard', 'route' => 'lembaga.dashboard', 'icon' => 'layout-dashboard'],
                ['label' => 'Donasi', 'route' => 'lembaga.donations', 'icon' => 'heart'],
            ],
            'admin' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'layout-dashboard'],
                ['label' => 'Verifikasi', 'route' => 'admin.verification', 'icon' => 'shield'],
                ['label' => 'Kelola User', 'route' => 'admin.users', 'icon' => 'users'],
                ['label' => 'Transaksi', 'route' => 'admin.transactions', 'icon' => 'shopping-cart'],
                ['label' => 'Laporan', 'route' => 'admin.reports', 'icon' => 'bar-chart'],
                ['label' => 'Edukasi', 'route' => 'admin.education', 'icon' => 'book-open'],
            ],
            default => [],
        };
    }

    protected function dashboardData(string $type, string $title, string $subtitle): array
    {
        $user = $this->currentUser();

        return [
            'user' => $user,
            'shell' => [
                'type' => $type,
                'title' => $title,
                'subtitle' => $subtitle,
                'userName' => (isset($user['type']) && $user['type'] === $type) ? $user['name'] : match ($type) {
                    'mitra' => 'Toko Roti Barokah',
                    'consumer' => 'Budi Santoso',
                    'lembaga' => 'Yayasan Peduli Anak',
                    'admin' => 'Admin ShareMeal',
                    default => 'ShareMeal',
                },
                'navigation' => $this->dashboardNavigation($type),
            ],
        ];
    }

    protected function parseLocalDateTime(string $value): Carbon
    {
        return Carbon::createFromFormat('Y-m-d\TH:i', $value, config('app.timezone'));
    }

    public function landing(): View
    {
        Auth::logout();
        ShareMealState::logout();

        return view('pages.landing');
    }

    public function login(): View
    {
        return view('pages.auth.login');
    }

    public function doLogin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'user_type' => ['required', 'in:consumer,mitra,lembaga,admin'],
        ]);

        $user = User::query()
            ->where('email', $data['email'])
            ->where('role', $data['user_type'])
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return back()->with('error', 'Email, password, atau tipe pengguna tidak sesuai.');
        }

        // Verification Guard for Mitra Only
        if ($user->role === 'mitra' && !$user->is_verified) {
            return back()->with('error', 'Akun Anda sedang dalam proses verifikasi oleh tim ShareMeal. Mohon tunggu email konfirmasi atau hubungi admin.');
        }

        Auth::login($user);
        ShareMealState::login($user->id);

        return redirect()->route($data['user_type'] . '.dashboard')->with('success', 'Login berhasil.');
    }

    public function register(): View
    {
        return view('pages.auth.register');
    }

    public function doRegister(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6', 'confirmed'],
            'user_type' => ['required', 'in:consumer,mitra,lembaga'],
            'terms' => ['accepted'],
        ];

        if ($request->user_type === 'mitra') {
            $rules['document_ktp_mitra'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
            $rules['document_siup_mitra'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
            $rules['document_nib_mitra'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
            $rules['document_halal_mitra'] = ['nullable', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
        } elseif ($request->user_type === 'lembaga') {
            $rules['document_legalitas_lembaga'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
            $rules['document_izin_lembaga'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
            $rules['document_identitas_lembaga'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
        }

        $data = $request->validate($rules);

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['user_type'],
            'status' => 'active',
            'phone' => null,
            'organization_name' => in_array($data['user_type'], ['mitra', 'lembaga'], true) ? $data['name'] : null,
            'joined_at' => now()->toDateString(),
            'transactions_count' => 0,
            'warnings_count' => 0,
            'is_verified' => false,
        ];

        // Process file uploads
        if ($data['user_type'] === 'mitra') {
            $userData['document_ktp'] = $request->file('document_ktp_mitra')->store('documents', 'public');
            $userData['document_siup'] = $request->file('document_siup_mitra')->store('documents', 'public');
            $userData['document_nib'] = $request->file('document_nib_mitra')->store('documents', 'public');
            if ($request->hasFile('document_halal_mitra')) {
                $userData['document_halal'] = $request->file('document_halal_mitra')->store('documents', 'public');
            }
        } elseif ($data['user_type'] === 'lembaga') {
            $userData['document_legalitas'] = $request->file('document_legalitas_lembaga')->store('documents', 'public');
            $userData['document_izin'] = $request->file('document_izin_lembaga')->store('documents', 'public');
            $userData['document_identitas'] = $request->file('document_identitas_lembaga')->store('documents', 'public');
        }

        User::query()->create($userData);

        return redirect()->route('login')->with('success', 'Registrasi berhasil. Akun Anda sedang dalam proses verifikasi oleh admin.');
    }

    public function logout(): RedirectResponse
    {
        \Illuminate\Support\Facades\Auth::logout();
        ShareMealState::logout();
        return redirect()->route('login')->with('success', 'Anda telah keluar.');
    }

    public function markNotificationsRead(): RedirectResponse
    {
        if (Auth::check()) {
            Auth::user()->unreadNotifications->markAsRead();
        }
        return back();
    }

    public function editProfile(): View|RedirectResponse
    {
        $user = Auth::user()?->load('profile');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login untuk mengelola profil.');
        }

        return view('pages.profile.edit', [
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    protected function normalizePhone(?string $phone): ?string
    {
        return $phone === null ? null : preg_replace('/\D+/', '', $phone);
    }

    protected function profilePhoneOtpSessionKey(int $userId): string
    {
        return 'profile_phone_otp.' . $userId;
    }

    protected function businessContactOtpSessionKey(int $userId): string
    {
        return 'business_contact_otp.' . $userId;
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user()?->load('profile');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login untuk mengelola profil.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'phone' => ['required', 'string', 'regex:/^(08|62)\d{8,13}$/'],
            'address' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'name.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Nomor telepon harus berupa angka valid dengan awalan 08 atau 62 dan panjang 10-15 digit.',
            'avatar.image' => 'Foto profil harus berupa gambar.',
            'avatar.mimes' => 'Foto profil harus berformat JPG, JPEG, atau PNG.',
            'avatar.max' => 'Ukuran foto profil maksimal 2 MB.',
        ]);

        $phone = $this->normalizePhone($data['phone'] ?? null);
        $profile = $user->profile ?: $user->profile()->create([]);
        $currentPhone = $this->normalizePhone($profile->phone ?? $user->phone);
        $phoneChanged = $phone !== $currentPhone;

        if ($phoneChanged && $profile->phone_change_available_at && $profile->phone_change_available_at->isFuture()) {
            return back()
                ->withErrors(['phone' => 'Nomor telepon baru bisa diganti lagi pada ' . $profile->phone_change_available_at->format('H:i:s') . '.'])
                ->withInput();
        }

        $profileData = [
            'address' => $data['address'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            $oldAvatar = $user->profile?->avatar;
            $profileData['avatar'] = $request->file('avatar')->store('avatars', 'public');

            if ($oldAvatar && !str_starts_with($oldAvatar, 'http://') && !str_starts_with($oldAvatar, 'https://')) {
                Storage::disk('public')->delete($oldAvatar);
            }
        }

        if ($phoneChanged) {
            $otp = (string) random_int(100000, 999999);
            $profileData['pending_phone'] = $phone;
            $profileData['phone_otp_hash'] = Hash::make($otp);
            $profileData['phone_otp_expires_at'] = now()->addMinutes(5);
            $request->session()->put($this->profilePhoneOtpSessionKey($user->id), $otp);
        }

        $user->update(['name' => $data['name']]);
        $profile->update($profileData);

        ShareMealState::login($user->id);

        if ($phoneChanged) {
            return back()
                ->with('success', 'Profil berhasil diperbarui. Masukkan kode OTP untuk memverifikasi nomor telepon baru.');
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function verifyProfilePhone(Request $request): RedirectResponse
    {
        $user = Auth::user()?->load('profile');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login untuk mengelola profil.');
        }

        $data = $request->validate([
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.digits' => 'Kode OTP harus 6 digit angka.',
        ]);

        $profile = $user->profile;

        if (!$profile || !$profile->pending_phone || !$profile->phone_otp_hash) {
            return back()->with('error', 'Tidak ada nomor telepon yang menunggu verifikasi.');
        }

        if (!$profile->phone_otp_expires_at || $profile->phone_otp_expires_at->isPast()) {
            $request->session()->forget($this->profilePhoneOtpSessionKey($user->id));
            return back()->with('error', 'Kode OTP sudah kedaluwarsa. Simpan ulang profil untuk meminta kode baru.');
        }

        if (!Hash::check($data['otp'], $profile->phone_otp_hash)) {
            return back()->withErrors(['otp' => 'Kode OTP tidak sesuai.']);
        }

        $phone = $profile->pending_phone;

        $user->update(['phone' => $phone]);
        $profile->update([
            'phone' => $phone,
            'pending_phone' => null,
            'phone_otp_hash' => null,
            'phone_otp_expires_at' => null,
            'phone_verified_at' => now(),
            'phone_change_available_at' => now()->addMinute(),
        ]);

        $request->session()->forget($this->profilePhoneOtpSessionKey($user->id));
        ShareMealState::login($user->id);

        return back()->with('success', 'Nomor telepon berhasil diverifikasi.');
    }

    public function uploadBusinessDocument(Request $request): RedirectResponse
    {
        $request->validate([
            'document_ktp' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'document_siup' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'document_nib' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'document_halal' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        $userId = \Illuminate\Support\Facades\Session::get('sharemeal.current_user_id');
        $user = User::query()->find($userId);

        if (!$user) {
            return back()->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        $updates = [];
        foreach (['document_ktp', 'document_siup', 'document_nib', 'document_halal'] as $field) {
            if ($request->hasFile($field)) {
                $updates[$field] = $request->file($field)->store('documents', 'public');
            }
        }

        if (!empty($updates)) {
            // Reset verification status when re-uploading
            $updates['is_verified'] = false;
            $updates['verification_rejection_reason'] = null;
            $updates['status'] = 'active';

            $user->update($updates);
            return back()->with('success', 'Semua dokumen berhasil diunggah dan sedang menunggu verifikasi ulang.');
        }

        return back()->with('error', 'Gagal mengunggah dokumen.');
    }

    public function consumerDashboard(): View
    {
        $userModel = User::find($this->currentUser()['id']);
        $notifications = $userModel ? $userModel->unreadNotifications : collect();

        $stores = ShareMealState::get('stores');
        $flashSales = collect($stores)->flatMap(function ($store) {
            return collect($store['deals'])->map(function ($deal) use ($store) {
                return [
                    'id' => $deal['id'],
                    'store_id' => $store['id'],
                    'store' => $store['name'],
                    'distance' => $store['distance'],
                    'item' => $deal['item'],
                    'original_price' => $deal['original_price'],
                    'discount_price' => $deal['discount_price'],
                    'discount' => max(0, 100 - (int) round(($deal['discount_price'] / $deal['original_price']) * 100)),
                    'stock' => $deal['stock'],
                    'expires_in' => $deal['expires_in'],
                    'rating' => $store['rating'],
                    'image' => $store['image'],
                ];
            });
        })->take(3)->values();

        return view('pages.consumer.dashboard', $this->dashboardData('consumer', 'Dashboard Konsumen', 'Hemat uang dan selamatkan lingkungan') + [
            'stats' => ['saved_meals' => 24, 'money_saved' => 350000, 'co2_reduced' => 15.5, 'favorite_stores' => 8],
            'flashSales' => $flashSales,
            'notifications' => $notifications,
            'favoriteStores' => collect($stores)->map(fn ($store) => [
                'id' => $store['id'],
                'name' => $store['name'],
                'category' => $store['category'],
                'distance' => $store['distance'],
                'rating' => $store['rating'],
                'active_deals' => count($store['deals']),
            ]),
        ]);
    }

    public function consumerSearch(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $filters = array_filter((array) $request->query('filters', []));
        $stores = collect(ShareMealState::get('stores'))->filter(function ($store) use ($search, $filters) {
            $matchesSearch = $search === '' || str_contains(strtolower($store['name']), strtolower($search)) || str_contains(strtolower($store['category']), strtolower($search));
            $matchesFilters = empty($filters) || collect($filters)->every(fn ($filter) => in_array($filter, $store['tags'], true));
            return $matchesSearch && $matchesFilters;
        })->values();

        return view('pages.consumer.search', $this->dashboardData('consumer', 'Cari Makanan Terdekat', 'Location-Based Search & Filter Kategori') + [
            'stores' => $stores,
            'search' => $search,
            'selectedFilters' => $filters,
            'filters' => [
                ['id' => 'halal', 'label' => 'Halal', 'icon' => '阜'],
                ['id' => 'vegan', 'label' => 'Vegan', 'icon' => '験'],
                ['id' => 'bakery', 'label' => 'Bakery', 'icon' => '込'],
                ['id' => 'healthy', 'label' => 'Healthy', 'icon' => '･'],
                ['id' => 'indonesian', 'label' => 'Indonesian', 'icon' => '骨'],
            ],
        ]);
    }

    public function consumerBook(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'store_id' => ['required', 'integer'],
            'deal_id' => ['required', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $bookingId = ShareMealState::createBooking((int) $data['store_id'], (int) $data['deal_id'], (int) ($data['quantity'] ?? 1), 'Budi Santoso');
        if (!$bookingId) {
            return back()->with('error', 'Booking gagal. Stok tidak tersedia.');
        }

        return redirect()->route('consumer.checkout', ['bookingId' => $bookingId])->with('success', 'Booking berhasil dibuat.');
    }

    public function consumerCheckout(Request $request): View
    {
        $bookingId = (string) $request->query('bookingId', '');
        $bookings = collect(ShareMealState::get('bookings'));
        $booking = $bookings->firstWhere('id', $bookingId);
        $store = collect(ShareMealState::get('stores'))->firstWhere('id', data_get($booking, 'store_id'));

        return view('pages.consumer.checkout', $this->dashboardData('consumer', 'Checkout Pembayaran', 'Selesaikan pembayaran untuk konfirmasi pesanan') + [
            'booking' => $booking,
            'store' => $store,
            'paymentMethods' => [
                ['id' => 'qris', 'name' => 'QRIS', 'description' => 'Scan QR untuk bayar'],
                ['id' => 'gopay', 'name' => 'GoPay', 'description' => 'E-wallet GoPay'],
                ['id' => 'ovo', 'name' => 'OVO', 'description' => 'E-wallet OVO'],
                ['id' => 'dana', 'name' => 'DANA', 'description' => 'E-wallet DANA'],
                ['id' => 'bca', 'name' => 'BCA Virtual Account', 'description' => 'Transfer bank BCA'],
                ['id' => 'mandiri', 'name' => 'Mandiri Virtual Account', 'description' => 'Transfer bank Mandiri'],
            ],
            'selectedMethod' => $request->query('method', 'qris'),
            'paymentReference' => 'PAY-' . strtoupper(substr($bookingId, -8)),
        ]);
    }

    public function consumerConfirmPayment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'booking_id' => ['required'],
        ]);
        ShareMealState::completePayment($data['booking_id']);
        return redirect()->route('consumer.history')->with('success', 'Pembayaran berhasil dikonfirmasi.');
    }

    public function consumerHistory(): View
    {
        $transactions = collect(ShareMealState::get('transactions'));
        $stats = [
            'total_transactions' => $transactions->count(),
            'total_savings' => $transactions->sum('discount'),
            'average_rating' => round((float) ($transactions->where('rating', '>', 0)->avg('rating') ?? 0), 1),
        ];

        return view('pages.consumer.history', $this->dashboardData('consumer', 'Riwayat Transaksi', 'Manajemen histori & bukti bayar') + [
            'transactions' => $transactions,
            'stats' => $stats,
        ]);
    }

    public function consumerReview(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'transaction_id' => ['required'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['nullable', 'string'],
        ]);

        ShareMealState::submitReview($data['transaction_id'], (int) $data['rating'], (string) ($data['review'] ?? ''));
        return back()->with('success', 'Review berhasil dikirim.');
    }

    public function consumerEducation(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $category = (string) $request->query('category', 'Semua');
        $articles = collect(ShareMealState::get('articles'))
            ->where('status', 'Published')
            ->filter(function ($article) use ($search, $category) {
                $matchesSearch = $search === '' || str_contains(strtolower($article['title']), strtolower($search)) || str_contains(strtolower($article['content']), strtolower($search));
                $matchesCategory = $category === 'Semua' || $article['category'] === $category;
                return $matchesSearch && $matchesCategory;
            })->values();

        return view('pages.consumer.education', $this->dashboardData('consumer', 'Edukasi Lingkungan', 'Tingkatkan pengetahuanmu tentang dampak sampah makanan.') + [
            'articles' => $articles,
            'search' => $search,
            'category' => $category,
            'categories' => ['Semua', 'Tips', 'Artikel', 'Panduan', 'Edukasi'],
        ]);
    }

    public function mitraDashboard(): View
    {
        $userId = Auth::id() ?? \App\Models\User::where('role', 'mitra')->value('id');
        app(AutoDonationService::class)->processProducts($userId);

        $products = Product::where('user_id', $userId)->get();
        $donationsCount = Donation::where('mitra_id', $userId)->count();
        $orders = \App\Models\Order::where('mitra_id', $userId)->get();
        $reviews = Review::where('mitra_id', $userId)->get();

        $stats = (object) [
            'totalProducts' => $products->count(),
            'activeFlashSale' => $products->where('status', 'flash-sale')->count(),
            'expiredProducts' => $products->where('status', 'expired')->count(),
            'pendingOrders' => $orders->where('status', 'pending')->count(),
            'totalRevenue' => $orders->where('status', 'completed')->sum('total_amount'),
            'foodSaved' => \App\Models\OrderItem::whereIn('order_id', $orders->where('status', 'completed')->pluck('id'))->sum('quantity'),
            'donationsGiven' => $donationsCount,
            'averageRating' => round($reviews->avg('rating') ?? 0, 1),
            'totalReviews' => $reviews->count(),
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

        return view('pages.mitra.dashboard', compact('stats', 'recentOrders', 'expiringItems', 'recentReviews'));
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

    public function mitraInventory(): View
    {
        $userId = Auth::id() ?? \App\Models\User::where('role', 'mitra')->value('id');
        app(AutoDonationService::class)->processProducts($userId);

        $products = Product::with(['user' => function($q) {
                $q->withAvg('reviewsAsMitra', 'rating')
                  ->withCount('reviewsAsMitra')
                  ->with('profile');
            }])
            ->where('user_id', $userId)
            ->get()
            ->map(function (Product $product) {
                $expiresAt = $product->expires_at?->copy()->timezone(config('app.timezone'));

                $product->expires_at_input = $expiresAt?->format('Y-m-d\TH:i');
                $product->expires_at_display = $expiresAt?->format('d/m/Y H:i');
                $product->pickup_start_time_input = $product->pickup_start_time ? substr((string) $product->pickup_start_time, 0, 5) : '';
                $product->pickup_end_time_input = $product->pickup_end_time ? substr((string) $product->pickup_end_time, 0, 5) : '';

                return $product;
            });

        return view('pages.mitra.inventory', compact('products'));
    }

    public function mitraInventoryStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'discount_price' => ['nullable', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'expires_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'pickup_start_time' => ['required', 'date_format:H:i'],
            'pickup_end_time' => ['required', 'date_format:H:i', 'after:pickup_start_time'],
            'status' => ['required', 'string', 'in:normal,flash-sale,donation,expired'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
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

        $expiresAt = $this->parseLocalDateTime($data['expires_at']);

        $product = Product::create([
            'user_id' => Auth::id() ?? \App\Models\User::where('role', 'mitra')->first()?->id,
            'name' => $data['name'],
            'category' => $data['category'],
            'price' => $data['price'],
            'discount_price' => $data['discount_price'] ?? 0,
            'stock' => $data['stock'],
            'expires_at' => $expiresAt,
            'pickup_start_time' => $data['pickup_start_time'],
            'pickup_end_time' => $data['pickup_end_time'],
            'status' => $data['status'],
            'image' => $request->hasFile('image') ? $request->file('image')->store('products', 'public') : 'https://images.unsplash.com/photo-1666114170628-b34b0dcc21aa?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxiYWtlcnklMjBicmVhZCUyMHBhc3RyeSUyMHNob3B8ZW58MXx8fHwxNzc0OTc0Mzg5fDA&ixlib=rb-4.1.0&q=80&w=1080',
        ]);

        if ($product->status === 'flash-sale') {
            $mitra = \App\Models\User::find($product->user_id);
            if ($mitra) {
                // Because favorite stores logic is frontend-only (localStorage), we notify all consumers as a mock demo
                $consumers = \App\Models\User::where('role', 'consumer')->get();
                if ($consumers->count() > 0) {
                    \Illuminate\Support\Facades\Notification::send($consumers, new \App\Notifications\FlashSaleNotification($mitra->name, $product->name, $product->discount_price));
                }
            }
        }

        return back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function mitraInventoryUpdate(Request $request, int $productId): RedirectResponse
    {
        $userId = Auth::id() ?? \App\Models\User::where('role', 'mitra')->value('id');
        $product = Product::where('user_id', $userId)->findOrFail($productId);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'discount_price' => ['nullable', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'expires_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'pickup_start_time' => ['required', 'date_format:H:i'],
            'pickup_end_time' => ['required', 'date_format:H:i', 'after:pickup_start_time'],
            'status' => ['required', 'string', 'in:normal,flash-sale,donation,expired'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
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

        $wasNotFlashSale = $product->getOriginal('status') !== 'flash-sale';
        $expiresAt = $this->parseLocalDateTime($data['expires_at']);

        $product->update([
            'name' => $data['name'],
            'category' => $data['category'],
            'price' => $data['price'],
            'discount_price' => $data['discount_price'] ?? 0,
            'stock' => $data['stock'],
            'expires_at' => $expiresAt,
            'pickup_start_time' => $data['pickup_start_time'],
            'pickup_end_time' => $data['pickup_end_time'],
            'status' => $data['status'],
        ]);

        if ($request->hasFile('image')) {
            $product->update(['image' => $request->file('image')->store('products', 'public')]);
        }

        if ($product->status === 'flash-sale' && $wasNotFlashSale) {
            $mitra = \App\Models\User::find($product->user_id);
            if ($mitra) {
                $consumers = \App\Models\User::where('role', 'consumer')->get();
                if ($consumers->count() > 0) {
                    \Illuminate\Support\Facades\Notification::send($consumers, new \App\Notifications\FlashSaleNotification($mitra->name, $product->name, $product->discount_price));
                }
            }
        }

        return back()->with('success', 'Informasi produk berhasil diperbarui.');
    }

    public function mitraInventoryFlashSale(int $productId): RedirectResponse
    {
        $userId = Auth::id() ?? \App\Models\User::where('role', 'mitra')->value('id');
        app(AutoDonationService::class)->processProducts($userId);

        $product = Product::where('user_id', $userId)->findOrFail($productId);

        if ($product->status === 'expired' || $product->expires_at->isPast()) {
            return back()->with('error', 'Produk sudah kedaluwarsa dan tidak bisa dijadikan flash sale.');
        }

        $product->update([
            'status' => 'flash-sale',
            'discount_price' => floor($product->price * 0.7), // Example 30% discount
        ]);

        $mitra = \App\Models\User::find($product->user_id);
        if ($mitra) {
            $consumers = \App\Models\User::where('role', 'consumer')->get();
            if ($consumers->count() > 0) {
                \Illuminate\Support\Facades\Notification::send($consumers, new \App\Notifications\FlashSaleNotification($mitra->name, $product->name, $product->discount_price));
            }
        }

        return back()->with('success', 'Flash sale diaktifkan.');
    }

    public function mitraInventoryToggleDonation(int $productId): RedirectResponse
    {
        $userId = Auth::id() ?? \App\Models\User::where('role', 'mitra')->value('id');
        $product = Product::where('user_id', $userId)->findOrFail($productId);

        $product->update([
            'donatable' => !$product->donatable,
        ]);

        $status = $product->donatable ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', 'Donasi otomatis untuk "' . $product->name . '" berhasil ' . $status . '.');
    }

    public function mitraDonationStore(Request $request): RedirectResponse
    {
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

        $userId = Auth::id() ?? \Illuminate\Support\Facades\Session::get('sharemeal.current_user_id');

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

        $lembagas = \App\Models\User::where('role', 'lembaga')->get();
        if ($lembagas->count() > 0) {
            $mitraName = Auth::user()->name ?? \App\Models\User::find($userId)?->name ?? 'Resto Mitra';
            \Illuminate\Support\Facades\Notification::send($lembagas, new \App\Notifications\DonationAvailableNotification($mitraName, $donation->title, $donation->quantity . ' ' . $donation->unit));
        }

        return back()->with('success', 'Donasi berhasil didaftarkan.');
    }

    public function mitraDonations(): View
    {
        $userId = Auth::id() ?? \Illuminate\Support\Facades\Session::get('sharemeal.current_user_id') ?? \App\Models\User::where('role', 'mitra')->value('id');
        app(AutoDonationService::class)->processProducts($userId);
        
        $donations = Donation::with('lembaga')
            ->where('mitra_id', $userId)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->get();

        return view('pages.mitra.donations', compact('donations'));
    }

    public function mitraInventoryDelete(int $productId): RedirectResponse
    {
        $userId = Auth::id() ?? \App\Models\User::where('role', 'mitra')->value('id');
        $product = Product::where('user_id', $userId)->findOrFail($productId);
        $product->delete();

        return back()->with('success', 'Produk dihapus.');
    }

    public function mitraOrders(): View
    {
        $userId = Auth::id() ?? \App\Models\User::where('role', 'mitra')->value('id');
        $orders = \App\Models\Order::with(['customer', 'items.product', 'reviewRelation'])
            ->where('mitra_id', $userId)
            ->latest()
            ->get();

        return view('pages.mitra.orders', compact('orders'));
    }

    public function mitraReviews(): View
    {
        $userId = Auth::id() ?? \App\Models\User::where('role', 'mitra')->value('id');
        $reviews = Review::with(['customer', 'order.items.product'])
            ->where('mitra_id', $userId)
            ->latest()
            ->paginate(10);

        return view('pages.mitra.reviews', compact('reviews'));
    }

    public function updateOrderStatus(Request $request, int $orderId): JsonResponse|RedirectResponse
    {
        $userId = Auth::id() ?? \App\Models\User::where('role', 'mitra')->value('id');
        $order = \App\Models\Order::where('mitra_id', $userId)->findOrFail($orderId);

        $request->validate([
            'status' => ['required', 'in:pending,ready,shipping,completed,cancelled'],
        ]);

        $order->update(['status' => $request->status]);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'status' => $order->status,
                'completed_time' => $order->completedTime,
            ]);
        }

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function mitraOrdersConfirm(int $orderId): JsonResponse|RedirectResponse
    {
        $userId = Auth::id() ?? \App\Models\User::where('role', 'mitra')->value('id');
        $order = \App\Models\Order::where('mitra_id', $userId)->findOrFail($orderId);
        $order->update(['status' => 'completed']);

        // Send notification to consumer (Diva's PBI #43)
        if ($order->customer) {
            $order->customer->notify(new \App\Notifications\OrderStatusUpdated($order));
        }

        if (request()->wantsJson() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'completed_time' => $order->completedTime,
            ]);
        }
        return back()->with('success', 'Pesanan dikonfirmasi sebagai sudah diambil.');
    }

    public function lembagaDashboard(): View
    {
        $userId = \Illuminate\Support\Facades\Session::get('sharemeal.current_user_id');
        $userObj = User::query()->find($userId);
        $donations = ShareMealState::get('donations');

        return view('pages.lembaga.dashboard', $this->dashboardData('lembaga', 'Dashboard Lembaga Sosial', 'Kelola penerimaan donasi makanan') + [
            'stats' => (object) ['totalDonations' => 156, 'activeDonations' => 8, 'beneficiaries' => 120, 'thisMonth' => 45],
            'donations' => $donations,
            'availableDonations' => collect($donations)->where('status', 'available')->all(),
            'recentDonations' => collect($donations)->whereIn('status', ['claimed', 'completed'])->sortByDesc('claimed_at')->take(5)->all(),
            'userObj' => $userObj,
        ]);
    }

    public function lembagaDonations(): View
    {
        return view('pages.lembaga.donations', $this->dashboardData('lembaga', 'Kelola Donasi', 'Klaim & tracking donasi makanan') + [
            'donations' => ShareMealState::get('donations'),
            'activeTab' => request('tab', 'available'),
        ]);
    }

    public function lembagaClaimDonation(string $donationId): RedirectResponse
    {
        $userId = Auth::id() ?? \Illuminate\Support\Facades\Session::get('sharemeal.current_user_id');
        
        $donation = \App\Models\Donation::with('mitra')->findOrFail($donationId);
        
        if ($donation->status !== 'pending' || ($donation->expires_at && \Carbon\Carbon::parse($donation->expires_at)->isPast())) {
            return back()->with('error', 'Donasi sudah tidak tersedia atau telah kedaluwarsa.');
        }
        
        $donation->update([
            'status' => 'claimed',
            'claimed_at' => now(),
            'tracking_status' => 'confirmed',
            'lembaga_id' => $userId
        ]);
        
        // Notify the Mitra that their donation was claimed
        if ($donation->mitra) {
            $lembagaName = Auth::user()->name ?? \App\Models\User::find($userId)?->name ?? 'Lembaga Sosial';
            \Illuminate\Support\Facades\Notification::send(
                $donation->mitra, 
                new \App\Notifications\DonationClaimedNotification($lembagaName, $donation->title, $donation->quantity . ' ' . $donation->unit)
            );
        }
        
        return back()->with('success', 'Donasi berhasil diklaim.');
    }

    public function lembagaCompleteDonation(string $donationId): RedirectResponse
    {
        ShareMealState::completeDonation($donationId);
        return back()->with('success', 'Donasi dikonfirmasi sudah diterima.');
    }

    public function adminDashboard(): View
    {
        $activities = [
            [
                'title' => 'Toko Roti Sejahtera',
                'description' => 'Menunggu verifikasi dokumen',
                'time' => '5 menit lalu',
                'type' => 'warning',
                'icon' => 'clock'
            ],
            [
                'title' => 'Budi Santoso',
                'description' => 'Registrasi akun konsumen baru',
                'time' => '10 menit lalu',
                'type' => 'success',
                'icon' => 'check-circle'
            ],
            [
                'title' => 'Yayasan Harapan Bangsa',
                'description' => 'Menunggu verifikasi legalitas',
                'time' => '30 menit lalu',
                'type' => 'warning',
                'icon' => 'clock'
            ],
            [
                'title' => 'Sistem',
                'description' => 'Laporan penyalahgunaan dari Toko ABC',
                'time' => '1 jam lalu',
                'type' => 'danger',
                'icon' => 'alert-circle'
            ],
            [
                'title' => 'Warung Makan Ibu Rina',
                'description' => 'Dokumen disetujui',
                'time' => '2 jam lalu',
                'type' => 'success',
                'icon' => 'check-circle'
            ],
        ];

        return view('pages.admin.dashboard', $this->dashboardData('admin', 'Dashboard Admin', 'Kelola sistem, verifikasi akun, dan moderasi platform') + [
            'applications' => ShareMealState::get('applications'),
            'users' => ShareMealState::get('users'),
            'activities' => $activities,
            'stats' => [
                'total_user' => 1250,
                'pending' => 15,
                'mitra_aktif' => 142,
                'lembaga_aktif' => 38,
                'transaksi' => 5420,
                'makanan_saved' => '12.5k',
                'co2_dikurangi' => '31250',
                'gmv_platform' => 'Rp 189.7M',
            ]
        ]);
    }

    public function adminVerification(): View
    {
        return view('pages.admin.verification', $this->dashboardData('admin', 'Verifikasi Mitra & Lembaga Sosial', 'Sistem approval & verifikasi admin') + [
            'applications' => ShareMealState::get('applications'),
            'activeTab' => request('tab', 'pending'),
        ]);
    }

    public function adminApproveApplication(int $applicationId): RedirectResponse
    {
        ShareMealState::approveApplication($applicationId);
        return back()->with('success', 'Aplikasi disetujui.');
    }

    public function adminRejectApplication(Request $request, int $applicationId): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required']]);
        ShareMealState::rejectApplication($applicationId, $data['reason']);
        return back()->with('success', 'Aplikasi ditolak.');
    }

    public function adminUsers(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $type = (string) $request->query('type', 'all');
        $status = (string) $request->query('status', 'all');
        $users = collect(ShareMealState::get('users'))->filter(function ($user) use ($search, $type, $status) {
            $matchesSearch = $search === '' || str_contains(strtolower($user['name']), strtolower($search)) || str_contains(strtolower($user['email']), strtolower($search));
            $matchesType = $type === 'all' || $user['type'] === $type;
            $matchesStatus = $status === 'all' || $user['status'] === $status;
            return $matchesSearch && $matchesType && $matchesStatus;
        })->values();

        return view('pages.admin.users', $this->dashboardData('admin', 'Manajemen Data User', 'Kelola akun & moderasi pelanggaran') + [
            'users' => $users,
            'allUsers' => ShareMealState::get('users'),
            'search' => $search,
            'type' => $type,
            'status' => $status,
        ]);
    }

    public function adminTransactions(Request $request): View
    {
        $page = (int) $request->query('page', 1);

        if ($page === 1) {
            $transactions = collect([
                (object)[
                    'id' => 5420,
                    'customer' => (object)['name' => 'Budi Santoso'],
                    'mitra' => (object)['name' => 'Toko Roti Sejahtera'],
                    'total_amount' => 45000,
                    'status' => 'completed',
                    'created_at' => now()->subMinutes(15)
                ],
                (object)[
                    'id' => 5419,
                    'customer' => (object)['name' => 'Siti Aminah'],
                    'mitra' => (object)['name' => 'Warung Makan Ibu Rina'],
                    'total_amount' => 28500,
                    'status' => 'pending',
                    'created_at' => now()->subMinutes(30)
                ],
                (object)[
                    'id' => 5418,
                    'customer' => (object)['name' => 'Andi Wijaya'],
                    'mitra' => (object)['name' => 'Healthy Cafe'],
                    'total_amount' => 120000,
                    'status' => 'completed',
                    'created_at' => now()->subHours(2)
                ],
                (object)[
                    'id' => 5417,
                    'customer' => (object)['name' => 'Rina Melati'],
                    'mitra' => (object)['name' => 'Toko Roti Sejahtera'],
                    'total_amount' => 15000,
                    'status' => 'cancelled',
                    'created_at' => now()->subHours(5)
                ],
            ]);
        } else {
            $transactions = collect([
                (object)[
                    'id' => 5416,
                    'customer' => (object)['name' => 'Dwi Cahyo'],
                    'mitra' => (object)['name' => 'Toko Roti Sejahtera'],
                    'total_amount' => 60000,
                    'status' => 'completed',
                    'created_at' => now()->subHours(6)
                ],
                (object)[
                    'id' => 5415,
                    'customer' => (object)['name' => 'Yuni Pertiwi'],
                    'mitra' => (object)['name' => 'Healthy Cafe'],
                    'total_amount' => 35000,
                    'status' => 'completed',
                    'created_at' => now()->subHours(7)
                ],
            ]);
        }
        
        $stats = [
            'total_transaksi' => 5420,
            'total_selesai' => 4150,
            'total_pending' => 1270,
            'gmv' => 'Rp 189.7M'
        ];

        return view('pages.admin.transactions', $this->dashboardData('admin', 'Pemantauan Transaksi', 'Pantau seluruh aktivitas transaksi di platform ShareMeal') + [
            'transactions' => $transactions,
            'stats' => $stats,
            'page' => $page
        ]);
    }

    public function adminReviews(): View
    {
        $reviews = Review::with(['customer', 'mitra.profile', 'order.items.product'])
            ->latest()
            ->paginate(15);

        $stats = [
            'total_reviews' => Review::count(),
            'avg_rating' => round(Review::avg('rating'), 1) ?: 0,
            'recent_reviews_count' => Review::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return view('pages.admin.reviews', $this->dashboardData('admin', 'Pemantauan Ulasan', 'Pantau kualitas layanan mitra melalui ulasan konsumen') + [
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }

    public function adminReports(Request $request): View
    {
        $stats = [
            'total_food_saved' => '12.480 Kg',
            'co2_reduction' => '31.200 Kg',
            'meals_distributed' => '8.240',
            'impact_value' => 'Rp 245.8M',
            'waste_reduction_rate' => 24.5, // percentage
        ];

        $monthlyData = [
            ['month' => 'Jan', 'saved' => 850, 'target' => 1000],
            ['month' => 'Feb', 'saved' => 1200, 'target' => 1000],
            ['month' => 'Mar', 'saved' => 1500, 'target' => 1000],
            ['month' => 'Apr', 'saved' => 1800, 'target' => 1000],
            ['month' => 'Mei', 'saved' => 2100, 'target' => 1000],
        ];

        $distributions = collect([
            (object)[
                'id' => 1,
                'mitra' => 'Toko Roti Sejahtera',
                'lembaga' => 'Yayasan Kasih Ibu',
                'items' => 'Roti Manis, Brownies',
                'quantity' => '25 Kg',
                'type' => 'Donasi',
                'status' => 'Diterima',
                'date' => now()->subDays(1)->format('d M Y')
            ],
            (object)[
                'id' => 2,
                'mitra' => 'Warung Makan Barokah',
                'lembaga' => 'Panti Asuhan Al-Falah',
                'items' => 'Nasi Bungkus, Lauk Pauk',
                'quantity' => '15 Kg',
                'type' => 'Donasi',
                'status' => 'Diterima',
                'date' => now()->subDays(2)->format('d M Y')
            ],
            (object)[
                'id' => 3,
                'mitra' => 'Healthy Cafe',
                'lembaga' => '-',
                'items' => 'Salad Bowl, Juice',
                'quantity' => '8 Kg',
                'type' => 'Flash Sale',
                'status' => 'Terjual',
                'date' => now()->subDays(3)->format('d M Y')
            ],
            (object)[
                'id' => 4,
                'mitra' => 'Bakery Delight',
                'lembaga' => 'Rumah Singgah',
                'items' => 'Croissant, Danish',
                'quantity' => '12 Kg',
                'type' => 'Donasi',
                'status' => 'Dalam Perjalanan',
                'date' => now()->subDays(1)->format('d M Y')
            ],
            (object)[
                'id' => 5,
                'mitra' => 'Resto Sedap Malam',
                'lembaga' => 'Yayasan Yatim Piatu',
                'items' => 'Ayam Bakar, Nasi',
                'quantity' => '30 Kg',
                'type' => 'Donasi',
                'status' => 'Diterima',
                'date' => now()->subDays(4)->format('d M Y')
            ],
        ]);

        return view('pages.admin.reports', $this->dashboardData('admin', 'Laporan Distribusi & Dampak', 'Evaluasi pengurangan food waste dan dampak sosial platform') + [
            'stats' => $stats,
            'monthlyData' => $monthlyData,
            'distributions' => $distributions,
        ]);
    }

    public function adminWarnUser(int $userId): RedirectResponse
    {
        ShareMealState::warnUser($userId);
        return back()->with('success', 'Peringatan diberikan kepada user.');
    }

    public function adminBlockUser(Request $request, int $userId): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required']]);
        ShareMealState::blockUser($userId, $data['reason']);
        return back()->with('success', 'User diblokir.');
    }

    public function adminUnblockUser(int $userId): RedirectResponse
    {
        ShareMealState::unblockUser($userId);
        return back()->with('success', 'Blokir user dibuka.');
    }

    public function adminEducation(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $tab = (string) $request->query('tab', 'all');
        $articles = collect(ShareMealState::get('articles'))->filter(function ($article) use ($search, $tab) {
            $matchesSearch = $search === '' || str_contains(strtolower($article['title']), strtolower($search)) || str_contains(strtolower($article['category']), strtolower($search));
            $matchesTab = $tab === 'all' || strtolower($article['status']) === $tab;
            return $matchesSearch && $matchesTab;
        })->values();

        return view('pages.admin.education', $this->dashboardData('admin', 'Edukasi Lingkungan', 'Kelola artikel, tips, dan panduan edukasi seputar food waste') + [
            'articles' => $articles,
            'allArticles' => ShareMealState::get('articles'),
            'search' => $search,
            'tab' => $tab,
        ]);
    }

    public function adminEducationStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required'],
            'category' => ['required'],
            'status' => ['required'],
            'content' => ['required'],
        ]);
        ShareMealState::saveArticle($data);
        return back()->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function adminEducationUpdate(Request $request, int $articleId): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required'],
            'category' => ['required'],
            'status' => ['required'],
            'content' => ['required'],
        ]);
        ShareMealState::saveArticle($data, $articleId);
        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    public function adminEducationDelete(int $articleId): RedirectResponse
    {
        ShareMealState::deleteArticle($articleId);
        return back()->with('success', 'Artikel berhasil dihapus.');
    }
}
