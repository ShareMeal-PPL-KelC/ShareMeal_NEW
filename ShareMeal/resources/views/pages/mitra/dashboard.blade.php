@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Mitra</h1>
            <p class="text-gray-600 mt-1">Kelola surplus pangan dan kurangi food waste</p>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500 bg-white px-4 py-2 rounded-lg border border-gray-100 shadow-sm">
            <i data-lucide="user" class="w-4 h-4"></i>
            <span>{{ $shell['userName'] }}</span>
        </div>
    </div>


    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-gray-50 rounded-lg">
                    <i data-lucide="package" class="w-5 h-5 text-gray-600"></i>
                </div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Produk</span>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $stats->totalProducts }}</div>
            <p class="text-sm text-gray-500 mt-1">Total produk dalam inventaris</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-orange-50 rounded-lg">
                    <i data-lucide="trending-down" class="w-5 h-5 text-orange-600"></i>
                </div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Promo</span>
            </div>
            <div class="text-3xl font-bold text-orange-600">{{ $stats->activeFlashSale }}</div>
            <p class="text-sm text-gray-500 mt-1">Produk Flash Sale aktif</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-blue-50 rounded-lg">
                    <i data-lucide="shopping-cart" class="w-5 h-5 text-blue-600"></i>
                </div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pesanan</span>
            </div>
            <div class="text-3xl font-bold text-blue-600">{{ $stats->pendingOrders }}</div>
            <p class="text-sm text-gray-500 mt-1">Sisa pesanan pending</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-green-50 rounded-lg">
                    <i data-lucide="dollar-sign" class="w-5 h-5 text-green-600"></i>
                </div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Revenue</span>
            </div>
            <div class="text-3xl font-bold text-green-600">Rp {{ number_format($stats->totalRevenue, 0, ',', '.') }}</div>
            <div class="flex items-center gap-1 text-xs text-green-600 mt-1">
                <i data-lucide="trending-up" class="w-3 h-3 text-green-500"></i>
                <span>+12% dari bulan lalu</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i data-lucide="leaf" class="w-5 h-5 text-green-700"></i>
                </div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Impact</span>
            </div>
            <div class="text-3xl font-bold text-green-700">{{ $stats->foodSaved }}</div>
            <p class="text-sm text-gray-500 mt-1">Makanan telah diselamatkan (kg)</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-purple-50 rounded-lg">
                    <i data-lucide="heart" class="w-5 h-5 text-purple-600"></i>
                </div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Donasi</span>
            </div>
            <div class="text-3xl font-bold text-purple-600">{{ $stats->donationsGiven }}</div>
            <p class="text-sm text-gray-500 mt-1">Donasi ke Lembaga Sosial</p>
        </div>
    </div>

    <!-- Expiring Items -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-5 h-5 text-orange-600"></i>
                <h2 class="text-xl font-bold text-gray-900 font-manrope">Produk Mendekati Expired</h2>
            </div>
            <a href="{{ route('mitra.inventory') }}" class="text-sm font-bold text-[#174413] hover:underline flex items-center gap-1">
                Kelola Stok
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>
        </div>
        <div class="p-6 space-y-4">
            @forelse($expiringItems as $item)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white rounded-lg border border-gray-100 flex items-center justify-center">
                         <i data-lucide="package" class="w-6 h-6 text-gray-400"></i>
                    </div>
                    <div>
                        <div class="font-bold text-gray-900">{{ $item->name }}</div>
                        <div class="text-sm text-gray-500">Stok: {{ $item->quantity }} unit</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-bold {{ $item->status === 'urgent' ? 'text-red-600' : 'text-orange-600' }}">
                        {{ $item->expiresIn }}
                    </div>
                    <div class="text-xs text-gray-500 uppercase">Sisa Waktu</div>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-400">
                 <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                 <p>Tidak ada produk mendekati kadaluarsa.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="shopping-bag" class="w-5 h-5 text-gray-600"></i>
                <h2 class="text-xl font-bold text-gray-900 font-manrope">Pesanan Terbaru</h2>
            </div>
            <a href="{{ route('mitra.orders') }}" class="text-sm font-bold text-[#174413] hover:underline flex items-center gap-1">
                Lihat Semua
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>
        </div>
        <div class="p-6">
            @forelse($recentOrders as $order)
                <!-- Order item structure would go here -->
            @empty
            <div class="text-center py-12">
                 <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                      <i data-lucide="shopping-cart" class="w-8 h-8 text-gray-300"></i>
                 </div>
                 <p class="text-gray-500 font-medium italic">Belum ada pesanan masuk.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
