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
                (store.profile?.business_name || store.organization_name || store.name).toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                (store.profile?.business_type || store.category).toLowerCase().includes(this.searchQuery.toLowerCase());
            
            const matchesFilters = this.selectedFilters.length === 0 ||
                this.selectedFilters.every(f => store.tags.includes(f));
            
            return matchesSearch && matchesFilters;
        });
    }
}'>
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-12 reveal">
        <div>
            <h1 class="text-5xl font-serif font-bold text-luxury-forest leading-tight text-center md:text-left">Cari Makanan</h1>
            <p class="text-luxury-slate font-medium mt-2 tracking-wide text-center md:text-left">Temukan kurasi surplus makanan terbaik di sekitar Anda.</p>
        </div>
        <div class="flex items-center gap-4 bg-white/40 backdrop-blur-md px-6 py-3 rounded-2xl border border-luxury-alabas/80 shadow-sm self-center md:self-auto">
            <div class="w-8 h-8 bg-luxury-forest rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-luxury-gold">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                </svg>
            </div>
            <span class="text-xs font-black uppercase tracking-widest text-luxury-forest" x-text="selectedAddress"></span>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="glass-card p-10 rounded-[3rem] space-y-10 mb-16 reveal">
        <div class="flex flex-col md:flex-row gap-6">
            <div class="relative flex-1 group">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-luxury-gold transition-transform group-focus-within:scale-110">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <input 
                    type="text" 
                    placeholder="Apa yang ingin Anda selamatkan hari ini?" 
                    x-model="searchQuery"
                    class="w-full pl-16 pr-6 py-5 bg-white/50 border border-luxury-alabas/80 rounded-[1.5rem] outline-none focus:ring-2 focus:ring-luxury-forest focus:bg-white transition-all duration-500 font-medium text-luxury-charcoal"
                >
            </div>
            <button @click="openMap = true; initMap()" class="bg-luxury-forest text-white px-10 py-5 rounded-[1.5rem] font-black uppercase tracking-[0.2em] text-[10px] flex items-center justify-center gap-3 hover:bg-luxury-gold transition-all duration-500 luxury-shadow group active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 group-hover:rotate-90 transition-transform duration-500 text-luxury-gold">
                    <circle cx="12" cy="12" r="10"/><line x1="22" x2="18" y1="12" y2="12"/><line x1="6" x2="2" y1="12" y2="12"/><line x1="12" x2="12" y1="6" y2="2"/><line x1="12" x2="12" y1="22" y2="18"/>
                </svg>
                Ganti Lokasi
            </button>
        </div>

        <div>
            <div class="flex items-center gap-3 mb-6">
                <div class="h-px flex-1 bg-luxury-alabas/60"></div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-luxury-gold">Filter Kategori</span>
                <div class="h-px flex-1 bg-luxury-alabas/60"></div>
            </div>
            <div class="flex flex-wrap justify-center gap-3">
                <template x-for="filter in filters" :key="filter.id">
                    <button 
                        @click="toggleFilter(filter.id)"
                        :class="selectedFilters.includes(filter.id) ? 'bg-luxury-forest text-white luxury-shadow scale-105' : 'bg-white/70 text-luxury-slate border border-luxury-alabas/80 hover:border-luxury-gold/50 hover:bg-white'"
                        class="px-8 py-3 rounded-full text-xs font-bold transition-all duration-500 flex items-center gap-3"
                    >
                        <span x-text="filter.icon" class="text-base"></span>
                        <span x-text="filter.label" class="uppercase tracking-widest"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- Results Info -->
    <div class="flex items-center justify-between mb-10 px-4 reveal">
        <h2 class="text-2xl font-serif font-bold text-luxury-forest italic">
            <span x-text="filteredStores.length"></span> curators found
        </h2>
        <div class="text-[10px] text-luxury-gold font-black uppercase tracking-[0.2em] bg-white/40 backdrop-blur-md px-4 py-2 rounded-full border border-luxury-alabas/80">
            Sorted by proximity
        </div>
    </div>
    <!-- Store Results -->
    <div class="space-y-16">
        <template x-for="(store, index) in filteredStores" :key="store.id">
            <div class="glass-card glass-card-hover rounded-[2.5rem] overflow-hidden group hover:border-luxury-gold/30 reveal p-6 md:p-8 space-y-8">
                <!-- Store Info Header (Image & Meta details side-by-side) -->
                <div class="flex flex-col lg:flex-row gap-6 lg:gap-8">
                    <!-- Store Image (Not too large, beautifully proportioned) -->
                    <div class="relative w-full lg:w-72 h-56 lg:h-44 shrink-0 rounded-2xl overflow-hidden border border-white/20 shadow-sm bg-white/5">
                        <img :src="store.image" :alt="store.name" class="w-full h-full object-cover transition-transform duration-[2000ms] group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                        <div class="absolute bottom-4 left-4">
                            <div class="bg-black/45 backdrop-blur-md px-3.5 py-1.5 rounded-full text-[9px] font-black text-white uppercase tracking-[0.2em] border border-white/20 shadow-md">
                                <span x-text="store.distance"></span> away
                            </div>
                        </div>
                    </div>

                    <!-- Store Details -->
                    <div class="flex-1 min-w-0 flex flex-col justify-between space-y-4">
                        <div>
                            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3.5 flex-wrap">
                                        <h3 class="text-3xl font-serif font-bold text-luxury-forest leading-tight group-hover:text-luxury-gold transition-colors duration-700 truncate" x-text="store.displayName || store.name"></h3>
                                        <button @click="toggleFavoriteStore(store)" class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-white/60 border border-luxury-alabas/80 transition-all duration-500 text-luxury-alabas hover:text-red-500 hover:shadow-sm active:scale-90">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 transition-all duration-300" :class="store.isFavorite ? 'fill-red-500 text-red-500 stroke-red-500' : 'text-luxury-slate'">
                                                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-2.5 mt-2 flex-wrap text-luxury-slate">
                                        <span class="text-[8px] font-black text-luxury-gold uppercase tracking-[0.3em] bg-luxury-gold/5 px-3 py-1 rounded-full border border-luxury-gold/10" x-text="store.profile?.business_type || store.category"></span>
                                        <div class="h-1.5 w-1.5 bg-luxury-alabas/60 rounded-full"></div>
                                        <p class="text-xs font-semibold italic truncate" x-text="store.address"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 bg-white/70 border border-luxury-alabas px-4 py-2 rounded-xl shadow-sm self-start shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-luxury-gold mr-1">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                    </svg>
                                    <span class="text-base font-serif font-black text-luxury-forest" x-text="store.rating"></span>
                                    <span class="text-[9px] text-luxury-slate font-bold uppercase tracking-widest" x-text="'(' + store.reviews + ')'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Operational Details Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-3.5 rounded-xl bg-white/30 border border-luxury-alabas/85 hover:bg-white/60 hover:shadow-sm transition-all duration-500 flex flex-col justify-center">
                                <div class="text-[9px] font-black uppercase tracking-[0.2em] text-luxury-gold mb-0.5">Operating Hours</div>
                                <div class="text-xs font-bold text-luxury-forest" x-text="store.profile?.business_opening_hours || store.profile?.opening_hours || '-'"></div>
                            </div>
                            <div class="p-3.5 rounded-xl bg-white/30 border border-luxury-alabas/85 hover:bg-white/60 hover:shadow-sm transition-all duration-500 flex flex-col justify-center">
                                <div class="text-[9px] font-black uppercase tracking-[0.2em] text-luxury-gold mb-0.5">Contact</div>
                                <div class="text-xs font-bold text-luxury-forest" x-text="store.profile?.business_contact || store.phone || '-'"></div>
                            </div>
                        </div>

                        <!-- Description Quote -->
                        <p class="text-xs leading-relaxed text-luxury-slate font-medium italic opacity-85 border-l-2 border-luxury-gold/30 pl-3.5" x-show="store.profile?.business_description || store.profile?.description" x-text="'&ldquo;' + (store.profile?.business_description || store.profile?.description) + '&rdquo;'"></p>
                    </div>
                </div>

                <!-- Products Selection (Daily Selection spanning full width below) -->
                <div class="space-y-6 pt-6 border-t border-luxury-alabas/60">
                    <div class="flex items-center gap-4">
                        <span class="text-[10px] font-black uppercase tracking-[0.3em] text-luxury-gold whitespace-nowrap">Daily Selection</span>
                        <div class="h-px w-full bg-luxury-alabas/60"></div>
                    </div>
                    
                    <div class="space-y-4">
                        <template x-for="deal in store.products" :key="deal.id">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 p-6 bg-white/40 border border-luxury-alabas/80 rounded-[2rem] hover:bg-white/80 hover:shadow-md hover:border-luxury-gold/20 transition-all duration-700">
                                <div class="flex-1 min-w-0">
                                    <div class="font-serif text-2xl font-bold text-luxury-forest truncate" x-text="deal.item"></div>
                                    <div class="flex flex-wrap items-center gap-4 mt-3">
                                        <div class="flex items-center gap-2 text-[10px] font-black text-orange-600 uppercase tracking-widest bg-orange-50/80 px-3 py-1 rounded-lg shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                            </svg>
                                            <span x-text="deal.expiresIn"></span>
                                        </div>
                                        <div class="flex items-center gap-2 text-[10px] font-black text-luxury-emerald uppercase tracking-widest bg-luxury-emerald/5 px-3 py-1 rounded-lg shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
                                            </svg>
                                            <span x-text="deal.pickupTime"></span>
                                        </div>
                                        <span class="text-[10px] text-luxury-slate font-black uppercase tracking-[0.2em] shrink-0" x-text="'Stock: ' + deal.stock"></span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between md:justify-end gap-8 w-full md:w-auto pt-6 md:pt-0 border-t md:border-t-0 border-luxury-alabas/60">
                                    <div class="text-left md:text-right shrink-0">
                                        <div class="text-3xl font-serif font-black text-luxury-forest leading-none" x-text="'Rp ' + (deal.discountPrice > 0 ? deal.discountPrice : deal.originalPrice).toLocaleString('id-ID')"></div>
                                        <div class="text-[11px] text-luxury-slate line-through mt-2 font-bold tracking-widest" x-show="deal.discountPrice > 0 && deal.discountPrice != deal.originalPrice" x-text="'Rp ' + deal.originalPrice.toLocaleString('id-ID')"></div>
                                    </div>
                                    <button 
                                        @click="window.location.href = '{{ route('consumer.checkout') }}?product_id=' + deal.id"
                                        :disabled="deal.stock === 0"
                                        :class="deal.stock === 0 ? 'bg-luxury-alabas/60 text-luxury-slate cursor-not-allowed opacity-40' : 'bg-luxury-forest text-white hover:bg-luxury-gold'"
                                        class="h-14 px-8 rounded-xl font-black uppercase tracking-[0.3em] text-[10px] transition-all duration-700 luxury-shadow active:scale-95 whitespace-nowrap"
                                        x-text="deal.stock === 0 ? 'Sold' : 'Reserve Now'"
                                    ></button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <!-- No Results -->
        <div x-show="filteredStores.length === 0" class="glass-card p-32 rounded-[4rem] text-center" style="display: none;">
            <div class="bg-white/60 w-28 h-28 rounded-[2.5rem] flex items-center justify-center mx-auto mb-10 border border-luxury-alabas/80 luxury-shadow">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 text-luxury-slate opacity-40 mx-auto">
                    <path d="m13.5 8.5-5 5"/><path d="m8.5 8.5 5 5"/><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
            </div>
            <h3 class="text-4xl font-serif font-bold text-luxury-forest mb-4 italic">No matching treasures.</h3>
            <p class="text-luxury-slate font-medium max-w-sm mx-auto leading-relaxed">Adjust your selection to explore other exceptional surplus opportunities waiting to be rescued.</p>
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
             class="fixed inset-0 transition-opacity bg-luxury-forest/65 backdrop-blur-md" 
             @click="openMap = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div x-show="openMap" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="inline-block w-full max-w-3xl overflow-hidden text-left align-middle transition-all transform glass-panel shadow-xl rounded-3xl border border-white/40 sm:my-8">
            
            <div class="p-6 border-b border-luxury-alabas/60 flex items-center justify-between bg-white/40">
                <div>
                    <h3 class="text-xl font-bold text-luxury-forest">Pilih Lokasi Pengantaran</h3>
                    <p class="text-sm text-luxury-slate mt-1 font-medium">Klik pada peta atau geser pin untuk menentukan lokasi</p>
                </div>
                <button @click="openMap = false" class="p-2 hover:bg-white rounded-full transition-colors text-luxury-slate shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <div class="relative bg-white/10">
                <div id="map-picker" class="h-[400px] w-full bg-gray-100"></div>
                
                <div class="absolute bottom-6 left-6 right-6 z-[1000]">
                    <div class="bg-white/95 backdrop-blur-md p-4 rounded-2xl shadow-xl border border-white/50">
                        <div class="flex items-start gap-4">
                            <div class="bg-emerald-100 p-2 rounded-xl text-emerald-600">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            </div>
                            <div class="flex-1">
                                <div class="text-[10px] font-black uppercase tracking-widest text-luxury-gold mb-1">Alamat Terpilih</div>
                                <div class="text-luxury-forest font-bold leading-snug" x-text="selectedAddress"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white/40 border-t border-luxury-alabas/60 flex justify-end gap-3">
                <button @click="openMap = false" class="px-6 py-3 font-bold text-luxury-slate hover:text-luxury-forest transition">Batal</button>
                <button @click="openMap = false" class="bg-[#174413] text-white px-8 py-3 rounded-xl font-bold shadow-md hover:bg-luxury-gold transition">
                    Konfirmasi Lokasi
                </button>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
