<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ShareMeal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-manrope { font-family: 'Manrope', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="{ mobileMenuOpen: false }">
    <!-- Top Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <span class="text-xl font-bold" style="color: #174413;">ShareMeal</span>
                </a>

                <div class="flex items-center gap-4">
                    <div class="hidden md:block text-right">
                        @if(Auth::check())
                            <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-gray-500 capitalize">{{ Auth::user()->role }}</div>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('logout') }}" id="logout-form-desktop" class="hidden md:flex">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 border border-gray-300 px-3 py-1.5 rounded-md text-sm font-medium hover:bg-gray-50 transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            Keluar
                        </button>
                    </form>
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
                            <a href="{{ route('admin.education') }}" 
                               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.education') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="book-open" class="w-5 h-5"></i>
                                <span>Edukasi</span>
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
                            <a href="{{ route('admin.education') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.education') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <i data-lucide="book-open" class="w-5 h-5"></i><span>Edukasi</span>
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

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();
    </script>
</body>
</html>
