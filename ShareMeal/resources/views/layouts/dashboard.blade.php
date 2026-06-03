<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - ShareMeal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        luxury: {
                            ivory: '#F8FAFC',
                            alabas: '#E2E8F0',
                            gold: '#10B981',
                            forest: '#174413',
                            emerald: '#059669',
                            charcoal: '#0F172A',
                            slate: '#475569'
                        }
                    },
                    fontFamily: {
                        serif: ['"Plus Jakarta Sans"', 'sans-serif'],
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        .luxury-shadow { box-shadow: 0 20px 50px -12px rgba(23, 68, 19, 0.04); }
        .glass-panel { background: rgba(255, 255, 255, 0.65); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.35); }
        
        /* Ambient floating blobs */
        @keyframes float-1 {
            0% { transform: translate(0px, 0px) scale(1); }
            50% { transform: translate(25px, -35px) scale(1.05); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        @keyframes float-2 {
            0% { transform: translate(0px, 0px) scale(1); }
            50% { transform: translate(-25px, 25px) scale(0.95); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-float-1 { animation: float-1 25s infinite alternate ease-in-out; }
        .animate-float-2 { animation: float-2 30s infinite alternate ease-in-out; }

        /* Glassmorphism Global Classes */
        .glass-card {
            background: rgba(255, 255, 255, 0.45) !important;
            backdrop-filter: blur(24px) !important;
            -webkit-backdrop-filter: blur(24px) !important;
            border: 1px solid rgba(255, 255, 255, 0.45) !important;
            box-shadow: 0 10px 40px -15px rgba(23, 68, 19, 0.03) !important;
        }
        
        .glass-card-hover {
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        
        .glass-card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -10px rgba(23, 68, 19, 0.06) !important;
            border-color: rgba(23, 68, 19, 0.15) !important;
            background: rgba(255, 255, 255, 0.65) !important;
        }

        /* Scroll Reveal System */
        .reveal {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
        
        .delay-100 { transition-delay: 100ms; }
        .delay-200 { transition-delay: 200ms; }
        .delay-300 { transition-delay: 300ms; }
        .delay-400 { transition-delay: 400ms; }
        .delay-500 { transition-delay: 500ms; }
    </style>
</head>
@php
    $navUser = Auth::user() ?? \App\Models\User::with('profile')->find(session('sharemeal.current_user_id'));
    
    // Determine active menu routes dynamically based on user role or URL prefix
    $routes = [];
    $userRole = $navUser?->role ?? 'consumer';
    
    if ($userRole === 'admin' || request()->is('admin*')) {
        $routes = [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
            ['route' => 'admin.verification', 'label' => 'Verifikasi', 'icon' => 'shield-check'],
            ['route' => 'admin.users', 'label' => 'Kelola User', 'icon' => 'users'],
            ['route' => 'admin.transactions', 'label' => 'Transaksi', 'icon' => 'receipt'],
            ['route' => 'admin.education', 'label' => 'Edukasi', 'icon' => 'book-open'],
            ['route' => 'admin.problem-reports.index', 'label' => 'Laporan Masalah', 'icon' => 'alert-triangle'],
            ['route' => 'admin.reports', 'label' => 'Dampak & Distribusi', 'icon' => 'bar-chart-3'],
        ];
    } elseif ($userRole === 'lembaga' || request()->is('lembaga*')) {
        $routes = [
            ['route' => 'lembaga.dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
            ['route' => 'lembaga.donations', 'label' => 'Donasi', 'icon' => 'heart'],
        ];
    } elseif ($userRole === 'mitra' || request()->is('mitra*')) {
        $routes = [
            ['route' => 'mitra.dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
            ['route' => 'mitra.inventory', 'label' => 'Inventaris', 'icon' => 'package'],
            ['route' => 'mitra.orders', 'label' => 'Pesanan', 'icon' => 'shopping-cart'],
            ['route' => 'mitra.reviews', 'label' => 'Ulasan', 'icon' => 'star'],
            ['route' => 'mitra.donations', 'label' => 'Donasi', 'icon' => 'heart'],
        ];
    } else {
        $routes = [
            ['route' => 'consumer.dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
            ['route' => 'consumer.search', 'label' => 'Cari Makanan', 'icon' => 'search'],
            ['route' => 'consumer.history', 'label' => 'Riwayat', 'icon' => 'history'],
            ['route' => 'consumer.education', 'label' => 'Edukasi', 'icon' => 'book-open'],
        ];
    }
@endphp
<body class="bg-luxury-ivory min-h-screen font-sans text-luxury-charcoal relative overflow-x-hidden" x-data="{ mobileMenuOpen: false }">
    <!-- Ambient Fixed Background Blobs -->
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
        <div class="absolute top-[15%] left-[-10%] w-[38rem] h-[38rem] bg-emerald-250/20 rounded-full blur-[130px] animate-float-1"></div>
        <div class="absolute bottom-[15%] right-[-10%] w-[35rem] h-[35rem] bg-lime-100/15 rounded-full blur-[120px] animate-float-2"></div>
    </div>

    <!-- PBI #45: Critical Notification Banner -->
    @if($navUser)
        @php
            $criticalAlerts = session('critical_alerts', []);
            if ($navUser->status === 'warned') {
                $criticalAlerts[] = [
                    'type' => 'warning',
                    'message' => 'Peringatan: Akun Anda mendapatkan peringatan karena pelanggaran kebijakan. Mohon patuhi aturan platform.',
                    'link' => route('mitra.profile'),
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
            @if(($alert['type'] ?? '') === 'warning')
            <div x-data="{ dismissed: localStorage.getItem('warning_banner_dismissed_{{ Auth::id() }}') === 'true' }"
                 x-show="!dismissed"
                 class="bg-orange-600 text-white px-4 py-2 text-center text-xs font-bold flex items-center justify-center gap-2 sticky top-0 z-[60] animate-in slide-in-from-top duration-300"
                 x-cloak>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
                <span>{{ $alert['message'] }}</span>
                @if(isset($alert['link']))
                    <a href="{{ $alert['link'] }}" 
                       @click="localStorage.setItem('warning_banner_dismissed_{{ Auth::id() }}', 'true'); dismissed = true;" 
                       class="underline ml-2 hover:text-orange-100 transition-colors">{{ $alert['link_text'] ?? 'Detail' }}</a>
                @endif
            </div>
            @elseif(($alert['type'] ?? '') === 'danger')
            <div class="bg-red-600 text-white px-4 py-2 text-center text-xs font-bold flex items-center justify-center gap-2 sticky top-0 z-[60] animate-in slide-in-from-top duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="9.5" x2="14.5" y1="14.5" y2="9.5"/><line x1="14.5" x2="9.5" y1="14.5" y2="9.5"/></svg>
                <span>{{ $alert['message'] }}</span>
                @if(isset($alert['action']))
                    <button class="bg-white text-red-600 px-2 py-0.5 rounded ml-2 uppercase text-[10px]">{{ $alert['action'] }}</button>
                @endif
            </div>
            @endif
        @endforeach
    @endif

    <!-- Fixed Absolute Left Sidebar (Visible only on Desktop) -->
    <aside class="fixed top-0 left-0 h-screen w-72 bg-white/45 backdrop-blur-2xl border-r border-luxury-alabas/85 z-40 hidden lg:flex flex-col py-8 px-6 shadow-[10px_0_30px_-15px_rgba(15,45,24,0.03)] justify-between">
        <div>
            <!-- Brand Logo -->
            <a href="{{ url('/') }}" class="flex items-center gap-3 group mb-10 px-4">
                <div class="w-10 h-10 bg-gradient-to-tr from-[#174413] to-emerald-700 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-105 shadow-md shadow-emerald-900/10">
                    <i data-lucide="leaf" class="w-6 h-6 text-luxury-gold"></i>
                </div>
                <span class="text-2xl font-bold tracking-tight text-[#174413]">ShareMeal</span>
            </a>

            <!-- Menu Header -->
            <div class="mb-4 px-4 flex items-center justify-between">
                <span class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.2em]">Menu Utama</span>
                <div class="h-0.5 w-6 bg-luxury-gold/50 rounded-full"></div>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-2.5">
                @foreach($routes as $item)
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-500 group {{ request()->routeIs($item['route']) ? 'bg-luxury-forest text-white shadow-lg shadow-emerald-950/10 translate-x-1' : 'text-luxury-slate hover:bg-white/70 hover:text-luxury-forest hover:translate-x-1' }}">
                        <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 {{ request()->routeIs($item['route']) ? 'text-luxury-gold' : 'group-hover:text-luxury-gold' }} transition-colors duration-500 stroke-[2]"></i>
                        <span class="text-sm font-bold tracking-wide">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <!-- Impact Score Gamification Card -->
        <div class="mt-auto p-6 bg-gradient-to-br from-[#12360f] to-[#1c5317] rounded-3xl relative overflow-hidden shadow-lg shadow-emerald-950/15 border border-white/5">
            <div class="relative z-10">
                <div class="text-luxury-gold text-xs font-black uppercase tracking-widest mb-1.5">Impact Score</div>
                <div class="text-2xl font-bold text-white mb-3 leading-none">6.5kg CO₂</div>
                <div class="h-1.5 w-full bg-white/10 rounded-full overflow-hidden">
                    <div class="h-full bg-luxury-gold rounded-full w-[65%]"></div>
                </div>
            </div>
            <i data-lucide="leaf" class="absolute -bottom-4 -right-4 w-24 h-24 text-white/5 rotate-12"></i>
        </div>
    </aside>

    <!-- Main Workspace Container (Padded left to accommodate desktop sidebar) -->
    <div class="lg:pl-72 min-h-screen flex flex-col relative z-10">
        
        <!-- Top Navigation Navbar -->
        <nav class="bg-white/45 backdrop-blur-xl border-b border-luxury-alabas/85 sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12">
                <div class="flex justify-between items-center h-20">
                    
                    <!-- Mobile Logo (visible only on mobile) -->
                    <a href="{{ url('/') }}" class="flex lg:hidden items-center gap-3 group">
                        <div class="w-10 h-10 bg-gradient-to-tr from-[#174413] to-emerald-700 rounded-xl flex items-center justify-center shadow-md">
                            <i data-lucide="leaf" class="w-5 h-5 text-luxury-gold"></i>
                        </div>
                        <span class="text-xl font-bold tracking-tight text-[#174413]">ShareMeal</span>
                    </a>

                    <!-- Spacer on desktop so notifications stay on the right -->
                    <div class="hidden lg:block"></div>

                    <div class="flex items-center gap-6">
                        <!-- Notifications Dropdown -->
                        @php
                            $dynamicInfoNotifications = [];
                            foreach (session('critical_alerts', []) as $alert) {
                                if (($alert['type'] ?? '') === 'info') {
                                    $dynamicInfoNotifications[] = $alert;
                                }
                            }
                        @endphp
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="relative p-2.5 text-luxury-slate hover:text-luxury-forest hover:bg-white/80 rounded-full transition-all duration-300 focus:outline-none">
                                <i data-lucide="bell" class="w-6 h-6 stroke-[1.5]"></i>
                                @if(Auth::check() && (Auth::user()->unreadNotifications->count() > 0 || count($dynamicInfoNotifications) > 0))
                                    <span class="absolute top-2 right-2 block h-2.5 w-2.5 rounded-full bg-luxury-gold ring-4 ring-white"></span>
                                @endif
                            </button>

                            <div x-show="open"
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="transform opacity-0 translate-y-4 scale-95"
                                 x-transition:enter-end="transform opacity-100 translate-y-0 scale-100"
                                 class="absolute right-0 mt-4 w-96 bg-white/95 backdrop-blur-xl rounded-[2rem] border border-luxury-alabas luxury-shadow py-4 z-50 overflow-hidden"
                                 x-cloak>
                                <div class="px-6 py-4 border-b border-luxury-alabas flex justify-between items-center bg-white/80">
                                    <h3 class="font-serif text-xl font-bold text-luxury-forest">Notifikasi</h3>
                                    @if(Auth::check() && Auth::user()->unreadNotifications->count() > 0)
                                        <form method="POST" action="{{ route('notifications.markRead') }}">
                                            @csrf
                                            <button type="submit" class="text-xs text-luxury-gold font-bold uppercase tracking-widest hover:text-luxury-forest transition-colors">Tandai Dibaca</button>
                                        </form>
                                    @endif
                                </div>
                                <div class="max-h-[32rem] overflow-y-auto custom-scrollbar bg-white/50">
                                    @if(Auth::check())
                                        @php
                                            $dbNotifications = Auth::user()->notifications()->latest()->take(5)->get();
                                        @endphp
                                        @if(count($dynamicInfoNotifications) > 0 || $dbNotifications->count() > 0)
                                            @foreach($dynamicInfoNotifications as $infoAlert)
                                                <div class="px-6 py-5 hover:bg-white/90 transition-colors border-b border-luxury-alabas last:border-0 bg-luxury-gold/10">
                                                    <div class="flex gap-4">
                                                        <div class="mt-1">
                                                            <div class="bg-luxury-gold/10 p-2 rounded-xl">
                                                                <i data-lucide="info" class="w-4 h-4 text-luxury-gold"></i>
                                                            </div>
                                                        </div>
                                                        <div class="flex-1">
                                                            <div class="text-sm font-bold text-luxury-charcoal">Status Klaim Donasi</div>
                                                            <div class="text-xs text-luxury-slate mt-1 leading-relaxed">{{ $infoAlert['message'] }}</div>
                                                            @if(isset($infoAlert['link']))
                                                                <a href="{{ $infoAlert['link'] }}" class="inline-block text-xs text-luxury-gold hover:text-luxury-forest font-semibold mt-2 underline">
                                                                    {{ $infoAlert['link_text'] ?? 'Lihat Detail' }}
                                                                </a>
                                                            @endif
                                                            <div class="text-[10px] text-luxury-gold mt-2 uppercase font-bold tracking-widest">Saat Ini</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach

                                            @foreach($dbNotifications as $notification)
                                                <div class="px-6 py-5 hover:bg-white/90 transition-colors border-b border-luxury-alabas last:border-0 {{ $notification->unread() ? 'bg-luxury-gold/10' : '' }}">
                                                    <div class="flex gap-4">
                                                        <div class="mt-1">
                                                            @if(($notification->data['status'] ?? '') == 'completed')
                                                                <div class="bg-luxury-forest/10 p-2 rounded-xl">
                                                                    <i data-lucide="check-circle" class="w-4 h-4 text-luxury-forest"></i>
                                                                </div>
                                                            @elseif(($notification->data['status'] ?? '') == 'cancelled')
                                                                <div class="bg-red-50 p-2 rounded-xl">
                                                                    <i data-lucide="x-circle" class="w-4 h-4 text-red-600"></i>
                                                                </div>
                                                            @else
                                                                <div class="bg-luxury-gold/10 p-2 rounded-xl">
                                                                    <i data-lucide="info" class="w-4 h-4 text-luxury-gold"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="flex-1">
                                                            <div class="text-sm font-bold text-luxury-charcoal">{{ $notification->data['title'] ?? 'Notifikasi' }}</div>
                                                            <div class="text-xs text-luxury-slate mt-1 leading-relaxed">{{ $notification->data['message'] ?? '' }}</div>
                                                            <div class="text-[10px] text-luxury-gold mt-2 uppercase font-bold tracking-widest">{{ $notification->created_at->diffForHumans() }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="px-6 py-12 text-center">
                                                <div class="bg-luxury-ivory w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border border-luxury-alabas">
                                                    <i data-lucide="bell-off" class="w-8 h-8 text-luxury-alabas"></i>
                                                </div>
                                                <p class="text-sm text-luxury-slate font-medium italic">Belum ada notifikasi baru</p>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                                <div class="px-6 py-4 border-t border-luxury-alabas text-center bg-white/80">
                                    <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-luxury-forest hover:text-luxury-gold transition-colors uppercase tracking-widest">Lihat Semua</a>
                                </div>
                            </div>
                        </div>

                        <!-- User Profile Dropdown -->
                        @if($navUser)
                            <div class="relative" x-data="{ open: false }">
                                <button type="button"
                                        @click="open = !open"
                                        class="flex items-center gap-4 rounded-2xl border border-luxury-alabas bg-white/60 p-1.5 pr-4 hover:bg-white focus:outline-none transition-all duration-300"
                                        :aria-expanded="open.toString()">
                                    <div class="relative">
                                        <img src="{{ $navUser->image }}" alt="Foto profil {{ $navUser->displayName }}" class="h-10 w-10 rounded-[0.8rem] object-cover border border-luxury-alabas">
                                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-luxury-forest border-2 ring-white rounded-full"></div>
                                    </div>
                                    <span class="hidden md:block text-left">
                                        <span class="block text-sm font-bold text-luxury-forest leading-tight">{{ $navUser->displayName }}</span>
                                        <span class="block text-[10px] text-luxury-gold uppercase font-black tracking-tighter leading-tight">{{ $navUser->role }}</span>
                                    </span>
                                    <i data-lucide="chevron-down" class="hidden md:block w-4 h-4 text-luxury-alabas transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                                </button>

                                <div x-show="open"
                                     @click.away="open = false"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="transform opacity-0 translate-y-4 scale-95"
                                     x-transition:enter-end="transform opacity-100 translate-y-0 scale-100"
                                     class="absolute right-0 mt-4 w-72 overflow-hidden rounded-[2rem] glass-panel luxury-shadow z-50"
                                     x-cloak>
                                    <div class="px-6 py-6 border-b border-luxury-alabas bg-luxury-forest/5">
                                        <div class="flex items-center gap-4">
                                            <img src="{{ $navUser->image }}" alt="Foto profil {{ $navUser->displayName }}" class="h-12 w-12 rounded-[1rem] object-cover border-2 border-white shadow-sm">
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-bold text-luxury-forest">{{ $navUser->displayName }}</div>
                                                <div class="truncate text-[11px] text-luxury-slate font-medium">{{ $navUser->email }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="py-3 px-3">
                                        <a href="{{ $navUser->role === 'mitra' ? route('mitra.profile') : route('profile.edit') }}" class="flex items-center gap-4 px-4 py-3 text-sm font-bold text-luxury-slate hover:bg-luxury-forest hover:text-white rounded-2xl transition-all duration-300">
                                            <i data-lucide="{{ $navUser->role === 'mitra' ? 'store' : 'user' }}" class="w-4 h-4 stroke-[2.5]"></i>
                                            {{ $navUser->role === 'mitra' ? 'Pengaturan Profil Usaha' : 'Profil Saya' }}
                                        </a>
                                        <div class="h-px bg-luxury-alabas my-2 mx-4"></div>
                                        <form method="POST" action="{{ route('logout') }}" id="logout-form-desktop">
                                            @csrf
                                            <button type="submit" class="flex w-full items-center gap-4 px-4 py-3 text-left text-sm font-bold text-red-500 hover:bg-red-50 rounded-2xl transition-all duration-300">
                                                <i data-lucide="log-out" class="w-4 h-4 stroke-[2.5]"></i>
                                                Keluar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Mobile Menu Button toggler -->
                        <button class="lg:hidden text-luxury-forest p-2.5 bg-white/60 border border-luxury-alabas/80 rounded-xl" @click="mobileMenuOpen = !mobileMenuOpen">
                            <i x-show="!mobileMenuOpen" data-lucide="menu" class="w-6 h-6"></i>
                            <i x-show="mobileMenuOpen" data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Mobile Side Navigation Drawer (visible only on mobile) -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="lg:hidden fixed inset-0 bg-luxury-forest/60 backdrop-blur-sm z-[70]" 
             @click="mobileMenuOpen = false" 
             x-cloak>
            <div class="bg-white w-72 h-full p-8 luxury-shadow animate-in slide-in-from-left duration-500 flex flex-col justify-between" @click.stop>
                <div>
                    <div class="mb-10 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <i data-lucide="leaf" class="w-6 h-6 text-luxury-forest"></i>
                            <span class="font-serif text-xl font-bold text-luxury-forest">ShareMeal</span>
                        </div>
                        <button @click="mobileMenuOpen = false" class="text-luxury-slate hover:text-luxury-forest p-2 bg-luxury-ivory rounded-full transition-all">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    @if(Auth::check())
                        <div class="mb-10 p-4 bg-luxury-ivory rounded-2xl border border-luxury-alabas">
                            <div class="flex items-center gap-3">
                                <img src="{{ Auth::user()->image }}" class="h-10 w-10 rounded-xl object-cover">
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-luxury-forest truncate">{{ Auth::user()->displayName }}</div>
                                    <div class="text-[10px] text-luxury-gold uppercase font-black tracking-widest">{{ Auth::user()->role }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <nav class="space-y-2">
                        @foreach($routes as $item)
                            <a href="{{ route($item['route']) }}" 
                               class="flex items-center gap-4 px-4 py-4 rounded-xl transition-all {{ request()->routeIs($item['route']) ? 'bg-luxury-forest text-white' : 'text-luxury-slate hover:bg-luxury-ivory' }}">
                                <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 {{ request()->routeIs($item['route']) ? 'text-luxury-gold' : '' }}"></i>
                                <span class="text-sm font-bold">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="mt-auto pt-8">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center justify-center gap-3 px-4 py-4 text-red-500 font-bold border-2 border-red-50 hover:bg-red-55 rounded-xl transition-all">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <main class="flex-1 px-6 sm:px-8 lg:px-12 py-12 max-w-7xl w-full mx-auto relative z-10 animate-in fade-in duration-700">
            @yield('content')
        </main>
    </div>

    <!-- PBI #45: Toast Notification System -->
    <div x-data="{ 
            notifications: [],
            add(n) {
                const id = Date.now();
                this.notifications.push({ id, ...n });
                setTimeout(() => {
                    this.notifications = this.notifications.filter(item => item.id !== id);
                }, 5000);
            }
         }" 
         @notify.window="add($event.detail)"
         class="fixed top-6 right-6 z-[100] space-y-4 w-96">
        <template x-for="n in notifications" :key="n.id">
            <div x-show="true"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="translate-x-12 opacity-0 scale-95"
                 x-transition:enter-end="translate-x-0 opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0 translate-x-4"
                 class="glass-panel rounded-[1.5rem] luxury-shadow p-5 flex items-start gap-4 ring-1 ring-white/50 relative overflow-hidden group cursor-pointer"
                 @click="notifications = notifications.filter(item => item.id !== n.id)">
                
                <!-- Accent Line -->
                <div class="absolute left-0 top-0 bottom-0 w-1"
                     :class="{
                        'bg-luxury-forest': n.type === 'success',
                        'bg-red-500': n.type === 'error',
                        'bg-luxury-gold': n.type === 'warning',
                        'bg-luxury-emerald': !n.type || n.type === 'info'
                     }"></div>

                <div class="mt-0.5">
                    <template x-if="n.type === 'success'">
                        <div class="bg-luxury-forest/10 p-2 rounded-xl text-luxury-forest">
                            <i data-lucide="check-circle" class="w-5 h-5 stroke-[2.5]"></i>
                        </div>
                    </template>
                    <template x-if="n.type === 'error'">
                        <div class="bg-red-55 p-2 rounded-xl text-red-500">
                            <i data-lucide="x-circle" class="w-5 h-5 stroke-[2.5]"></i>
                        </div>
                    </template>
                    <template x-if="n.type === 'warning'">
                        <div class="bg-luxury-gold/10 p-2 rounded-xl text-luxury-gold">
                            <i data-lucide="alert-triangle" class="w-5 h-5 stroke-[2.5]"></i>
                        </div>
                    </template>
                    <template x-if="!n.type || n.type === 'info'">
                        <div class="bg-luxury-emerald/10 p-2 rounded-xl text-luxury-emerald">
                            <i data-lucide="bell" class="w-5 h-5 stroke-[2.5]"></i>
                        </div>
                    </template>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-black text-luxury-charcoal" x-text="n.title"></div>
                    <div class="text-xs text-luxury-slate font-medium mt-1 leading-relaxed line-clamp-2" x-text="n.message"></div>
                </div>
                <button class="text-luxury-alabas group-hover:text-luxury-slate transition-colors">
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
        @if(session('error_different_store'))
            window.dispatchEvent(new CustomEvent('notify', { detail: { title: 'Keranjang Terisi', message: '{{ session('error_different_store') }}', type: 'error' } }));
        @endif

        // Global Intersection Observer for scroll reveals
        document.addEventListener('DOMContentLoaded', () => {
            const observerOptions = {
                root: null,
                rootMargin: '0px 0px -40px 0px',
                threshold: 0.05
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            const revealElements = document.querySelectorAll('.reveal');
            revealElements.forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>
