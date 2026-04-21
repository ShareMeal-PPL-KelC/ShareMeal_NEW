@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data='{ 
    searchQuery: "", 
    selectedFilters: [],
    stores: {!! json_encode($stores) !!},
    filters: {!! json_encode($filters) !!},
    toggleFilter(id) {
        if (this.selectedFilters.includes(id)) {
            this.selectedFilters = this.selectedFilters.filter(f => f !== id);
        } else {
            this.selectedFilters.push(id);
        }
    },
    get filteredStores() {
        return this.stores.filter(store => {
            const matchesSearch = this.searchQuery === "" || 
                store.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                store.category.toLowerCase().includes(this.searchQuery.toLowerCase());
            
            const matchesFilters = this.selectedFilters.length === 0 ||
                this.selectedFilters.every(f => store.tags.includes(f));
            
            return matchesSearch && matchesFilters;
        });
    }
}'>
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Cari Makanan Terdekat</h1>
        <p class="text-gray-600 mt-1">Temukan surplus makanan lezat dengan harga hemat</p>
    </div>

    <!-- Search & Filter Card -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input 
                    type="text" 
                    placeholder="Cari toko atau jenis makanan..." 
                    x-model="searchQuery"
                    class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:ring-2 focus:ring-green-600 focus:bg-white transition"
                >
            </div>
            <button class="bg-[#174413] text-white px-6 py-3.5 rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-[#256020] transition shadow-lg shadow-green-100">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg>
                Lokasi Saya
            </button>
        </div>

        <div>
            <div class="flex items-center gap-2 mb-4 text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                <span class="text-sm font-bold uppercase tracking-wider">Filter Kategori</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <template x-for="filter in filters" :key="filter.id">
                    <button 
                        @click="toggleFilter(filter.id)"
                        :class="selectedFilters.includes(filter.id) ? 'bg-green-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-5 py-2.5 rounded-full text-sm font-bold transition flex items-center gap-2"
                    >
                        <span x-text="filter.icon"></span>
                        <span x-text="filter.label"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- Results Info -->
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900">
            <span x-text="filteredStores.length"></span> toko ditemukan
        </h2>
        <div class="text-sm text-gray-500 font-medium">Diurutkan berdasarkan jarak terdekat</div>
    </div>

    <!-- Store Results -->
    <div class="space-y-6">
        <template x-for="store in filteredStores" :key="store.id">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden group hover:shadow-md transition duration-300">
                <div class="grid md:grid-cols-[240px_1fr] gap-0">
                    <!-- Store Image -->
                    <div class="relative h-56 md:h-auto overflow-hidden">
                        <img :src="store.image" :alt="store.name" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <button @click="store.isFavorite = !store.isFavorite" class="absolute top-4 right-4 w-10 h-10 bg-white/90 backdrop-blur rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-gray-400" :class="store.isFavorite ? 'fill-red-600 text-red-600' : 'fill-transparent'"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        </button>
                    </div>

                    <!-- Store Details -->
                    <div class="p-8">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 leading-tight" x-text="store.name"></h3>
                                <p class="text-gray-500 font-medium" x-text="store.category"></p>
                            </div>
                            <div class="bg-yellow-50 px-3 py-1.5 rounded-xl flex items-center gap-1.5 border border-yellow-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-yellow-400"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                <span class="font-bold text-gray-900" x-text="store.rating"></span>
                                <span class="text-xs text-gray-400" x-text="'(' + store.reviews + ')'"></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 text-sm text-gray-600 mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-green-600"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span x-text="store.address"></span>
                            <span class="font-black text-green-600" x-text="'• ' + store.distance"></span>
                        </div>

                        <!-- Flash Sales List -->
                        <div class="bg-gray-50 rounded-2xl p-5 space-y-4">
                            <h4 class="text-sm font-black uppercase tracking-widest text-gray-400 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-orange-500"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                Flash Sale Aktif
                            </h4>
                            
                            <template x-for="deal in store.products" :key="deal.id">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-3 bg-white rounded-xl border border-gray-100 shadow-sm">
                                    <div class="flex-1">
                                        <div class="font-bold text-gray-900" x-text="deal.item"></div>
                                        <div class="flex items-center gap-3 mt-1">
                                            <span class="text-xs font-bold text-orange-600 flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                                <span x-text="deal.expiresIn"></span>
                                            </span>
                                            <span class="text-xs text-gray-400 font-medium" x-text="'Stok: ' + deal.stock"></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between md:justify-end gap-4 w-full md:w-auto mt-2 md:mt-0 border-t md:border-t-0 pt-2 md:pt-0">
                                        <div class="text-left md:text-right">
                                            <div class="text-lg font-black text-green-600 leading-none" x-text="'Rp ' + deal.discountPrice.toLocaleString('id-ID')"></div>
                                            <div class="text-[10px] text-gray-400 line-through mt-1" x-text="'Rp ' + deal.originalPrice.toLocaleString('id-ID')"></div>
                                        </div>
                                        <button 
                                            @click="window.location.href = '{{ route('consumer.checkout') }}?product_id=' + deal.id"
                                            :disabled="deal.stock === 0"
                                            :class="deal.stock === 0 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-green-600 text-white hover:bg-green-700'"
                                            class="px-5 py-2.5 rounded-xl font-bold text-sm transition shadow-sm"
                                            x-text="deal.stock === 0 ? 'Habis' : 'Booking'"
                                        ></button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- No Results -->
        <div x-show="filteredStores.length === 0" class="bg-white p-20 rounded-3xl border border-dashed border-gray-200 text-center" style="display: none;">
            <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 text-gray-300"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Tidak Ada Hasil</h3>
            <p class="text-gray-500">Coba ubah kata kunci atau filter pencarian Anda untuk menemukan makanan lezat lainnya.</p>
        </div>
    </div>
</div>
@endsection