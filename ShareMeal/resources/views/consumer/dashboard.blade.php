@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Dashboard Konsumen</h1>
        <p class="text-gray-600 mt-1">Hemat uang dan selamatkan lingkungan</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600">{{ $stats->savedMeals }}</div>
                <div class="text-xs text-gray-500 mt-1 font-medium uppercase tracking-wider">Makanan Diselamatkan</div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600">Rp {{ number_format($stats->moneySaved / 1000, 0) }}k</div>
                <div class="text-xs text-gray-500 mt-1 font-medium uppercase tracking-wider">Uang Dihemat</div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600">{{ $stats->co2Reduced }} kg</div>
                <div class="text-xs text-gray-500 mt-1 font-medium uppercase tracking-wider">CO₂ Dikurangi</div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-600">{{ $stats->favoriteStores }}</div>
                <div class="text-xs text-gray-500 mt-1 font-medium uppercase tracking-wider">Toko Favorit</div>
            </div>
        </div>
    </div>

    <!-- Notification Banner -->
    <div class="bg-gradient-to-r from-orange-50 to-red-50 border border-orange-200 rounded-xl p-4 flex items-center gap-4">
        <div class="bg-orange-100 p-2 rounded-full flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-orange-600"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path></svg>
        </div>
        <div>
            <div class="font-bold text-orange-900">3 Flash Sale Baru dari Toko Favorit!</div>
            <div class="text-sm text-orange-700">Notifikasi push real-time untuk update stok makanan surplus</div>
        </div>
    </div>

    <!-- Flash Sales -->
    <div>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Flash Sale Terdekat</h2>
            <a href="{{ route('consumer.search') }}" class="flex items-center gap-2 border border-gray-200 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                Lihat Semua
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($flashSales as $sale)
            <div class="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-md transition group flex flex-col">
                <div class="relative h-48 overflow-hidden flex-shrink-0">
                    <img src="{{ $sale->image }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    <div class="absolute top-3 right-3 bg-red-600 text-white px-2.5 py-1 rounded-full text-xs font-bold shadow-lg tracking-widest">
                        -{{ $sale->discount }}%
                    </div>
                    <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur px-2.5 py-1 rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-orange-600"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span class="text-orange-600">{{ $sale->expiresIn }}</span>
                    </div>
                </div>
                <div class="p-5 flex-1 flex flex-col">
                    <h3 class="font-bold text-lg text-gray-900 mb-1">{{ $sale->item }}</h3>
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <span>{{ $sale->store }}</span>
                        <span class="text-gray-300">•</span>
                        <span>{{ $sale->distance }}</span>
                    </div>

                    <div class="flex items-center gap-1.5 mb-5 flex-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-yellow-400"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        <span class="font-bold text-gray-900">{{ $sale->rating }}</span>
                        <span class="text-gray-400 text-xs">• Stok: {{ $sale->stock }}</span>
                    </div>

                    <div class="flex items-end justify-between border-t border-gray-50 pt-4 mt-auto">
                        <div>
                            <div class="text-2xl font-black text-green-600 leading-none">Rp {{ number_format($sale->discountPrice, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-400 line-through mt-1">Rp {{ number_format($sale->originalPrice, 0, ',', '.') }}</div>
                        </div>
                        <a href="{{ route('consumer.checkout', ['product_id' => $sale->id]) }}" class="bg-[#174413] text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-[#256020] transition-colors flex items-center gap-2 shadow-lg shadow-green-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><polyline points="22 8 12 18 2 8"></polyline></svg>
                            Booking
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Favorite Stores -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mt-6">
        <div class="p-6 border-b border-gray-50 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-red-600"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                Toko Favorit
            </h2>
            <button class="text-sm font-semibold text-gray-500 hover:text-gray-900 transition-colors">Kelola</button>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($favoriteStores as $store)
            <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-gray-50 transition cursor-pointer">
                <div class="flex-1">
                    <div class="font-bold text-gray-900 text-lg">{{ $store->name }}</div>
                    <div class="text-sm text-gray-500 flex flex-wrap items-center gap-4 mt-2">
                        <span class="font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded text-xs">{{ $store->category }}</span>
                        <span class="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            {{ $store->distance }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-yellow-400"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            {{ $store->rating }}
                        </span>
                    </div>
                </div>
                <div>
                    <span class="bg-green-100 text-green-700 px-4 py-2 rounded-full text-xs font-bold border border-green-200">
                        {{ $store->activeDeals }} deals aktif
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
@endsection
