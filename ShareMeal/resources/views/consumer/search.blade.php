@extends('layouts.dashboard')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="space-y-6" x-data='{ 
    searchQuery: "", 
    selectedFilters: [],
    stores: {!! json_encode($stores) !!},
    filters: {!! json_encode($filters) !!},
    favorites: JSON.parse(localStorage.getItem("favoriteStores") || "[]"),
    
    // Map Picker Data
    openMap: false,
    selectedAddress: "Jl. Telekomunikasi No. 1, Bandung",
    map: null,
    marker: null,

    init() {
        this.stores.forEach(store => {
            store.isFavorite = this.favorites.includes(store.id);
        });
        this.$watch("favorites", val => localStorage.setItem("favoriteStores", JSON.stringify(val)));
    },

    initMap() {
        if (this.map) return;
        
        setTimeout(() => {
            this.map = L.map("map-picker").setView([-6.974, 107.630], 15);
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "© OpenStreetMap"
            }).addTo(this.map);

            this.marker = L.marker([-6.974, 107.630], {draggable: true}).addTo(this.map);
            
            this.map.on("click", (e) => {
                this.marker.setLatLng(e.latlng);
                this.updateAddress(e.latlng.lat, e.latlng.lng);
            });

            this.marker.on("dragend", () => {
                const pos = this.marker.getLatLng();
                this.updateAddress(pos.lat, pos.lng);
            });
        }, 100);
    },

    updateAddress(lat, lng) {
        // Dummy reverse geocoding
        const dummyAddresses = [
            "Jl. Bojongsoang Raya No. 45, Bandung",
            "Kost Putra Barokah, Sukabirus",
            "Apartemen Buah Batu Park, Bandung",
            "Gedung Kuliah Umum Telkom University",
            "Warteg Bahari, Jl. Sukapura"
        ];
        this.selectedAddress = dummyAddresses[Math.floor(Math.random() * dummyAddresses.length)];
    },

    toggleFavoriteStore(store) {
        store.isFavorite = !store.isFavorite;
        if (store.isFavorite) {
            if (!this.favorites.includes(store.id)) this.favorites.push(store.id);
        } else {
            this.favorites = this.favorites.filter(id => id !== store.id);
        }
    },
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
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Cari Makanan Terdekat</h1>
            <p class="text-gray-600 mt-1">Temukan surplus makanan lezat dengan harga hemat</p>
        </div>
        <div class="flex items-center gap-2 bg-green-50 px-4 py-2 rounded-xl border border-green-100 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-green-600"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            <span class="text-sm font-bold text-green-900" x-text="selectedAddress"></span>
        </div>
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
            <button @click="openMap = true; initMap()" class="bg-[#174413] text-white px-6 py-3.5 rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-[#256020] transition shadow-lg shadow-green-100">
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
                    </div>

                    <!-- Store Details -->
                    <div class="p-8">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <div class="flex items-center gap-3">
                                    <h3 class="text-2xl font-black text-gray-900 leading-tight" x-text="store.name"></h3>
                                    <button @click="toggleFavoriteStore(store)" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-50 transition text-gray-300 hover:text-red-500 focus:outline-none focus:ring-2 focus:ring-red-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 transition-colors" :class="store.isFavorite ? 'fill-red-500 text-red-500' : 'fill-transparent'"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                    </button>
                                </div>
                                <p class="text-gray-500 font-medium mt-1" x-text="store.category"></p>
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
                                Menu Tersedia
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
                                            <div class="text-lg font-black text-green-600 leading-none" x-text="'Rp ' + (deal.discountPrice > 0 ? deal.discountPrice : deal.originalPrice).toLocaleString('id-ID')"></div>
                                            <div class="text-[10px] text-gray-400 line-through mt-1" x-show="deal.discountPrice > 0 && deal.discountPrice != deal.originalPrice" x-text="'Rp ' + deal.originalPrice.toLocaleString('id-ID')"></div>
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

    <!-- Map Picker Modal -->
    <div x-show="openMap" 
         class="fixed inset-0 z-[100] overflow-y-auto" 
         x-cloak
         @keydown.escape.window="openMap = false">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="openMap" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" 
                 @click="openMap = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="openMap" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block w-full max-w-3xl overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-3xl sm:my-8">
                
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Pilih Lokasi Pengantaran</h3>
                        <p class="text-sm text-gray-500 mt-1">Klik pada peta atau geser pin untuk menentukan lokasi</p>
                    </div>
                    <button @click="openMap = false" class="p-2 hover:bg-gray-100 rounded-full transition-colors text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <div class="relative">
                    <div id="map-picker" class="h-[400px] w-full bg-gray-100"></div>
                    
                    <div class="absolute bottom-6 left-6 right-6 z-[1000]">
                        <div class="bg-white p-4 rounded-2xl shadow-xl border border-gray-100">
                            <div class="flex items-start gap-4">
                                <div class="bg-green-100 p-2 rounded-xl">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-green-600"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Alamat Terpilih</div>
                                    <div class="text-gray-900 font-bold leading-snug" x-text="selectedAddress"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-gray-50 flex justify-end gap-3">
                    <button @click="openMap = false" class="px-6 py-3 font-bold text-gray-500 hover:text-gray-700 transition">Batal</button>
                    <button @click="openMap = false" class="bg-[#174413] text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-green-100 hover:bg-[#256020] transition">
                        Konfirmasi Lokasi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection