@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="{
    openManage: false, 
    allStores: {{ $favoriteStores->toJson() }},
    favorites: JSON.parse(localStorage.getItem('favoriteStores') || '[]'),
    init() {
        this.allStores.forEach(store => {
            store.isFavorite = this.favorites.includes(store.id);
        });
        this.$watch('openManage', value => {
            if (value) {
                document.body.classList.add('overflow-hidden');
            } else {
                document.body.classList.remove('overflow-hidden');
            }
        });
    },
    get favoriteList() {
        return this.allStores.filter(s => s.isFavorite);
    },
    toggleFavorite(id) {
        const store = this.allStores.find(s => s.id === id);
        if (store) {
            store.isFavorite = !store.isFavorite;
            if (store.isFavorite) {
                if (!this.favorites.includes(id)) this.favorites.push(id);
            } else {
                this.favorites = this.favorites.filter(fid => fid !== id);
            }
            localStorage.setItem('favoriteStores', JSON.stringify(this.favorites));
        }
    }
}">
    <!-- Welcome Greeting Hero Banner -->
    <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-[#12360f] to-[#1c5317] p-8 md:p-12 text-white border border-white/10 shadow-2xl reveal mb-10">
        <!-- Internal Glowing Blobs -->
        <div class="absolute top-[-30%] left-[-15%] w-[30rem] h-[30rem] bg-emerald-400/20 rounded-full blur-[90px] pointer-events-none"></div>
        <div class="absolute bottom-[-30%] right-[-15%] w-[32rem] h-[32rem] bg-lime-400/15 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="relative z-10 md:w-2/3">
            <span class="bg-white/10 text-emerald-300 border border-white/10 backdrop-blur px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-widest mb-6 inline-block">
                🌿 Sahabat Bumi
            </span>
            <h1 class="text-4xl md:text-5xl font-black mb-4 leading-tight font-serif text-white">
                Selamat Datang Kembali!
            </h1>
            <p class="text-emerald-100 text-base md:text-lg max-w-xl font-medium opacity-90 leading-relaxed">
                Setiap surplus pangan yang Anda selamatkan hari ini membantu mengurangi emisi gas rumah kaca dan menghemat sumber daya berharga bumi kita.
            </p>
        </div>
        
        <!-- Background Leaf Deco -->
        <div class="absolute -right-16 -bottom-16 opacity-10">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-80 h-80"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"></path><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"></path></svg>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-16">
        <div class="glass-card glass-card-hover p-8 md:p-10 rounded-[2.5rem] group transition-all duration-500 reveal delay-100">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-emerald-100 transition-all duration-300">
                    <i data-lucide="package" class="w-7 h-7"></i>
                </div>
                <div class="text-3xl md:text-4xl font-serif font-black text-luxury-forest group-hover:text-emerald-600 transition-colors leading-none">{{ $stats->savedMeals }}</div>
                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-[0.2em] text-center mt-4 group-hover:text-luxury-forest transition-colors">Makanan Diselamatkan</div>
            </div>
        </div>
        <div class="glass-card glass-card-hover p-8 md:p-10 rounded-[2.5rem] group transition-all duration-500 reveal delay-200">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 bg-teal-50 text-teal-650 border border-teal-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-teal-100 transition-all duration-300">
                    <i data-lucide="trending-up" class="w-7 h-7"></i>
                </div>
                <div class="text-3xl md:text-4xl font-serif font-black text-luxury-forest group-hover:text-emerald-600 transition-colors leading-none">Rp {{ number_format($stats->moneySaved, 0, ',', '.') }}</div>
                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-[0.2em] text-center mt-4 group-hover:text-luxury-forest transition-colors">Uang Dihemat</div>
            </div>
        </div>
        <div class="glass-card glass-card-hover p-8 md:p-10 rounded-[2.5rem] group transition-all duration-500 reveal delay-300">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 bg-lime-50 text-lime-650 border border-lime-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-lime-100 transition-all duration-300">
                    <i data-lucide="leaf" class="w-7 h-7"></i>
                </div>
                <div class="text-3xl md:text-4xl font-serif font-black text-luxury-forest group-hover:text-emerald-600 transition-colors leading-none">{{ $stats->co2Reduced }} kg</div>
                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-[0.2em] text-center mt-4 group-hover:text-luxury-forest transition-colors">CO₂ Dikurangi</div>
            </div>
        </div>
        <div class="glass-card glass-card-hover p-8 md:p-10 rounded-[2.5rem] group transition-all duration-500 reveal delay-400">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 bg-amber-50 text-amber-600 border border-amber-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-amber-100 transition-all duration-300">
                    <i data-lucide="heart" class="w-7 h-7"></i>
                </div>
                <div class="text-3xl md:text-4xl font-serif font-black text-luxury-forest group-hover:text-emerald-600 transition-colors leading-none" x-text="favorites.length"></div>
                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-[0.2em] text-center mt-4 group-hover:text-luxury-forest transition-colors">Toko Favorit</div>
            </div>
        </div>
    </div>


    <!-- Flash Sales -->
    <div class="mb-20">
        <div class="flex items-end justify-between mb-10 reveal">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1 text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200/80 backdrop-blur-md mb-3 uppercase tracking-wide">Paling Segar</span>
                <h2 class="text-3xl font-serif font-bold text-luxury-forest">Flash Sale Terdekat</h2>
                <div class="h-1 w-12 bg-luxury-gold mt-2 rounded-full"></div>
            </div>
            <a href="{{ route('consumer.search') }}" class="text-xs font-black uppercase tracking-[0.2em] text-luxury-gold hover:text-luxury-forest transition-colors flex items-center gap-2 group bg-white/60 border border-luxury-alabas/85 px-5 py-3 rounded-2xl shadow-sm">
                Lihat Semua
                <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
            </a>
        </div>

        @if($flashSales && count($flashSales) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                @foreach($flashSales as $sale)
                <div class="group relative glass-card glass-card-hover rounded-[2.5rem] overflow-hidden transition-all duration-500 reveal delay-{{ $loop->iteration * 100 }} flex flex-col justify-between">
                    <div class="relative h-56 overflow-hidden flex-shrink-0">
                        <img src="{{ $sale->image }}" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-luxury-forest/80 via-transparent to-transparent opacity-60"></div>
                        
                        <div class="absolute top-4 right-4">
                            <div class="glass-panel px-4 py-2 rounded-full text-xs font-black text-luxury-forest uppercase tracking-widest border border-white/40 shadow-sm">
                                -{{ $sale->discount }}%
                            </div>
                        </div>

                        <div class="absolute bottom-4 left-4">
                            <div class="flex items-center gap-2 bg-white/95 backdrop-blur-md px-3.5 py-1.5 rounded-xl border border-white/50 shadow-sm">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-orange-650 animate-pulse"></i>
                                <span class="text-[10px] font-bold text-orange-650 uppercase tracking-wider">{{ $sale->expiresIn }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 flex-1 flex flex-col justify-between bg-white/10">
                        <div>
                            <h3 class="font-serif text-2xl font-bold text-luxury-forest mb-1.5 leading-snug">{{ $sale->item }}</h3>
                            <div class="flex items-center gap-2 text-[10px] font-bold text-luxury-slate uppercase tracking-widest mb-4">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-luxury-gold"></i>
                                <span>{{ $sale->store }}</span>
                                <span class="text-luxury-alabas">•</span>
                                <span>{{ $sale->distance }}</span>
                            </div>

                            <div class="flex items-center gap-1.5 mb-6">
                                <i data-lucide="star" class="w-4 h-4 text-yellow-400 fill-yellow-400"></i>
                                <span class="font-black text-luxury-forest text-sm">{{ $sale->rating }}</span>
                                <span class="text-luxury-alabas/80">•</span>
                                <span class="text-luxury-slate text-xs font-semibold">Stok: {{ $sale->stock }}</span>
                            </div>
                        </div>

                        <div class="flex items-end justify-between border-t border-luxury-alabas/60 pt-6 mt-auto">
                            <div>
                                <div class="text-2xl font-serif font-black text-luxury-forest">Rp {{ number_format($sale->discountPrice, 0, ',', '.') }}</div>
                                <div class="text-[10px] text-luxury-slate font-bold line-through mt-1">Rp {{ number_format($sale->originalPrice, 0, ',', '.') }}</div>
                            </div>
                            <form action="{{ route('consumer.cart.add') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $sale->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" 
                                        class="bg-luxury-forest text-white w-14 h-14 rounded-[1.2rem] flex items-center justify-center hover:bg-luxury-gold transition-all duration-500 luxury-shadow hover:scale-105 active:scale-95 shadow-lg">
                                    <i data-lucide="plus" class="w-6 h-6 text-white"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <!-- Empty state design for Flash Sales -->
            <div class="glass-card rounded-[2.5rem] p-16 text-center max-w-2xl mx-auto reveal">
                <div class="w-20 h-20 bg-luxury-ivory border border-luxury-alabas/55 rounded-3xl flex items-center justify-center mx-auto mb-6 text-luxury-slate/40 shadow-inner">
                    <i data-lucide="shopping-bag" class="w-10 h-10 stroke-[1.5]"></i>
                </div>
                <h3 class="font-serif text-2xl font-bold text-luxury-forest mb-2.5">Belum Ada Flash Sale Terdekat</h3>
                <p class="text-sm font-medium text-luxury-slate max-w-md mx-auto leading-relaxed">
                    Saat ini belum ada produk flash sale terdekat di sekitar lokasi Anda. Silakan cek kembali nanti atau cari kuliner lezat lainnya!
                </p>
                <div class="mt-8">
                    <a href="{{ route('consumer.search') }}" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-luxury-forest text-white border border-luxury-forest text-xs font-black uppercase tracking-widest hover:bg-transparent hover:text-luxury-forest transition-all duration-300 shadow-md shadow-emerald-950/10">
                        <i data-lucide="search" class="w-4 h-4 stroke-[2.5]"></i>
                        Cari Makanan Lainnya
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Favorite Stores -->
    <div class="glass-card rounded-[3rem] overflow-hidden mt-6 reveal">
        <div class="p-10 border-b border-luxury-alabas/60 flex items-center justify-between bg-white/30">
            <h2 class="text-2xl font-serif font-bold text-luxury-forest flex items-center gap-3">
                <i data-lucide="heart" class="w-6 h-6 text-red-500 fill-red-500 animate-pulse"></i>
                Toko Favorit
            </h2>
            <button @click="openManage = true" class="px-6 py-3 rounded-2xl bg-white/80 border border-luxury-alabas/85 text-[10px] font-black uppercase tracking-[0.2em] text-luxury-forest hover:bg-luxury-forest hover:text-white transition-all duration-500 luxury-shadow">Kelola</button>
        </div>
        <div class="divide-y divide-luxury-alabas/40 bg-white/20">
            <template x-for="store in favoriteList" :key="store.id">
                <div class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 hover:bg-white/40 transition-all duration-500 cursor-pointer group">
                    <div class="flex-1">
                        <div class="font-serif text-2xl font-bold text-luxury-forest group-hover:text-luxury-gold transition-colors" x-text="store.displayName || store.name"></div>
                        <div class="flex flex-wrap items-center gap-4 mt-2.5">
                            <span class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.2em] bg-luxury-gold/10 px-3 py-1 rounded-full" x-text="store.category"></span>
                            <span class="flex items-center gap-1.5 text-[10px] font-bold text-luxury-slate tracking-widest uppercase">
                                <i data-lucide="navigation" class="w-3.5 h-3.5 text-luxury-gold"></i>
                                <span x-text="store.distance"></span>
                            </span>
                            <span class="flex items-center gap-1.5 text-[10px] font-bold text-luxury-gold tracking-widest uppercase">
                                <i data-lucide="star" class="w-3.5 h-3.5 fill-luxury-gold text-luxury-gold"></i>
                                <span x-text="store.rating"></span>
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-6">
                        <span x-show="store.activeDeals > 0" class="text-[10px] font-black text-luxury-forest uppercase tracking-[0.2em] px-4 py-2 bg-luxury-ivory/80 rounded-xl border border-luxury-alabas/80">
                            <span x-text="store.activeDeals"></span> promo aktif
                        </span>
                        <button @click.stop="toggleFavorite(store.id)" class="text-luxury-alabas hover:text-red-500 transition-all duration-300 transform hover:scale-125 p-2 rounded-full hover:bg-red-50/50">
                            <i data-lucide="heart" class="w-6 h-6 fill-red-500 text-red-500"></i>
                        </button>
                    </div>
                </div>
            </template>
            <div x-show="favoriteList.length === 0" class="p-20 text-center text-luxury-slate font-serif italic text-lg bg-white/10">
                Belum ada toko favorit. Klik Kelola untuk menambah.
            </div>
        </div>
    </div>

    <!-- Manage Favorites Modal -->
    <div x-show="openManage"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
         x-cloak
         @keydown.escape.window="openManage = false">
        
        <!-- Backdrop -->
        <div x-show="openManage"
             x-transition:enter="ease-out duration-500"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-luxury-forest/65 backdrop-blur-md"
             @click="openManage = false"></div>

         <!-- Modal Content -->
        <div x-show="openManage"
             x-transition:enter="ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 translate-y-12 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 scale-95"
             class="relative glass-panel w-full max-w-2xl rounded-[3rem] overflow-hidden shadow-2xl border border-white/40">

            <div class="p-10 border-b border-luxury-alabas/60 flex items-center justify-between bg-white/40">
                <div>
                    <h3 class="text-3xl font-serif font-bold text-luxury-forest">Jelajahi Toko</h3>
                    <p class="text-[10px] text-luxury-gold font-black uppercase tracking-[0.2em] mt-1">Personalisasi pengalaman belanja Anda</p>
                </div>
                <button @click="openManage = false" class="w-12 h-12 flex items-center justify-center rounded-full bg-white text-luxury-slate hover:text-luxury-forest transition-all shadow-sm">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="p-10 space-y-6 max-h-[50vh] overflow-y-auto custom-scrollbar bg-white/10">
                <template x-for="store in allStores" :key="store.id">
                    <div class="flex items-center justify-between p-6 bg-white/40 border border-luxury-alabas/80 rounded-[2rem] hover:bg-white/80 hover:shadow-md transition-all duration-500 group">
                        <div class="flex items-center gap-6">
                            <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-luxury-forest luxury-shadow border border-luxury-alabas/70 transition-transform group-hover:scale-110">
                                <i data-lucide="store" class="w-6 h-6 stroke-[1.5]"></i>
                            </div>
                            <div>
                                <div class="font-bold text-luxury-forest text-lg" x-text="store.displayName || store.name"></div>
                                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-widest mt-1" x-text="store.category + ' • ' + store.distance"></div>
                            </div>
                        </div>
                        <button @click="toggleFavorite(store.id)"
                                :class="store.isFavorite ? 'bg-red-50 text-red-600 border-red-200' : 'bg-luxury-forest text-white border-luxury-forest'"
                                class="w-12 h-12 rounded-2xl border transition-all duration-500 flex items-center justify-center luxury-shadow active:scale-90">
                            <template x-if="store.isFavorite">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-red-600"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </template>
                            <template x-if="!store.isFavorite">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </template>
                        </button>
                    </div>
                </template>
            </div>

            <div class="p-10 bg-white/30 flex justify-end border-t border-luxury-alabas/60">
                <button @click="openManage = false" class="bg-luxury-forest text-white px-12 py-4 rounded-[1.5rem] font-black uppercase tracking-[0.2em] text-[10px] hover:bg-luxury-gold transition-all duration-500 luxury-shadow">
                    Selesai
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
@endsection
