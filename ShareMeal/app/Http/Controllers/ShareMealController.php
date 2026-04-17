<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ShareMealState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
                ['label' => 'Dashboard', 'route' => 'mitra.dashboard'],
                ['label' => 'Inventaris', 'route' => 'mitra.inventory'],
                ['label' => 'Pesanan', 'route' => 'mitra.orders'],
            ],
            'consumer' => [
                ['label' => 'Dashboard', 'route' => 'consumer.dashboard'],
                ['label' => 'Cari Makanan', 'route' => 'consumer.search'],
                ['label' => 'Riwayat', 'route' => 'consumer.history'],
                ['label' => 'Edukasi', 'route' => 'consumer.education'],
            ],
            'lembaga' => [
                ['label' => 'Dashboard', 'route' => 'lembaga.dashboard'],
                ['label' => 'Donasi', 'route' => 'lembaga.donations'],
            ],
            'admin' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
                ['label' => 'Verifikasi', 'route' => 'admin.verification'],
                ['label' => 'Kelola User', 'route' => 'admin.users'],
                ['label' => 'Edukasi', 'route' => 'admin.education'],
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
                'userName' => $user['type'] === $type ? $user['name'] : match ($type) {
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

    public function landing(): View
    {
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

        // We allow login even if unverified so users can see rejection reasons 
        // and re-upload documents from their dashboard.

        ShareMealState::login($data['user_type'], $user->name);

        return redirect()->route($data['user_type'] . '.dashboard')->with('success', 'Login berhasil.');
    }

    public function register(): View
    {
        return view('pages.auth.register');
    }

    public function doRegister(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6', 'confirmed'],
            'user_type' => ['required', 'in:consumer,mitra,lembaga'],
            'terms' => ['accepted'],
        ];

        // Conditional validation for documents (Mitra Only)
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

        // Process file uploads for Mitra
        if ($data['user_type'] === 'mitra') {
            $userData['document_ktp'] = $request->file('document_ktp_mitra')->store('documents', 'public');
            $userData['document_siup'] = $request->file('document_siup_mitra')->store('documents', 'public');
            $userData['document_nib'] = $request->file('document_nib_mitra')->store('documents', 'public');
            if ($request->hasFile('document_halal_mitra')) {
                $userData['document_halal'] = $request->file('document_halal_mitra')->store('documents', 'public');
            }
        }

        User::query()->create($userData);

        return redirect()->route('login')->with('success', 'Registrasi berhasil. Akun Anda sedang dalam proses verifikasi oleh admin.');
    }

    public function logout(): RedirectResponse
    {
        ShareMealState::logout();
        return redirect()->route('login')->with('success', 'Anda telah keluar.');
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
                ['id' => 'halal', 'label' => 'Halal', 'icon' => '🕌'],
                ['id' => 'vegan', 'label' => 'Vegan', 'icon' => '🌱'],
                ['id' => 'bakery', 'label' => 'Bakery', 'icon' => '🍞'],
                ['id' => 'healthy', 'label' => 'Healthy', 'icon' => '🥗'],
                ['id' => 'indonesian', 'label' => 'Indonesian', 'icon' => '🍜'],
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
        return view('pages.mitra.dashboard', $this->dashboardData('mitra', 'Dashboard Mitra', 'Kelola surplus pangan dan kurangi food waste') + [
            'stats' => (object) [
                'totalProducts' => 2,
                'activeFlashSale' => 1,
                'pendingOrders' => 0,
                'totalRevenue' => 0,
                'foodSaved' => 0,
                'donationsGiven' => 0,
            ],
            'expiringItems' => [
                (object) ['name' => 'Roti Tawar', 'quantity' => 25, 'expiresIn' => '4 hours from now', 'status' => 'warning'],
                (object) ['name' => 'Roti Keju', 'quantity' => 30, 'expiresIn' => '20 hours from now', 'status' => 'normal'],
            ],
            'recentOrders' => []
        ]);
    }

    public function mitraInventory(): View
    {
        return view('pages.mitra.inventory', $this->dashboardData('mitra', 'Manajemen Inventaris Surplus', 'Kelola stok makanan near-expired') + [
            'products' => ShareMealState::get('inventory_products'),
        ]);
    }

    public function mitraInventoryStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required'],
            'category' => ['required'],
            'price' => ['required', 'integer'],
            'stock' => ['required', 'integer'],
            'expires_at' => ['required', 'date'],
            'description' => ['nullable'],
        ]);
        ShareMealState::addInventoryProduct($data);
        return back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function mitraInventoryFlashSale(int $productId): RedirectResponse
    {
        ShareMealState::setFlashSale($productId);
        return back()->with('success', 'Flash sale diaktifkan.');
    }

    public function mitraInventoryDelete(int $productId): RedirectResponse
    {
        ShareMealState::deleteInventoryProduct($productId);
        return back()->with('success', 'Produk dihapus.');
    }

    public function mitraOrders(): View
    {
        return view('pages.mitra.orders', $this->dashboardData('mitra', 'Daftar Pesanan Masuk', 'Kelola pesanan booking pengambilan makanan') + [
            'orders' => ShareMealState::get('orders'),
            'activeTab' => request('tab', 'pending'),
        ]);
    }

    public function mitraOrdersConfirm(string $orderId): RedirectResponse
    {
        ShareMealState::updateOrderStatus($orderId, 'completed');
        return back()->with('success', 'Pesanan dikonfirmasi sebagai sudah diambil.');
    }

    public function lembagaDashboard(): View
    {
        $userId = \Illuminate\Support\Facades\Session::get('sharemeal.current_user_id');
        $userObj = User::query()->find($userId);
        $donations = ShareMealState::get('donations');

        return view('pages.lembaga.dashboard', $this->dashboardData('lembaga', 'Dashboard Lembaga Sosial', 'Kelola penerimaan donasi makanan') + [
            'userObj' => $userObj,
            'stats' => (object) [
                'totalDonations' => 156,
                'activeDonations' => 8,
                'beneficiaries' => 120,
                'thisMonth' => 45,
            ],
            'availableDonations' => collect($donations)->filter(fn($d) => $d['status'] === 'available')->take(5),
            'recentDonations' => collect($donations)->filter(fn($d) => $d['status'] !== 'available')->take(5),
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
        ShareMealState::claimDonation($donationId);
        return back()->with('success', 'Donasi berhasil diklaim.');
    }

    public function lembagaCompleteDonation(string $donationId): RedirectResponse
    {
        ShareMealState::completeDonation($donationId);
        return back()->with('success', 'Donasi dikonfirmasi sudah diterima.');
    }

    public function adminDashboard(): View
    {
        return view('pages.admin.dashboard', $this->dashboardData('admin', 'Dashboard Admin', 'Kelola sistem, verifikasi akun, dan moderasi platform') + [
            'applications' => ShareMealState::get('applications'),
            'users' => ShareMealState::get('users'),
        ]);
    }

    public function adminVerification(): View
    {
        return view('pages.admin.verification', $this->dashboardData('admin', 'Verifikasi Mitra & Lembaga Sosial', 'Sistem approval & verifikasi admin') + [
            'applications' => ShareMealState::get('applications'),
            'activeTab' => request('tab', 'pending'),
        ]);
    }

    public function adminApproveApplication(int $userId): RedirectResponse
    {
        ShareMealState::approveApplication($userId);
        return back()->with('success', 'Aplikasi verifikasi disetujui.');
    }

    public function adminRejectApplication(Request $request, int $userId): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required']]);
        ShareMealState::rejectApplication($userId, $data['reason']);
        return back()->with('success', 'Aplikasi verifikasi ditolak.');
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
