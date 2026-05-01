<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Donation;
use App\Support\ShareMealState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                ['label' => 'Dashboard', 'route' => 'mitra.dashboard', 'icon' => 'layout-dashboard'],
                ['label' => 'Inventaris', 'route' => 'mitra.inventory', 'icon' => 'package'],
                ['label' => 'Pesanan', 'route' => 'mitra.orders', 'icon' => 'shopping-cart'],
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
                ['label' => 'Transaksi', 'route' => 'admin.transactions', 'icon' => 'receipt'],
                ['label' => 'Laporan', 'route' => 'admin.reports', 'icon' => 'bar-chart-2'],
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
            'name' => ['required'],
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
        $userId = Auth::id();
        $products = Product::where('user_id', $userId)->get();
        $donations = Donation::where('mitra_id', $userId)->get();
        $orders = \App\Models\Order::with('items')->where('mitra_id', $userId)->get();

        $stats = (object) [
            'totalProducts' => $products->count(),
            'activeFlashSale' => $products->where('status', 'flash-sale')->count(),
            'pendingOrders' => $orders->where('status', 'pending')->count(),
            'totalRevenue' => $orders->where('status', 'completed')->sum('total_amount'),
            'foodSaved' => $orders->where('status', 'completed')->sum(function($order) {
                return $order->items->sum('quantity');
            }),
            'donationsGiven' => $donations->count(),
        ];

        $recentOrders = \App\Models\Order::with(['customer', 'items.product'])
            ->where('mitra_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        $expiringItems = Product::where('user_id', $userId)
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->orderBy('expires_at')
            ->take(5)
            ->get();

        return view('pages.mitra.dashboard', compact('stats', 'recentOrders', 'expiringItems'));
    }

    public function mitraInventory(): View
    {
        $products = Product::where('user_id', Auth::id())->get();

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
            'expires_at' => ['required', 'date'],
            'status' => ['required', 'string', 'in:normal,flash-sale,donation'],
            'image' => ['nullable', 'string'],
        ]);

        Product::create([
            'user_id' => $this->currentUser()['id'] ?? \App\Models\User::where('role', 'mitra')->first()?->id,
            'name' => $data['name'],
            'category' => $data['category'],
            'price' => $data['price'],
            'discount_price' => $data['discount_price'] ?? 0,
            'stock' => $data['stock'],
            'expires_at' => $data['expires_at'],
            'status' => $data['status'],
            'image' => $data['image'] ?? 'https://images.unsplash.com/photo-1666114170628-b34b0dcc21aa?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxiYWtlcnklMjBicmVhZCUyMHBhc3RyeSUyMHNob3B8ZW58MXx8fHwxNzc0OTc0Mzg5fDA&ixlib=rb-4.1.0&q=80&w=1080',
        ]);

        return back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function mitraInventoryUpdate(Request $request, int $productId): RedirectResponse
    {
        $product = Product::where('user_id', Auth::id())->findOrFail($productId);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'discount_price' => ['nullable', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'expires_at' => ['required', 'date'],
            'status' => ['required', 'string', 'in:normal,flash-sale,donation'],
            'image' => ['nullable', 'string'],
        ]);

        $product->update([
            'name' => $data['name'],
            'category' => $data['category'],
            'price' => $data['price'],
            'discount_price' => $data['discount_price'] ?? 0,
            'stock' => $data['stock'],
            'expires_at' => $data['expires_at'],
            'status' => $data['status'],
        ]);

        if (!empty($data['image'])) {
            $product->update(['image' => $data['image']]);
        }

        return back()->with('success', 'Informasi produk berhasil diperbarui.');
    }

    public function mitraInventoryFlashSale(int $productId): RedirectResponse
    {
        $product = Product::where('user_id', Auth::id())->findOrFail($productId);

        $product->update([
            'status' => 'flash-sale',
            'discount_price' => floor($product->price * 0.7), // Example 30% discount
        ]);

        return back()->with('success', 'Flash sale diaktifkan.');
    }

    public function mitraDonationStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        Donation::create([
            'mitra_id' => Auth::id(),
            'title' => $data['title'],
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'description' => $data['description'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Donasi berhasil didaftarkan.');
    }

    public function mitraInventoryDelete(int $productId): RedirectResponse
    {
        $product = Product::where('user_id', Auth::id())->findOrFail($productId);
        $product->delete();

        return back()->with('success', 'Produk dihapus.');
    }

    public function mitraOrders(): View
    {
        $orders = \App\Models\Order::with(['customer', 'items.product'])
            ->where('mitra_id', Auth::id())
            ->latest()
            ->get();

        return view('pages.mitra.orders', compact('orders'));
    }

    public function mitraOrdersConfirm(int $orderId): JsonResponse|RedirectResponse
    {
        $userId = \Illuminate\Support\Facades\Session::get('sharemeal.current_user_id');
        $order = \App\Models\Order::where('mitra_id', $userId)->findOrFail($orderId);
        $order->update(['status' => 'completed']);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Pesanan dikonfirmasi sebagai sudah diambil.');
    }

    public function lembagaDashboard(): View
    {
        $userId = \Illuminate\Support\Facades\Session::get('sharemeal.current_user_id');
        $userObj = User::query()->find($userId);
        $donations = ShareMealState::get('donations');

        return view('pages.lembaga.dashboard', $this->dashboardData('lembaga', 'Dashboard Lembaga Sosial', 'Kelola penerimaan donasi makanan') + [
            'stats' => ['total_donations' => 156, 'active_donations' => 8, 'beneficiaries' => 120, 'this_month' => 45],
            'donations' => ShareMealState::get('donations'),
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
