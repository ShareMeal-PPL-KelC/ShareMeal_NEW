<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - ShareMeal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-manrope { font-family: 'Manrope', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
@php
    $navUser = Auth::user() ?? \App\Models\User::with('profile')->find(session('sharemeal.current_user_id'));
@endphp
<body class="bg-gray-50 min-h-screen" x-data="{ mobileMenuOpen: false }">
    <!-- PBI #45: Critical Notification Banner -->
    @if($navUser)
        @php
            $criticalAlerts = session('critical_alerts', []);
            if ($navUser->status === 'warned') {
                $criticalAlerts[] = [
                    'type' => 'warning',
                    'message' => 'Peringatan: Akun Anda mendapatkan peringatan karena pelanggaran kebijakan. Mohon patuhi aturan platform.',
                    'link' => '#',
                    'link_text' => 'Pelajari Selengkapnya'
                ];
            }
            if ($navUser->status === 'blocked') {
                $criticalAlerts[] = [
                    'type' => 'danger',
                    'message' => 'AKSES DIBATASI: Akun Anda telah diblokir. Silakan hubungi dukungan untuk informasi lebih lanjut.',
                    'action' => 'Banding'
                ];
            }
        @endphp

        @foreach($criticalAlerts as $alert)
            <div class="{{ ($alert['type'] ?? '') === 'danger' ? 'bg-red-600' : 'bg-orange-600' }} text-white px-4 py-2 text-center text-xs font-bold flex items-center justify-center gap-2 sticky top-0 z-[60] animate-in slide-in-from-top duration-300">
                <i data-lucide="{{ ($alert['type'] ?? '') === 'danger' ? 'shield-off' : 'alert-circle' }}" class="w-4 h-4"></i>
                <span>{{ $alert['message'] }}</span>
                @if(isset($alert['link']))
                    <a href="{{ $alert['link'] }}" class="underline ml-2">{{ $alert['link_text'] ?? 'Detail' }}</a>
                @endif
                @if(isset($alert['action']))
                    <button class="bg-white {{ ($alert['type'] ?? '') === 'danger' ? 'text-red-600' : 'text-orange-600' }} px-2 py-0.5 rounded ml-2 uppercase text-[10px]">{{ $alert['action'] }}</button>
                @endif
            </div>
        @endforeach
    @endif

    <!-- Top Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <span class="text-xl font-bold" style="color: #174413;">ShareMeal</span>
                </a>

                <div class="flex items-center gap-4">
                    <!-- Favorite Stores -->
                    {{-- <a href="{{ route('consumer.favorites') }}" class="relative p-2 text-gray-400 hover:text-red-500 transition-colors group">
                        <i data-lucide="heart" class="w-6 h-6 group-hover:fill-red-500 group-hover:text-red-500 transition-all duration-300"></i>
                    </a> --}}

                    <!-- Notifications Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="relative p-2 text-gray-400 hover:text-gray-500 transition-colors focus:outline-none">
                            <i data-lucide="bell" class="w-6 h-6"></i>
                            @if(Auth::check() && Auth::user()->unreadNotifications->count() > 0)
                                <span class="absolute top-1 right-1 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
                            @endif
                        </button>

                        <div x-show="open"
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50"
                             x-cloak>
                            <div class="px-4 py-2 border-b border-gray-50 flex justify-between items-center">
                                <h3 class="font-bold text-gray-900">Notifikasi</h3>
                                @if(Auth::check() && Auth::user()->unreadNotifications->count() > 0)
                                    <form method="POST" action="{{ route('notifications.markRead') }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-green-600 font-semibold hover:text-green-700">Tandai semua dibaca</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                @if(Auth::check())
                                    @forelse(Auth::user()->notifications()->latest()->take(5)->get() as $notification)
                                        <div class="px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0 {{ $notification->unread() ? 'bg-blue-50/30' : '' }}">
                                            <div class="flex gap-3">
                                                <div class="mt-1">
                                                    @if(($notification->data['status'] ?? '') == 'completed')
                                                        <div class="bg-green-100 p-1.5 rounded-full">
                                                            <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                                                        </div>
                                                    @elseif(($notification->data['status'] ?? '') == 'cancelled')
                                                        <div class="bg-red-100 p-1.5 rounded-full">
                                                            <i data-lucide="x-circle" class="w-4 h-4 text-red-600"></i>
                                                        </div>
                                                    @else
                                                        <div class="bg-blue-100 p-1.5 rounded-full">
                                                            <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-bold text-gray-900">{{ $notification->data['title'] ?? 'Notifikasi' }}</div>
                                                    <div class="text-xs text-gray-600 mt-0.5">{{ $notification->data['message'] ?? '' }}</div>
                                                    <div class="text-[10px] text-gray-400 mt-1 uppercase font-medium">{{ $notification->created_at->diffForHumans() }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="px-4 py-8 text-center">
                                            <div class="bg-gray-50 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <i data-lucide="bell-off" class="w-6 h-6 text-gray-300"></i>
                                            </div>
                                            <p class="text-sm text-gray-500">Belum ada notifikasi baru</p>
                                        </div>
                                    @endforelse
                                @endif
                            </div>
                            <div class="px-4 py-2 border-t border-gray-50 text-center">
                                <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-gray-500 hover:text-gray-900 transition-colors">Lihat Semua Notifikasi</a>
                            </div>
                        </div>
                    </div>

                    @if($navUser)
                        <div class="relative" x-data="{ open: false }">
                            <button type="button"
                                    @click="open = !open"
                                    class="flex items-center gap-3 rounded-full border border-gray-200 bg-white py-1 pl-1 pr-2 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-100 transition-colors"
                                    :aria-expanded="open.toString()">
                                <img src="{{ $navUser->image }}" alt="Foto profil {{ $navUser->name }}" class="h-9 w-9 rounded-full object-cover border border-green-100">
                                <span class="hidden md:block text-right">
                                    <span class="block text-sm font-medium text-gray-900 leading-tight">{{ $navUser->name }}</span>
                                    <span class="block text-xs text-gray-500 capitalize leading-tight">{{ $navUser->role }}</span>
                                </span>
                                <i data-lucide="chevron-down" class="hidden md:block w-4 h-4 text-gray-400"></i>
                            </button>

                            <div x-show="open"
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-64 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-lg z-50"
                                 x-cloak>
                                <div class="px-4 py-4 border-b border-gray-50">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $navUser->image }}" alt="Foto profil {{ $navUser->name }}" class="h-11 w-11 rounded-full object-cover border border-green-100">
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-bold text-gray-900">{{ $navUser->name }}</div>
                                            <div class="truncate text-xs text-gray-500">{{ $navUser->email }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="py-2">
                                    <a href="{{ $navUser->role === 'mitra' ? route('mitra.profile') : route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-green-700 transition-colors">
                                        <i data-lucide="{{ $navUser->role === 'mitra' ? 'store' : 'user-round-cog' }}" class="w-4 h-4"></i>
                                        {{ $navUser->role === 'mitra' ? 'Pengaturan Profil Usaha' : 'Pengaturan Profil' }}
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}" id="logout-form-desktop">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                                            <i data-lucide="log-out" class="w-4 h-4"></i>
                                            Keluar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                    <button class="md:hidden text-gray-600" @click="mobileMenuOpen = !mobileMenuOpen">
                        <i x-show="!mobileMenuOpen" data-lucide="menu" class="w-6 h-6"></i>
                        <i x-show="mobileMenuOpen" data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar - Desktop -->
            <aside class="hidden md:block w-64 flex-shrink-0">
                <div class="bg-white rounded-lg shadow-sm p-4 sticky top-24 border border-gray-100">
                    <nav class="space-y-2">
                        @if(request()->is('admin*'))
                            <a href="{{ route('admin.dashboard') }}"
                               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="{{ route('admin.verification') }}"
                               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.verification') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="shield" class="w-5 h-5"></i>
                                <span>Verifikasi</span>
                            </a>
                            <a href="{{ route('admin.users') }}"
                               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.users') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="users" class="w-5 h-5"></i>
                                <span>Kelola User</span>
                            </a>
                            <a href="{{ route('admin.transactions') }}"
                               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.transactions') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="receipt" class="w-5 h-5"></i>
                                <span>Transaksi</span>
                            </a>
                            <a href="{{ route('admin.education') }}"
                               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.education') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="book-open" class="w-5 h-5"></i>
                                <span>Edukasi</span>
                            </a>
                            <a href="{{ route('admin.problem-reports.index') }}"
                               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.problem-reports.*') ? 'bg-red-50 text-red-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                                <span>Laporan Masalah</span>
                            </a>
                        @elseif(request()->is('lembaga*'))
                            <a href="{{ route('lembaga.dashboard') }}"
                               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('lembaga.dashboard') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="{{ route('lembaga.donations') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('lembaga.donations') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="heart" class="w-5 h-5"></i>
                                <span>Donasi</span>
                            </a>
                        @elseif(request()->is('mitra*'))
                            <a href="{{ route('mitra.dashboard') }}"
                               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('mitra.dashboard') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="{{ route('mitra.inventory') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('mitra.inventory') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="package" class="w-5 h-5"></i>
                                <span>Inventaris</span>
                            </a>
                            <a href="{{ route('mitra.orders') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('mitra.orders') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                <span>Pesanan</span>
                            </a>
                            <a href="{{ route('mitra.reviews') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('mitra.reviews') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="star" class="w-5 h-5"></i>
                                <span>Ulasan</span>
                            </a>
                            <a href="{{ route('mitra.donations') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('mitra.donations') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="heart" class="w-5 h-5"></i>
                                <span>Donasi</span>
                            </a>
                        @else
                            <a href="{{ route('consumer.dashboard') }}"
                               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('consumer.dashboard') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="{{ route('consumer.search') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('consumer.search') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="search" class="w-5 h-5"></i>
                                <span>Cari Makanan</span>
                            </a>
                            <a href="{{ route('consumer.history') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('consumer.history') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="history" class="w-5 h-5"></i>
                                <span>Riwayat</span>
                            </a>
                            <a href="{{ route('consumer.education') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('consumer.education') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="book-open" class="w-5 h-5"></i>
                                <span>Edukasi</span>
                            </a>
                        @endif
                    </nav>
                </div>
            </aside>

            <!-- Mobile Menu Overlay -->
            <div x-show="mobileMenuOpen" class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-40" @click="mobileMenuOpen = false" x-cloak>
                <div class="bg-white w-64 h-full p-4" @click.stop>
                    <div class="mb-6 flex justify-between items-center">
                        @if(Auth::check())
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-500 capitalize">{{ Auth::user()->role }}</div>
                            </div>
                        @endif
                        <button @click="mobileMenuOpen = false" class="text-gray-400 hover:text-gray-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <nav class="space-y-2">
                        @if(request()->is('admin*'))
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="layout-dashboard" class="w-5 h-5"></i><span>Dashboard</span>
                            </a>
                            <a href="{{ route('admin.verification') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.verification') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="shield" class="w-5 h-5"></i><span>Verifikasi</span>
                            </a>
                            <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.users') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="users" class="w-5 h-5"></i><span>Kelola User</span>
                            </a>
                            <a href="{{ route('admin.transactions') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.transactions') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="receipt" class="w-5 h-5"></i><span>Transaksi</span>
                            </a>
                            <a href="{{ route('admin.education') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.education') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="book-open" class="w-5 h-5"></i><span>Edukasi</span>
                            </a>
                            <a href="{{ route('admin.problem-reports.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.problem-reports.*') ? 'bg-red-50 text-red-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="alert-triangle" class="w-5 h-5"></i><span>Laporan Masalah</span>
                            </a>
                        @elseif(request()->is('lembaga*'))
                            <a href="{{ route('lembaga.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('lembaga.dashboard') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="layout-dashboard" class="w-5 h-5"></i><span>Dashboard</span>
                            </a>
                            <a href="{{ route('lembaga.donations') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('lembaga.donations') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="heart" class="w-5 h-5"></i><span>Donasi</span>
                            </a>
                        @elseif(request()->is('mitra*'))
                            <a href="{{ route('mitra.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('mitra.dashboard') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="layout-dashboard" class="w-5 h-5"></i><span>Dashboard</span>
                            </a>
                            <a href="{{ route('mitra.inventory') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('mitra.inventory') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="package" class="w-5 h-5"></i><span>Inventaris</span>
                            </a>
                            <a href="{{ route('mitra.orders') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('mitra.orders') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="shopping-cart" class="w-5 h-5"></i><span>Pesanan</span>
                            </a>
                            <a href="{{ route('mitra.reviews') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('mitra.reviews') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="star" class="w-5 h-5"></i><span>Ulasan</span>
                            </a>
                            <a href="{{ route('mitra.donations') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('mitra.donations') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="heart" class="w-5 h-5"></i><span>Donasi</span>
                            </a>
                        @else
                            <a href="{{ route('consumer.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('consumer.dashboard') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="layout-dashboard" class="w-5 h-5"></i><span>Dashboard</span>
                            </a>
                            <a href="{{ route('consumer.search') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('consumer.search') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="search" class="w-5 h-5"></i><span>Cari Makanan</span>
                            </a>
                            <a href="{{ route('consumer.history') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('consumer.history') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="history" class="w-5 h-5"></i><span>Riwayat</span>
                            </a>
                            <a href="{{ route('consumer.education') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('consumer.education') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="book-open" class="w-5 h-5"></i><span>Edukasi</span>
                            </a>
                        @endif
                    </nav>
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <form method="POST" action="{{ route('logout') }}" id="logout-form-mobile">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 px-4 py-3 text-red-600 font-medium hover:bg-red-50 rounded-lg transition-colors">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="flex-1">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- PBI #45: Toast Notification System -->
    <div x-data="{ 
            notifications: [],
            add(n) {
                this.notifications.push({ id: Date.now(), ...n });
                setTimeout(() => {
                    this.notifications = this.notifications.filter(item => item.id !== n.id);
                }, 5000);
            }
         }" 
         @notify.window="notifications.push({ id: Date.now(), ...$event.detail })"
         class="fixed bottom-6 right-6 z-[100] space-y-3 w-80">
        <template x-for="n in notifications" :key="n.id">
            <div x-show="true"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-4 opacity-0 scale-95"
                 x-transition:enter-end="translate-y-0 opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-2xl shadow-2xl border border-gray-100 p-4 flex items-start gap-4 ring-1 ring-black/5"
                 @click="notifications = notifications.filter(item => item.id !== n.id)">
                <div class="mt-0.5">
                    <template x-if="n.type === 'success'">
                        <div class="bg-green-100 p-2 rounded-xl text-green-600">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                        </div>
                    </template>
                    <template x-if="n.type === 'error'">
                        <div class="bg-red-100 p-2 rounded-xl text-red-600">
                            <i data-lucide="x-circle" class="w-5 h-5"></i>
                        </div>
                    </template>
                    <template x-if="n.type === 'warning'">
                        <div class="bg-orange-100 p-2 rounded-xl text-orange-600">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                    </template>
                    <template x-if="!n.type || n.type === 'info'">
                        <div class="bg-blue-100 p-2 rounded-xl text-blue-600">
                            <i data-lucide="bell" class="w-5 h-5"></i>
                        </div>
                    </template>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-black text-gray-900" x-text="n.title"></div>
                    <div class="text-xs text-gray-500 font-medium mt-0.5 line-clamp-2" x-text="n.message"></div>
                </div>
                <button class="text-gray-300 hover:text-gray-500 transition-colors">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </template>
    </div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // PBI #45: Trigger session messages as toasts
        @if(session('success'))
            window.dispatchEvent(new CustomEvent('notify', { detail: { title: 'Berhasil', message: '{{ session('success') }}', type: 'success' } }));
        @endif
        @if(session('error'))
            window.dispatchEvent(new CustomEvent('notify', { detail: { title: 'Terjadi Kesalahan', message: '{{ session('error') }}', type: 'error' } }));
        @endif
    </script>
</body>
</html>
