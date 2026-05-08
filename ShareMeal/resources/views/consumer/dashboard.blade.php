@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="{
    openManage: false, 
    allStores: [
        {id: 1, name: 'Toko Roti Makmur', category: 'Bakery', distance: '0.5 km', rating: 4.8, activeDeals: 2, isFavorite: true},
        {id: 2, name: 'Healthy Cafe', category: 'Healthy Food', distance: '1.2 km', rating: 4.5, activeDeals: 1, isFavorite: true},
        {id: 3, name: 'Warung Ibu Rina', category: 'Indonesian', distance: '0.8 km', rating: 4.7, activeDeals: 5, isFavorite: false},
        {id: 4, name: 'Bakery Delight', category: 'Bakery', distance: '2.1 km', rating: 4.2, activeDeals: 1, isFavorite: false},
        {id: 5, name: 'Fresh Salads', category: 'Healthy Food', distance: '1.5 km', rating: 4.6, activeDeals: 3, isFavorite: true},
        {id: 6, name: 'Kedai Kopi Janji', category: 'Coffee', distance: '0.3 km', rating: 4.4, activeDeals: 0, isFavorite: false},
    ],
    get favorites() {
        return this.allStores.filter(s => s.isFavorite);
    },
    toggleFavorite(id) {
        const store = this.allStores.find(s => s.id === id);
        if (store) store.isFavorite = !store.isFavorite;
    }
}">
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
                <div class="text-3xl font-bold text-purple-600" x-text="favorites.length"></div>
                <div class="text-xs text-gray-500 mt-1 font-medium uppercase tracking-wider">Toko Favorit</div>
            </div>
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
            <button @click="openManage = true" class="text-sm font-semibold text-[#174413] hover:text-[#256020] transition-colors px-3 py-1 bg-green-50 rounded-md">Kelola</button>
        </div>
        <div class="divide-y divide-gray-50">
            <template x-for="store in favorites" :key="store.id">
                <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-gray-50 transition cursor-pointer group">
                    <div class="flex-1">
                        <div class="font-bold text-gray-900 text-lg" x-text="store.name"></div>
                        <div class="text-sm text-gray-500 flex flex-wrap items-center gap-4 mt-2">
                            <span class="font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded text-xs" x-text="store.category"></span>
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                <span x-text="store.distance"></span>
                            </span>
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-yellow-400"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                <span x-text="store.rating"></span>
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span x-show="store.activeDeals > 0" class="bg-green-100 text-green-700 px-4 py-2 rounded-full text-xs font-bold border border-green-200">
                            <span x-text="store.activeDeals"></span> deals aktif
                        </span>
                        <button @click.stop="toggleFavorite(store.id)" class="text-gray-300 hover:text-red-500 transition-colors p-2 rounded-full hover:bg-red-50">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-red-500"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        </button>
                    </div>
                </div>
            </template>
            <div x-show="favorites.length === 0" class="p-10 text-center text-gray-500">
                Belum ada toko favorit. Klik Kelola untuk menambah.
            </div>
        </div>
    </div>

    <!-- Manage Favorites Modal -->
    <div x-show="openManage"
         class="fixed inset-0 z-[100] overflow-y-auto"
         x-cloak
         @keydown.escape.window="openManage = false">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="openManage"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                 @click="openManage = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="openManage"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block w-full max-w-2xl p-6 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl sm:my-8">

                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">Kelola Toko Favorit</h3>
                    <button @click="openManage = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-2">
                    <template x-for="store in allStores" :key="store.id">
                        <div class="flex items-center justify-between p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900" x-text="store.name"></div>
                                    <div class="text-sm text-gray-500" x-text="store.category + ' • ' + store.distance"></div>
                                </div>
                            </div>
                            <button @click="toggleFavorite(store.id)"
                                    :class="store.isFavorite ? 'bg-red-50 text-red-600 border-red-100' : 'bg-green-50 text-[#174413] border-green-100'"
                                    class="px-4 py-2 rounded-lg text-sm font-bold border transition-colors flex items-center gap-2">
                                <template x-if="store.isFavorite">
                                    <span class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                        Hapus
                                    </span>
                                </template>
                                <template x-if="!store.isFavorite">
                                    <span class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M12 5v14M5 12h14"></path></svg>
                                        Tambah
                                    </span>
                                </template>
                            </button>
                        </div>
                    </template>
                </div>

                <div class="mt-8 flex justify-end">
                    <button @click="openManage = false" class="bg-[#174413] text-white px-8 py-3 rounded-xl font-bold hover:bg-[#256020] transition-colors">
                        Selesai
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
@endsection
