@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data='{ 
    stores: {!! json_encode($stores) !!},
    favorites: JSON.parse(localStorage.getItem("favoriteStores") || "[]"),
    init() {
        this.stores.forEach(store => {
            store.isFavorite = this.favorites.includes(store.id);
        });
        this.$watch("favorites", val => localStorage.setItem("favoriteStores", JSON.stringify(val)));
    },
    toggleFavoriteStore(store) {
        store.isFavorite = !store.isFavorite;
        if (store.isFavorite) {
            if (!this.favorites.includes(store.id)) this.favorites.push(store.id);
        } else {
            this.favorites = this.favorites.filter(id => id !== store.id);
        }
    },
    get favoriteStoresList() {
        return this.stores.filter(store => this.favorites.includes(store.id));
    }
}'>
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Toko Favorit</h1>
        <p class="text-gray-600 mt-1">Daftar toko dan mitra kesukaan Anda</p>
    </div>

    <!-- Results Info -->
    <div class="flex items-center justify-between mt-8">
        <h2 class="text-xl font-bold text-gray-900">
            <span x-text="favoriteStoresList.length"></span> toko favorit
        </h2>
    </div>

    <!-- Store Results -->
    <div class="space-y-6 mt-4">
        <template x-for="store in favoriteStoresList" :key="store.id">
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
                    </div>
                </div>
            </div>
        </template>

        <!-- No Results -->
        <div x-show="favoriteStoresList.length === 0" class="bg-white p-20 rounded-3xl border border-dashed border-gray-200 text-center" style="display: none;">
            <div class="bg-red-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 text-red-300"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Belum ada Toko Favorit</h3>
            <p class="text-gray-500">Anda belum menambahkan toko manapun ke daftar favorit Anda. Temukan toko kesukaanmu di Cari Makanan!</p>
            <a href="{{ route('consumer.search') }}" class="inline-block mt-6 bg-green-600 text-white font-bold py-3 px-6 rounded-xl hover:bg-green-700 transition">Mulai Cari Makanan</a>
        </div>
    </div>
</div>
@endsection
