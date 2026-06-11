<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Donation;
use App\Models\Review;
use App\Models\VerificationApplication;
use App\Models\ProblemReport;
use App\Models\Order;
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
                ['label' => 'Riwayat', 'route' => 'mitra.history', 'icon' => 'history'],
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
                ['label' => 'Riwayat Donasi', 'route' => 'lembaga.history', 'icon' => 'history'],
            ],
            'admin' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'layout-dashboard'],
                ['label' => 'Verifikasi', 'route' => 'admin.verification', 'icon' => 'shield'],
                ['label' => 'Kelola User', 'route' => 'admin.users', 'icon' => 'users'],
                ['route' => 'admin.problem-reports.index', 'label' => 'Laporan Masalah', 'icon' => 'alert-triangle'],
                ['label' => 'Transaksi', 'route' => 'admin.transactions', 'icon' => 'shopping-cart'],
                ['label' => 'Laporan', 'route' => 'admin.reports', 'icon' => 'bar-chart'],
                ['label' => 'Edukasi', 'route' => 'admin.education', 'icon' => 'book-open'],
                ['label' => 'Log Admin', 'route' => 'admin.logs', 'icon' => 'activity'],
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
            'email'     => ['required', 'email'],
            'password'  => ['required'],
            'user_type' => ['required', 'in:consumer,mitra,lembaga,admin'],
        ]);

        $user = User::query()
            ->where('email', $data['email'])
            ->where('role', $data['user_type'])
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return back()->with('error', 'Email, password, atau tipe pengguna tidak sesuai.');
        }

        Auth::login($user, $request->boolean('remember'));
        ShareMealState::login($user->id);

        // Reset rate limiter on successful login
        $request->session()->regenerate();

        if ($data['user_type'] === 'mitra') {
            return redirect()->route('mitra.dashboard');
        }

        return redirect()->route($data['user_type'] . '.dashboard');
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
            $rules['organization_name'] = ['required', 'string', 'regex:/^[a-zA-Z0-9\s]+$/'];
            $rules['document_ktp_mitra'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
            $rules['document_siup_mitra'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
            $rules['document_nib_mitra'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
            $rules['document_halal_mitra'] = ['nullable', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
        } elseif ($request->user_type === 'lembaga') {
            $rules['organization_name'] = ['required', 'string', 'regex:/^[a-zA-Z0-9\s]+$/'];
            $rules['document_legalitas_lembaga'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
            $rules['document_izin_lembaga'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
            $rules['document_identitas_lembaga'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
        }

        $data = $request->validate($rules, [
            'name.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'organization_name.required' => 'Nama mitra atau nama lembaga wajib diisi.',
            'organization_name.regex' => 'Nama mitra atau nama lembaga hanya boleh berisi huruf, angka, dan spasi.',
        ]);

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['user_type'],
            'status' => 'active',
            'phone' => null,
            'organization_name' => in_array($data['user_type'], ['mitra', 'lembaga'], true) ? $data['organization_name'] : null,
            'joined_at' => now()->toDateString(),
            'transactions_count' => 0,
            'warnings_count' => 0,
            'is_verified' => $data['user_type'] === 'consumer',
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

        $successMessage = $data['user_type'] === 'consumer' 
            ? 'Registrasi berhasil. Silakan masuk menggunakan akun Anda.' 
            : 'Registrasi berhasil. Akun Anda sedang dalam proses verifikasi oleh admin.';

        return redirect()->route('login')->with('success', $successMessage);
    }

    public function logout(): RedirectResponse
    {
        \Illuminate\Support\Facades\Auth::logout();
        ShareMealState::logout();
        return redirect()->route('login')->with('success', 'Anda telah keluar.');
    }

    public function forgotPassword(): View
    {
        return view('pages.auth.forgot-password');
    }

    public function sendResetOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email'     => ['required', 'email'],
            'user_type' => ['required', 'in:consumer,mitra,lembaga,admin'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'user_type.required' => 'Tipe pengguna wajib dipilih.',
        ]);

        if ($data['user_type'] === 'admin') {
            return back()->with('error', 'Fitur lupa sandi tidak tersedia untuk Administrator.');
        }

        $user = User::query()
            ->where('email', $data['email'])
            ->where('role', $data['user_type'])
            ->first();

        if (!$user) {
            return back()->with('error', 'Email dengan tipe pengguna tersebut tidak terdaftar.');
        }

        // Generate 6-digit OTP
        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP hash and timestamp
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $data['email']],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // Flash to session for testing/grading (simulates receiving email)
        session()->flash('demo_reset_otp', $otp);

        return redirect()->route('password.verify_otp_form', [
            'email' => $data['email'],
            'user_type' => $data['user_type'],
        ])->with('success', 'Kode OTP reset kata sandi telah dikirim. Silakan masukkan kode OTP di bawah.');
    }

    public function verifyResetOtpForm(Request $request): View
    {
        return view('pages.auth.verify-otp', [
            'email' => $request->query('email'),
            'user_type' => $request->query('user_type'),
        ]);
    }

    public function verifyResetOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email'     => ['required', 'email'],
            'user_type' => ['required', 'in:consumer,mitra,lembaga,admin'],
            'otp'       => ['required', 'digits:6'],
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.digits' => 'Kode OTP harus berupa 6 digit angka.',
        ]);

        if ($data['user_type'] === 'admin') {
            return back()->with('error', 'Fitur lupa sandi tidak tersedia untuk Administrator.');
        }

        $resetRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->first();

        if (!$resetRecord) {
            return back()->with('error', 'Permintaan verifikasi tidak ditemukan atau telah kedaluwarsa.');
        }

        // Check if OTP has expired (10 minutes)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(10)->isPast()) {
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            return redirect()->route('password.request')->with('error', 'Kode OTP sudah kedaluwarsa. Silakan ajukan lupa sandi kembali.');
        }

        // Verify OTP
        if (!Hash::check($data['otp'], $resetRecord->token)) {
            return back()->with('error', 'Kode OTP tidak valid.');
        }

        // Store verification status in session
        session()->put('reset_password_verified_email', $data['email']);
        session()->put('reset_password_verified_type', $data['user_type']);
        session()->put('reset_password_verified_otp', $data['otp']);

        return redirect()->route('password.reset')->with('success', 'OTP terverifikasi. Silakan masukkan kata sandi baru.');
    }

    public function resetPassword(): mixed
    {
        $email = session('reset_password_verified_email');
        $user_type = session('reset_password_verified_type');

        if (!$email || !$user_type) {
            return redirect()->route('password.request')->with('error', 'Silakan verifikasi kode OTP Anda terlebih dahulu.');
        }

        return view('pages.auth.reset-password', [
            'email' => $email,
            'user_type' => $user_type,
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $sessionEmail = session('reset_password_verified_email');
        $sessionType = session('reset_password_verified_type');
        $sessionOtp = session('reset_password_verified_otp');

        if (!$sessionEmail || !$sessionType || !$sessionOtp) {
            return redirect()->route('password.request')->with('error', 'Sesi verifikasi Anda tidak valid. Silakan ajukan lupa sandi kembali.');
        }

        $data = $request->validate([
            'password' => ['required', 'min:6', 'confirmed'],
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal harus 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::query()
            ->where('email', $sessionEmail)
            ->where('role', $sessionType)
            ->first();

        if (!$user) {
            return redirect()->route('password.request')->with('error', 'Identitas pengguna tidak ditemukan.');
        }

        // Additional sanity check on DB token
        $resetRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $sessionEmail)
            ->first();

        if (!$resetRecord || !Hash::check($sessionOtp, $resetRecord->token)) {
            return redirect()->route('password.request')->with('error', 'Permintaan reset sandi tidak valid atau telah kedaluwarsa.');
        }

        // Update password
        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        // Clean up session and DB token
        session()->forget([
            'reset_password_verified_email',
            'reset_password_verified_type',
            'reset_password_verified_otp'
        ]);
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $sessionEmail)->delete();

        return redirect()->route('login')->with('success', 'Kata sandi berhasil diperbarui. Silakan masuk menggunakan kata sandi baru.');
    }

    public function markNotificationsRead(): RedirectResponse
    {
        if (Auth::check()) {
            Auth::user()->unreadNotifications->markAsRead();
        }
        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    public function markSingleNotificationRead(string $id): RedirectResponse
    {
        if (Auth::check()) {
            Auth::user()->notifications()->findOrFail($id)->markAsRead();
        }
        return back();
    }

    public function allNotifications(): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login untuk mengakses notifikasi.');
        }
        
        $notifications = $user->notifications()->paginate(15);
        $role = $user->role ?? 'consumer';

        return view('pages.notifications', $this->dashboardData($role, 'Semua Notifikasi', 'Pantau semua aktivitas dan pemberitahuan Anda') + [
            'notificationsList' => $notifications,
        ]);
    }

    public function editProfile(): View|RedirectResponse
    {
        $user = Auth::user()?->load('profile');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login untuk mengelola profil.');
        }

        if ($user->role === 'consumer' && !$user->is_verified) {
            $user->update(['is_verified' => true]);
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

        if ($user->role === 'consumer' && !$user->is_verified) {
            $user->update(['is_verified' => true]);
        }

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
        if ($user->role === 'lembaga') {
            if ($request->hasFile('document_ktp')) {
                $updates['document_legalitas'] = $request->file('document_ktp')->store('documents', 'public');
            }
            if ($request->hasFile('document_siup')) {
                $updates['document_izin'] = $request->file('document_siup')->store('documents', 'public');
            }
            if ($request->hasFile('document_nib')) {
                $updates['document_identitas'] = $request->file('document_nib')->store('documents', 'public');
            }
        } else {
            foreach (['document_ktp', 'document_siup', 'document_nib', 'document_halal'] as $field) {
                if ($request->hasFile($field)) {
                    $updates[$field] = $request->file($field)->store('documents', 'public');
                }
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

    public function mitraInventory(): View
    {
        $userId = Auth::id() ?? \App\Models\User::where('role', 'mitra')->value('id');
        app(AutoDonationService::class)->processProducts($userId);

        $user = Auth::user()?->load('profile');
        $profile = $user?->profile;
        $openingHours = $profile?->opening_hours ?? '08:00 - 20:00';
        $parts = explode(' - ', $openingHours);
        $shopOpen = count($parts) === 2 ? trim($parts[0]) : '08:00';
        $shopClose = count($parts) === 2 ? trim($parts[1]) : '20:00';

        try {
            $startCarbon = \Carbon\Carbon::createFromFormat('H:i', $shopOpen);
            $defaultPickupStart = $startCarbon->addHour()->format('H:i');
        } catch (\Exception $e) {
            $defaultPickupStart = '09:00';
        }
        $defaultPickupEnd = $shopClose;

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

        return view('pages.mitra.inventory', compact('products', 'defaultPickupStart', 'defaultPickupEnd'));
    }

    public function mitraInventoryStore(Request $request): RedirectResponse
    {
        if (!Auth::user()?->is_verified) {
            return back()->with('error', 'Akun Anda belum terverifikasi. Anda tidak dapat menambahkan produk ke inventaris.');
        }

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
            'image.max' => 'Ukuran gambar tidak boleh melebihi 2MB.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif.',
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

        $discountPrice = $data['discount_price'] ?? 0;
        if ($data['status'] === 'flash-sale' && $discountPrice <= 0) {
            $discountPrice = floor($data['price'] * 0.7);
        }

        $product = Product::create([
            'user_id' => Auth::id() ?? \App\Models\User::where('role', 'mitra')->first()?->id,
            'name' => $data['name'],
            'category' => $data['category'],
            'price' => $data['price'],
            'discount_price' => $discountPrice,
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

        if ($product->status === 'expired' || $product->expires_at->isPast() || $product->stock <= 0) {
            return back()->with('error', 'Produk sudah habis atau kedaluwarsa dan tidak dapat diubah.');
        }

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
            'image.max' => 'Ukuran gambar tidak boleh melebihi 2MB.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif.',
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

        $discountPrice = $data['discount_price'] ?? 0;
        if ($data['status'] === 'flash-sale' && $discountPrice <= 0) {
            $discountPrice = floor($data['price'] * 0.7);
        }

        $product->update([
            'name' => $data['name'],
            'category' => $data['category'],
            'price' => $data['price'],
            'discount_price' => $discountPrice,
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

        if ($product->status === 'expired' || $product->expires_at->isPast() || $product->stock <= 0) {
            return back()->with('error', 'Produk sudah habis atau kedaluwarsa.');
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

        if ($product->status === 'expired' || $product->expires_at->isPast() || $product->stock <= 0) {
            return back()->with('error', 'Produk sudah habis atau kedaluwarsa.');
        }

        $product->update([
            'donatable' => !$product->donatable,
        ]);

        $status = $product->donatable ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', 'Donasi otomatis untuk "' . $product->name . '" berhasil ' . $status . '.');
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

    public function mitraDonationComplete(int $donationId): RedirectResponse
    {
        $userId = Auth::id() ?? \Illuminate\Support\Facades\Session::get('sharemeal.current_user_id');
        $donation = Donation::where('mitra_id', $userId)->findOrFail($donationId);

        if ($donation->status !== 'claimed') {
            return back()->with('error', 'Hanya donasi yang sudah diklaim yang bisa diselesaikan.');
        }

        $donation->update([
            'status' => 'completed',
            'delivered_at' => now(),
            'tracking_status' => 'delivered',
        ]);

        return back()->with('success', 'Donasi dikonfirmasi telah diserahkan.');
    }

    public function mitraDonationCancel(int $donationId): RedirectResponse
    {
        $userId = Auth::id() ?? \Illuminate\Support\Facades\Session::get('sharemeal.current_user_id');
        $donation = Donation::where('mitra_id', $userId)->findOrFail($donationId);

        if ($donation->status === 'completed') {
            return back()->with('error', 'Donasi yang sudah selesai tidak bisa dibatalkan.');
        }

        if ($donation->status === 'claimed') {
            // Optional: notify lembaga if needed
        }

        $donation->delete();

        return back()->with('success', 'Donasi berhasil dibatalkan/dihapus.');
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
        \App\Models\Order::checkAndApplyDelays();
        $userId = Auth::id() ?? \App\Models\User::where('role', 'mitra')->value('id');
        $orders = \App\Models\Order::with(['customer.profile', 'items.product', 'reviewRelation'])
            ->where('mitra_id', $userId)
            ->latest()
            ->get();

        return view('pages.mitra.orders', compact('orders'));
    }

    public function mitraHistory(): View
    {
        $userId = Auth::id() ?? \App\Models\User::where('role', 'mitra')->value('id');
        
        $orders = \App\Models\Order::with(['customer.profile', 'items.product', 'reviewRelation'])
            ->where('mitra_id', $userId)
            ->whereIn('status', ['completed', 'cancelled'])
            ->latest('updated_at')
            ->get();

        $stats = (object) [
            'total_orders' => $orders->count(),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
            'total_revenue' => $orders->where('status', 'completed')->sum('total_amount'),
        ];

        return view('pages.mitra.history', $this->dashboardData('mitra', 'Riwayat Transaksi', 'Manajemen histori transaksi penjualan') + [
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }

    public function mitraReviews(): View
    {
        $userId = Auth::id() ?? \App\Models\User::where('role', 'mitra')->value('id');
        
        $allReviews = Review::where('mitra_id', $userId)->get();
        
        $stats = [
            'average' => round($allReviews->avg('rating') ?? 0, 1),
            'total' => $allReviews->count(),
            'counts' => [
                5 => $allReviews->where('rating', 5)->count(),
                4 => $allReviews->where('rating', 4)->count(),
                3 => $allReviews->where('rating', 3)->count(),
                2 => $allReviews->where('rating', 2)->count(),
                1 => $allReviews->where('rating', 1)->count(),
            ]
        ];

        $reviews = Review::with(['customer', 'order.items.product'])
            ->where('mitra_id', $userId)
            ->latest()
            ->paginate(10);

        return view('pages.mitra.reviews', compact('reviews', 'stats'));
    }

    public function updateOrderStatus(Request $request, int $orderId): JsonResponse|RedirectResponse
    {
        $userId = Auth::id() ?? \App\Models\User::where('role', 'mitra')->value('id');
        $order = \App\Models\Order::where('mitra_id', $userId)->findOrFail($orderId);

        $request->validate([
            'status' => ['required', 'in:pending,processing,ready,shipping,completed,cancelled'],
            'cancel_reason' => ['nullable', 'required_if:status,cancelled', 'string', 'max:500'],
        ]);

        $updateData = ['status' => $request->status];
        if ($request->status === 'cancelled') {
            $updateData['cancel_reason'] = $request->cancel_reason ?: 'Dibatalkan oleh mitra toko.';
        } elseif (in_array($request->status, ['processing', 'ready']) && $order->status === 'pending' && $order->receiving_method === 'pickup') {
            // Batas waktu pengambilan mulai berjalan (1 jam) saat pesanan diproses oleh mitra
            $updateData['pickup_start_time'] = now()->format('H:i:s');
            $updateData['pickup_end_time'] = now()->addHour()->format('H:i:s');
        }

        $order->update($updateData);

        // Send notification to consumer (handled automatically via Order model booted observer)

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'status' => $order->status,
                'completed_time' => $order->completedTime,
            ]);
        }

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function delayOrder(Request $request, int $orderId): JsonResponse|RedirectResponse
    {
        $userId = Auth::id() ?? \App\Models\User::where('role', 'mitra')->value('id');
        $order = \App\Models\Order::where('mitra_id', $userId)->findOrFail($orderId);

        if ($order->status !== 'processing') {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan hanya dapat ditandai delay jika statusnya sedang diproses.',
                ], 422);
            }
            return back()->with('error', 'Pesanan hanya dapat ditandai delay jika statusnya sedang diproses.');
        }

        $order->update([
            'is_delayed' => true,
            'delayed_at' => now(),
        ]);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_delayed' => true,
                'message' => 'Pesanan berhasil ditandai delay.',
            ]);
        }

        return back()->with('success', 'Pesanan berhasil ditandai delay.');
    }

    public function mitraOrdersConfirm(int $orderId): JsonResponse|RedirectResponse
    {
        $userId = Auth::id() ?? \App\Models\User::where('role', 'mitra')->value('id');
        $order = \App\Models\Order::where('mitra_id', $userId)->findOrFail($orderId);
        $order->update(['status' => 'completed']);

        // Send notification to consumer (handled automatically via Order model booted observer)

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
        
        $allDonations = ShareMealState::get('donations');
        
        // Filter donations: show available ones, and only show claimed/completed if claimed by this user
        $donations = collect($allDonations)->filter(function ($donation) use ($userObj) {
            return $donation['status'] === 'available' || $donation['lembaga_id'] == $userObj->id;
        })->values()->all();

        // PBI #45: Add critical alert for active claimed donations
        $criticalAlerts = [];
        $activeClaimedCount = collect($donations)->where('status', 'claimed')->count();
        if ($activeClaimedCount > 0) {
            $criticalAlerts[] = [
                'type' => 'info',
                'title' => 'Status Klaim Donasi',
                'message' => "Ada $activeClaimedCount donasi yang sudah Anda klaim dan menunggu penjemputan.",
                'link' => route('lembaga.donations', ['tab' => 'claimed']),
                'link_text' => 'Lihat Jadwal'
            ];
        }
        session()->flash('critical_alerts', $criticalAlerts);

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
        $userId = Auth::id() ?? \Illuminate\Support\Facades\Session::get('sharemeal.current_user_id');
        $allDonations = ShareMealState::get('donations');

        // Filter donations: show available ones, and only show claimed/completed if claimed by this user
        $donations = collect($allDonations)->filter(function ($donation) use ($userId) {
            return $donation['status'] === 'available' || $donation['lembaga_id'] == $userId;
        })->values()->all();

        return view('pages.lembaga.donations', $this->dashboardData('lembaga', 'Kelola Donasi', 'Klaim & tracking donasi makanan') + [
            'donations' => $donations,
            'activeTab' => request('tab', 'available'),
        ]);
    }

    public function lembagaHistory(): View
    {
        $userId = Auth::id() ?? \Illuminate\Support\Facades\Session::get('sharemeal.current_user_id');
        $allDonations = ShareMealState::get('donations');

        // Filter donations: only show completed donations for this user (lembaga_id == $userId && status == 'completed')
        $completedDonations = collect($allDonations)->filter(function ($donation) use ($userId) {
            return $donation['lembaga_id'] == $userId && $donation['status'] === 'completed';
        })->values()->all();

        return view('pages.lembaga.history', $this->dashboardData('lembaga', 'Riwayat Penerimaan Donasi', 'Daftar donasi makanan yang berhasil diterima') + [
            'completedDonations' => $completedDonations,
        ]);
    }

    public function lembagaClaimDonation(Request $request, string $donationId): RedirectResponse
    {
        $userId = Auth::id() ?? \Illuminate\Support\Facades\Session::get('sharemeal.current_user_id');
        $user = \App\Models\User::find($userId);

        if (!$user || !$user->is_verified) {
            return back()->with('error', 'Klaim donasi gagal. Akun Lembaga Anda belum terverifikasi atau telah ditolak oleh admin.');
        }

        $request->validate([
            'pickup_time' => ['required', 'string'],
        ]);

        $donation = \App\Models\Donation::with('mitra')->findOrFail($donationId);

        if ($donation->status !== 'pending' || ($donation->expires_at && \Carbon\Carbon::parse($donation->expires_at)->isPast())) {
            return back()->with('error', 'Donasi sudah tidak tersedia atau telah kedaluwarsa.');
        }

        // Parse the pickup time which might contain full date or just time
        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $request->pickup_time)) {
                $pickupTime = \Carbon\Carbon::parse($request->pickup_time);
            } else {
                $pickupTime = \Carbon\Carbon::today()->setTimeFromTimeString($request->pickup_time);
                if ($pickupTime->isPast()) {
                    $pickupTime->addDay();
                }
            }
        } catch (\Exception $e) {
            $pickupTime = \Carbon\Carbon::today()->setTimeFromTimeString($request->pickup_time);
        }

        $donation->update([
            'status' => 'claimed',
            'claimed_at' => now(),
            'pickup_time' => $pickupTime,
            'tracking_status' => 'confirmed',
            'lembaga_id' => $userId
        ]);

        // Increment transaction counts for both Lembaga and Mitra
        User::query()->whereKey($userId)->increment('transactions_count');
        User::query()->whereKey($donation->mitra_id)->increment('transactions_count');

        // Notify the Mitra that their donation was claimed
        if ($donation->mitra) {
            $lembagaName = Auth::user()->name ?? \App\Models\User::find($userId)?->name ?? 'Lembaga Sosial';
            \Illuminate\Support\Facades\Notification::send(
                $donation->mitra,
                new \App\Notifications\DonationClaimedNotification($lembagaName, $donation->title, $donation->quantity . ' ' . $donation->unit)
            );
        }

        return back()->with('success', 'Donasi berhasil diklaim. Jadwal penjemputan: ' . $pickupTime->format('H:i'));
    }
    public function lembagaCompleteDonation(string $donationId): RedirectResponse
    {
        $donation = Donation::findOrFail($donationId);

        if ($donation->status !== 'claimed') {
            return back()->with('error', 'Hanya donasi yang sudah diklaim yang bisa diselesaikan.');
        }

        $donation->update([
            'status' => 'completed',
            'delivered_at' => now(),
            'tracking_status' => 'delivered',
        ]);

        return back()->with('success', 'Donasi dikonfirmasi sudah diterima.');
    }

    public function lembagaSubmitProblemReport(Request $request)
    {
        $data = $request->validate([
            'donation_id' => ['required', 'exists:donations,id'],
            'issue_type' => ['required', 'string', 'in:expired,bad_quality,mismatch,other'],
            'description' => ['required', 'string', 'max:2000'],
            'evidence_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $userId = Auth::id() ?? \Illuminate\Support\Facades\Session::get('sharemeal.current_user_id');
        $donation = \App\Models\Donation::where('id', $data['donation_id'])
            ->where('lembaga_id', $userId)
            ->firstOrFail();

        $evidencePath = null;
        if ($request->hasFile('evidence_image')) {
            $evidencePath = $request->file('evidence_image')->store('reports', 'public');
        }

        $report = \App\Models\ProblemReport::create([
            'reporter_id' => $userId,
            'mitra_id' => $donation->mitra_id,
            'donation_id' => $donation->id,
            'issue_type' => $data['issue_type'],
            'description' => $data['description'],
            'evidence_image' => $evidencePath,
            'status' => 'pending',
        ]);

        // Notify Admins
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\NewProblemReportNotification($report));
        }

        return back()->with('success', 'Laporan masalah donasi berhasil dikirim.');
    }

    public function adminDashboard(): View
    {
        $activities = collect();

        // 1. New user registrations (excluding admin)
        $newUsers = User::where('role', '!=', 'admin')->latest()->take(5)->get();
        foreach ($newUsers as $user) {
            $roleLabel = match($user->role) {
                'mitra' => 'Mitra Toko',
                'lembaga' => 'Lembaga Sosial',
                'consumer' => 'Konsumen',
                default => $user->role
            };
            $activities->push([
                'title' => $user->name,
                'description' => 'Registrasi akun baru sebagai ' . $roleLabel,
                'time' => $user->created_at ? $user->created_at->diffForHumans() : '-',
                'type' => 'info',
                'icon' => 'user-plus',
                'timestamp' => $user->created_at
            ]);
        }

        // 2. New verification applications
        $newApps = VerificationApplication::latest()->take(5)->get();
        foreach ($newApps as $app) {
            $typeLabel = $app->type === 'mitra' ? 'Mitra Toko' : 'Lembaga Sosial';
            $statusLabel = match($app->status) {
                'pending' => 'Menunggu verifikasi dokumen',
                'approved' => 'Dokumen verifikasi disetujui',
                'rejected' => 'Dokumen verifikasi ditolak',
                default => $app->status
            };
            $type = match($app->status) {
                'approved' => 'success',
                'rejected' => 'danger',
                default => 'warning'
            };
            $icon = match($app->status) {
                'approved' => 'check-circle',
                'rejected' => 'x-circle',
                default => 'clock'
            };
            $activities->push([
                'title' => $app->name,
                'description' => $statusLabel . ' (' . $typeLabel . ')',
                'time' => $app->created_at ? $app->created_at->diffForHumans() : '-',
                'type' => $type,
                'icon' => $icon,
                'timestamp' => $app->created_at
            ]);
        }

        // 3. New problem reports
        $newReports = ProblemReport::with('reporter')->latest()->take(5)->get();
        foreach ($newReports as $report) {
            $reporterName = $report->reporter ? $report->reporter->name : 'Pengguna';
            $statusLabel = match($report->status) {
                'pending' => 'Laporan masalah baru diajukan oleh ' . $reporterName,
                'resolved' => 'Laporan masalah diselesaikan oleh Admin',
                default => 'Laporan masalah status: ' . $report->status
            };
            $type = $report->status === 'resolved' ? 'success' : 'danger';
            $icon = $report->status === 'resolved' ? 'check-circle' : 'alert-circle';
            
            $activities->push([
                'title' => 'Laporan Masalah: ' . $report->issue_label,
                'description' => $statusLabel . ' - "' . $report->description . '"',
                'time' => $report->created_at ? $report->created_at->diffForHumans() : '-',
                'type' => $type,
                'icon' => $icon,
                'timestamp' => $report->created_at
            ]);
        }

        // 4. User profile updates
        $profileUpdates = \App\Models\UserProfile::with('user')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        foreach ($profileUpdates as $profile) {
            if (!$profile->user) {
                continue;
            }
            $roleLabel = match($profile->user->role) {
                'mitra' => 'Mitra Toko',
                'lembaga' => 'Lembaga Sosial',
                'consumer' => 'Konsumen',
                default => $profile->user->role
            };
            $activities->push([
                'title' => $profile->user->name,
                'description' => 'Memperbarui informasi profil ' . $roleLabel,
                'time' => $profile->updated_at ? $profile->updated_at->diffForHumans() : '-',
                'type' => 'success',
                'icon' => 'user-cog',
                'timestamp' => $profile->updated_at
            ]);
        }

        // Sort all by timestamp descending, take top 8, and transform
        $activities = $activities->sortByDesc('timestamp')->take(8)->values()->all();

        $applications = ShareMealState::get('applications');

        $totalUser = User::count();
        $mitraAktif = User::where('role', 'mitra')->count();
        $lembagaAktif = User::where('role', 'lembaga')->count();
        $totalTransaksi = Order::count();
        
        $makananSavedRaw = Order::where('status', 'completed')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->sum('order_items.quantity');
            
        $co2Raw = $makananSavedRaw * 2.5;
        $gmvRaw = Order::where('status', 'completed')->sum('total_amount');

        if ($makananSavedRaw >= 1000) {
            $makanan_saved = number_format($makananSavedRaw / 1000, 1, ',', '.') . 'k';
        } else {
            $makanan_saved = number_format($makananSavedRaw, 0, ',', '.');
        }

        if ($gmvRaw >= 1000000) {
            $gmv_platform = 'Rp ' . number_format($gmvRaw / 1000000, 1, ',', '.') . 'Jt';
        } else {
            $gmv_platform = 'Rp ' . number_format($gmvRaw, 0, ',', '.');
        }

        return view('pages.admin.dashboard', $this->dashboardData('admin', 'Dashboard Admin', 'Kelola sistem, verifikasi akun, dan moderasi platform') + [
            'applications' => $applications,
            'users' => ShareMealState::get('users'),
            'activities' => $activities,
            'stats' => [
                'total_user' => $totalUser,
                'pending' => count($applications),
                'mitra_aktif' => $mitraAktif,
                'lembaga_aktif' => $lembagaAktif,
                'transaksi' => $totalTransaksi,
                'makanan_saved' => $makanan_saved,
                'co2_dikurangi' => number_format($co2Raw, 0, ',', '.'),
                'gmv_platform' => $gmv_platform,
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
        $app = \App\Models\VerificationApplication::find($applicationId);
        $orgName = $app ? ($app->user?->organization_name ?? $app->user?->name) : 'Aplikasi #' . $applicationId;
        ShareMealState::approveApplication($applicationId);

        \App\Models\AdminLog::create([
            'admin_id' => Auth::id() ?? \App\Models\User::where('role', 'admin')->value('id'),
            'action' => 'verify_approve',
            'target_id' => $applicationId,
            'details' => 'Menyetujui verifikasi berkas akun: ' . $orgName,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Aplikasi disetujui.');
    }

    public function adminRejectApplication(Request $request, int $applicationId): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required']]);
        $app = \App\Models\VerificationApplication::find($applicationId);
        $orgName = $app ? ($app->user?->organization_name ?? $app->user?->name) : 'Aplikasi #' . $applicationId;
        ShareMealState::rejectApplication($applicationId, $data['reason']);

        \App\Models\AdminLog::create([
            'admin_id' => Auth::id() ?? \App\Models\User::where('role', 'admin')->value('id'),
            'action' => 'verify_reject',
            'target_id' => $applicationId,
            'details' => 'Menolak verifikasi berkas akun: ' . $orgName . ' dengan alasan: ' . $data['reason'],
            'ip_address' => request()->ip(),
        ]);

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
        $search = $request->query('search');
        $perPage = 10;

        $query = Order::with(['customer', 'mitra'])->latest();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('mitra', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $totalTransactions = $query->count();
        $totalPages = max(1, (int) ceil($totalTransactions / $perPage));

        // Ensure page parameter is within valid range
        if ($page < 1) {
            $page = 1;
        } elseif ($page > $totalPages) {
            $page = $totalPages;
        }

        $transactions = $query->skip(($page - 1) * $perPage)->take($perPage)->get();
        
        $totalSelesai = Order::where('status', 'completed')->count();
        $totalPending = Order::where('status', 'pending')->count();
        $gmvRaw = Order::where('status', 'completed')->sum('total_amount');

        if ($gmvRaw >= 1000000000) {
            $gmv = 'Rp ' . number_format($gmvRaw / 1000000000, 1, ',', '.') . 'M';
        } elseif ($gmvRaw >= 1000000) {
            $gmv = 'Rp ' . number_format($gmvRaw / 1000000, 1, ',', '.') . 'Jt';
        } else {
            $gmv = 'Rp ' . number_format($gmvRaw, 0, ',', '.');
        }

        $stats = [
            'total_transaksi' => Order::count(),
            'total_selesai' => $totalSelesai,
            'total_pending' => $totalPending,
            'gmv' => $gmv
        ];

        return view('pages.admin.transactions', $this->dashboardData('admin', 'Pemantauan Transaksi', 'Pantau seluruh aktivitas transaksi di platform ShareMeal') + [
            'transactions' => $transactions,
            'stats' => $stats,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search
        ]);
    }

    public function adminExportTransactionsCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // Seluruh data transaksi (semua halaman)
        $allTransactions = Order::with(['customer', 'mitra'])->latest()->get();

        $filename = 'transaksi_sharemeal_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ];

        $callback = function () use ($allTransactions) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8 agar Excel tidak garbled
            fputs($file, "\xEF\xBB\xBF");

            // Header baris
            fputcsv($file, [
                'ID Transaksi',
                'Konsumen',
                'Mitra',
                'Total (Rp)',
                'Status',
                'Tanggal',
                'Jam (WIB)',
            ]);

            foreach ($allTransactions as $trx) {
                $statusLabel = match ($trx->status) {
                    'completed' => 'Selesai',
                    'pending'   => 'Menunggu',
                    'cancelled' => 'Dibatalkan',
                    default     => $trx->status,
                };

                fputcsv($file, [
                    'TRX-' . str_pad($trx->id, 5, '0', STR_PAD_LEFT),
                    $trx->customer->name ?? '-',
                    $trx->mitra->name ?? '-',
                    $trx->total_amount,
                    $statusLabel,
                    $trx->created_at ? $trx->created_at->format('d/m/Y') : '-',
                    $trx->created_at ? $trx->created_at->format('H:i') : '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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

    public function adminExportReportsExcel()
    {
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="Laporan_Distribusi_ShareMeal.xls"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Laporan Penyaluran & Distribusi ShareMeal']);
            fputcsv($file, []);
            fputcsv($file, ['Mitra', 'Lembaga', 'Item Makanan', 'Jumlah', 'Tipe', 'Status', 'Tanggal']);
            
            $distributions = [
                ['Toko Roti Sejahtera', 'Yayasan Kasih Ibu', 'Roti Manis, Brownies', '25 Kg', 'Donasi', 'Diterima', '2026-03-31'],
                ['Warung Makan Barokah', 'Panti Asuhan Al-Falah', 'Nasi Bungkus, Lauk Pauk', '15 Kg', 'Donasi', 'Diterima', '2026-03-30'],
                ['Healthy Cafe', '-', 'Salad Bowl, Juice', '8 Kg', 'Flash Sale', 'Terjual', '2026-03-29'],
                ['Bakery Delight', 'Rumah Singgah', 'Croissant, Danish', '12 Kg', 'Donasi', 'Dalam Perjalanan', '2026-03-31'],
                ['Resto Sedap Malam', 'Yayasan Yatim Piatu', 'Ayam Bakar, Nasi', '30 Kg', 'Donasi', 'Diterima', '2026-03-28']
            ];
            
            foreach ($distributions as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function adminExportReportsPdf()
    {
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Laporan_Distribusi_ShareMeal.pdf"',
        ];

        $callback = function() {
            echo "%PDF-1.4\n";
            echo "1 0 obj <</Type /Catalog /Pages 2 0 R>> endobj\n";
            echo "2 0 obj <</Type /Pages /Kids [3 0 R] /Count 1>> endobj\n";
            echo "3 0 obj <</Type /Page /Parent 2 0 R /Resources <</Font <</F1 4 0 R>>>> /MediaBox [0 0 595 842] /Contents 5 0 R>> endobj\n";
            echo "4 0 obj <</Type /Font /Subtype /Type1 /BaseFont /Helvetica>> endobj\n";
            
            $content = "BT /F1 12 Tf 50 750 Td (Laporan Penyaluran & Distribusi ShareMeal) Tj ET\n";
            $content .= "BT /F1 10 Tf 50 720 Td (Total Makanan Terselamatkan: 12.480 Kg) Tj ET\n";
            $content .= "BT /F1 10 Tf 50 700 Td (Reduksi Emisi CO2: 31.200 Kg) Tj ET\n";
            $content .= "BT /F1 10 Tf 50 680 Td (Estimasi Nilai Ekonomi: Rp 245.8M) Tj ET\n";
            
            $len = strlen($content);
            echo "5 0 obj <</Length $len>> stream\n" . $content . "endstream\nendobj\n";
            echo "xref\n0 6\n0000000000 65535 f\n";
            echo "trailer <</Size 6 /Root 1 0 R>>\n";
            echo "startxref\n350\n%%EOF\n";
        };

        return response()->stream($callback, 200, $headers);
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

    public function adminWarnUser(Request $request, int $userId): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required']]);
        $user = \App\Models\User::find($userId);
        $name = $user ? $user->displayName : 'User #' . $userId;
        ShareMealState::warnUser($userId, $data['reason']);

        \App\Models\AdminLog::create([
            'admin_id' => Auth::id() ?? \App\Models\User::where('role', 'admin')->value('id'),
            'action' => 'warn_user',
            'target_id' => $userId,
            'details' => 'Mengirim peringatan resmi kepada ' . $name . '. Alasan: ' . $data['reason'],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Peringatan diberikan kepada user.');
    }

    public function adminBlockUser(Request $request, int $userId): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required']]);
        $user = \App\Models\User::find($userId);
        $name = $user ? $user->displayName : 'User #' . $userId;
        ShareMealState::blockUser($userId, $data['reason']);

        \App\Models\AdminLog::create([
            'admin_id' => Auth::id() ?? \App\Models\User::where('role', 'admin')->value('id'),
            'action' => 'block_user',
            'target_id' => $userId,
            'details' => 'Memblokir akun ' . $name . '. Alasan: ' . $data['reason'],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'User diblokir.');
    }

    public function adminUnblockUser(int $userId): RedirectResponse
    {
        $user = \App\Models\User::find($userId);
        $name = $user ? $user->displayName : 'User #' . $userId;
        ShareMealState::unblockUser($userId);

        \App\Models\AdminLog::create([
            'admin_id' => Auth::id() ?? \App\Models\User::where('role', 'admin')->value('id'),
            'action' => 'unblock_user',
            'target_id' => $userId,
            'details' => 'Membuka blokir akun ' . $name,
            'ip_address' => request()->ip(),
        ]);

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
            'title'    => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'status'   => ['required', 'string'],
            'content'  => ['required', 'string'],
            'image'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('articles', 'public');
        }

        ShareMealState::saveArticle($data);

        \App\Models\AdminLog::create([
            'admin_id' => Auth::id() ?? \App\Models\User::where('role', 'admin')->value('id'),
            'action' => 'education_create',
            'details' => 'Membuat artikel edukasi baru: "' . $data['title'] . '"',
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function adminEducationUpdate(Request $request, int $articleId): RedirectResponse
    {
        $data = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'status'   => ['required', 'string'],
            'content'  => ['required', 'string'],
            'image'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            $oldArticle = \App\Models\Article::find($articleId);
            if ($oldArticle && $oldArticle->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldArticle->image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldArticle->image);
            }
            $data['image_path'] = $request->file('image')->store('articles', 'public');
        }

        ShareMealState::saveArticle($data, $articleId);

        \App\Models\AdminLog::create([
            'admin_id' => Auth::id() ?? \App\Models\User::where('role', 'admin')->value('id'),
            'action' => 'education_update',
            'target_id' => $articleId,
            'details' => 'Memperbarui artikel edukasi: "' . $data['title'] . '"',
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    public function adminEducationDelete(int $articleId): RedirectResponse
    {
        $article = \App\Models\Article::find($articleId);
        $title = $article ? $article->title : 'Artikel #' . $articleId;
        ShareMealState::deleteArticle($articleId);

        \App\Models\AdminLog::create([
            'admin_id' => Auth::id() ?? \App\Models\User::where('role', 'admin')->value('id'),
            'action' => 'education_delete',
            'target_id' => $articleId,
            'details' => 'Menghapus artikel edukasi: "' . $title . '"',
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Artikel berhasil dihapus.');
    }

    public function adminProblemReports(): View
    {
        $reports = \App\Models\ProblemReport::with(['reporter', 'mitra', 'order', 'donation'])
            ->latest()
            ->paginate(15);

        return view('pages.admin.problem_reports', $this->dashboardData('admin', 'Laporan Masalah', 'Moderasi dan tindak lanjut laporan makanan bermasalah') + [
            'reports' => $reports,
        ]);
    }

    public function adminDismissReport(int $reportId): RedirectResponse
    {
        $report = \App\Models\ProblemReport::findOrFail($reportId);
        $report->update(['status' => 'dismissed']);

        \App\Models\AdminLog::create([
            'admin_id' => Auth::id() ?? \App\Models\User::where('role', 'admin')->value('id'),
            'action' => 'report_dismiss',
            'target_id' => $reportId,
            'details' => 'Mengabaikan laporan masalah #' . $reportId . ' (' . $report->issue_label . ')',
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Laporan telah diabaikan.');
    }

    public function adminWarnMitraReport(Request $request, int $reportId): RedirectResponse
    {
        $report = \App\Models\ProblemReport::findOrFail($reportId);
        $mitra = $report->mitra;
        $reason = $request->input('reason') ?: ($report->issue_label . ': ' . $report->description);

        if ($mitra) {
            $mitra->increment('warnings_count');
            $mitra->update([
                'status' => 'warned',
                'last_warning_at' => now(),
                'warning_reason' => $reason,
            ]);

            // Notify Mitra
            $mitra->notify(new \App\Notifications\SystemWarningNotification(
                'Peringatan Akun',
                'Akun Anda mendapatkan peringatan resmi. Alasan: ' . $reason
            ));
        }

        $report->update(['status' => 'resolved', 'admin_note' => 'Diberikan peringatan kepada mitra. Alasan: ' . $reason]);

        \App\Models\AdminLog::create([
            'admin_id' => Auth::id() ?? \App\Models\User::where('role', 'admin')->value('id'),
            'action' => 'report_warn',
            'target_id' => $reportId,
            'details' => 'Menindaklanjuti laporan #' . $reportId . ' dengan memberi peringatan ke Mitra ' . ($mitra ? $mitra->displayName : 'Tidak Diketahui') . '. Alasan: ' . $reason,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Peringatan telah dikirimkan kepada mitra.');
    }

    public function adminBlockMitraReport(Request $request, int $reportId): RedirectResponse
    {
        $report = \App\Models\ProblemReport::findOrFail($reportId);
        $mitra = $report->mitra;
        $reason = $request->input('reason') ?: ('Pelanggaran berat/berulang berdasarkan laporan: ' . $report->issue_label);

        if ($mitra) {
            $mitra->update([
                'status' => 'blocked',
                'blocked_at' => now(),
                'block_reason' => $reason,
            ]);

            // Notify Mitra
            $mitra->notify(new \App\Notifications\SystemWarningNotification(
                'Akun Diblokir',
                'Akun Anda telah dinonaktifkan permanen oleh Admin. Alasan: ' . $reason
            ));
        }

        $report->update(['status' => 'resolved', 'admin_note' => 'Mitra telah diblokir secara permanen. Alasan: ' . $reason]);

        \App\Models\AdminLog::create([
            'admin_id' => Auth::id() ?? \App\Models\User::where('role', 'admin')->value('id'),
            'action' => 'report_block',
            'target_id' => $reportId,
            'details' => 'Menindaklanjuti laporan #' . $reportId . ' dengan memblokir Mitra ' . ($mitra ? $mitra->displayName : 'Tidak Diketahui') . '. Alasan: ' . $reason,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Mitra telah diblokir.');
    }

    public function adminLogs(Request $request): View
    {
        $page = (int) $request->query('page', 1);
        $search = $request->query('search');
        $actionType = $request->query('action_type', 'all');
        $perPage = 15;

        $query = \App\Models\AdminLog::with('admin')->latest();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('details', 'like', "%{$search}%")
                  ->orWhereHas('admin', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($actionType && $actionType !== 'all') {
            if ($actionType === 'verify') {
                $query->whereIn('action', ['verify_approve', 'verify_reject']);
            } elseif ($actionType === 'user') {
                $query->whereIn('action', ['warn_user', 'block_user', 'unblock_user']);
            } elseif ($actionType === 'education') {
                $query->whereIn('action', ['education_create', 'education_update', 'education_delete']);
            } elseif ($actionType === 'report') {
                $query->whereIn('action', ['report_dismiss', 'report_warn', 'report_block']);
            } else {
                $query->where('action', $actionType);
            }
        }

        $totalLogs = $query->count();
        $totalPages = max(1, (int) ceil($totalLogs / $perPage));
        if ($page < 1) { $page = 1; } elseif ($page > $totalPages) { $page = $totalPages; }

        $logs = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return view('pages.admin.logs', $this->dashboardData('admin', 'Log Aktivitas Admin', 'Jejak audit seluruh tindakan moderasi dan administrasi sistem') + [
            'logs' => $logs,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'actionType' => $actionType,
        ]);
    }
}
