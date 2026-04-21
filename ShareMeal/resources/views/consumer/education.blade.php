@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="{
    searchQuery: '',
    activeCategory: 'Semua',
    articles: {{ json_encode($articles) }},
    get filteredArticles() {
        return this.articles.filter(article => {
            const matchesSearch = article.title.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                 article.content.toLowerCase().includes(this.searchQuery.toLowerCase());
            const matchesCategory = this.activeCategory === 'Semua' || article.category === this.activeCategory;
            return matchesSearch && matchesCategory;
        });
    },
    handleShare(title) {
        alert('Tautan untuk \'' + title + '\' berhasil disalin!');
    }
}">
    <!-- Header Hero Section -->
    <div class="bg-[#174413] rounded-3xl p-8 md:p-12 text-white relative overflow-hidden shadow-2xl shadow-green-100">
        <div class="relative z-10 md:w-2/3">
            <span class="bg-green-500 text-white px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest mb-6 inline-block">
                Edukasi Lingkungan
            </span>
            <h1 class="text-4xl md:text-5xl font-black mb-4 leading-tight">
                Mari Bersama Kurangi Food Waste
            </h1>
            <p class="text-green-100 text-lg mb-8 max-w-xl font-medium opacity-90">
                Tingkatkan pengetahuanmu tentang dampak sampah makanan dan temukan tips praktis
                untuk mulai menyelamatkan makanan hari ini.
            </p>
            <div class="relative max-w-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input
                    type="text"
                    placeholder="Cari artikel atau topik..."
                    x-model="searchQuery"
                    class="w-full pl-12 pr-4 py-4 bg-white text-gray-900 rounded-2xl outline-none focus:ring-4 focus:ring-green-500/30 transition shadow-lg font-medium"
                >
            </div>
        </div>

        <!-- Decorative SVG -->
        <div class="absolute -right-16 -bottom-16 opacity-10">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-80 h-80"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"></path><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"></path></svg>
        </div>
    </div>

    <!-- Stats / Impact Gamification -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-green-50 border border-green-100 p-6 rounded-2xl flex items-center gap-5">
            <div class="w-14 h-14 rounded-full bg-green-200 flex items-center justify-center shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7 text-green-700"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
            </div>
            <div>
                <p class="text-xs text-green-800 font-black uppercase tracking-widest">Artikel Dibaca</p>
                <h3 class="text-2xl font-black text-green-950">{{ $stats->readCount }} Artikel</h3>
            </div>
        </div>
        <div class="bg-blue-50 border border-blue-100 p-6 rounded-2xl flex items-center gap-5">
            <div class="w-14 h-14 rounded-full bg-blue-200 flex items-center justify-center shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7 text-blue-700"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>
            </div>
            <div>
                <p class="text-xs text-blue-800 font-black uppercase tracking-widest">Level Edukasi</p>
                <h3 class="text-2xl font-black text-blue-950">{{ $stats->level }}</h3>
            </div>
        </div>
        <div class="bg-orange-50 border border-orange-100 p-6 rounded-2xl flex items-center gap-5">
            <div class="w-14 h-14 rounded-full bg-orange-200 flex items-center justify-center shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7 text-orange-700"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"></path><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"></path></svg>
            </div>
            <div>
                <p class="text-xs text-orange-800 font-black uppercase tracking-widest">Poin Pengetahuan</p>
                <h3 class="text-2xl font-black text-orange-950">{{ $stats->points }} Poin</h3>
            </div>
        </div>
    </div>

    <!-- Categories Filter -->
    <div class="flex gap-3 overflow-x-auto pb-4 no-scrollbar">
        @foreach($categories as $category)
        <button
            @click="activeCategory = '{{ $category }}'"
            :class="activeCategory === '{{ $category }}' ? 'bg-[#174413] text-white shadow-xl shadow-green-100' : 'bg-white text-gray-600 border border-gray-100 hover:bg-gray-50'"
            class="px-8 py-3 rounded-2xl font-black text-sm transition-all flex-shrink-0"
        >
            {{ $category }}
        </button>
        @endforeach
    </div>

    <!-- Article Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <template x-for="article in filteredArticles" :key="article.id">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col hover:shadow-xl transition-all duration-300 group">
                <div class="h-56 relative overflow-hidden">
                    <img :src="article.image" :alt="article.title" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute top-4 left-4">
                        <span class="bg-white/90 backdrop-blur-md text-gray-900 px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-tighter shadow-sm" x-text="article.category"></span>
                    </div>
                </div>
                <div class="p-8 flex-1 flex flex-col">
                    <div class="flex items-center text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4 gap-4">
                        <span class="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3 text-green-600"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> <span x-text="article.author"></span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3 text-orange-600"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> <span x-text="article.readTime"></span>
                        </span>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 mb-3 leading-tight group-hover:text-[#174413] transition-colors" x-text="article.title"></h3>
                    <p class="text-gray-500 text-sm font-medium line-clamp-3 mb-6 flex-1 leading-relaxed" x-text="article.content"></p>

                    <div class="pt-6 border-t border-gray-50 flex items-center justify-between mt-auto">
                        <button class="text-[#174413] font-black text-sm flex items-center gap-1.5 hover:gap-3 transition-all group/btn">
                            Baca Selengkapnya
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </button>
                        <button @click="handleShare(article.title)" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-green-50 hover:text-green-600 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="filteredArticles.length === 0" class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200 shadow-inner" style="display: none;">
        <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 text-gray-300"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
        </div>
        <h3 class="text-xl font-black text-gray-900 mb-2">Tidak ada artikel ditemukan</h3>
        <p class="text-gray-500 font-medium">Coba gunakan kata kunci lain atau reset filter Anda.</p>
        <button
            @click="searchQuery = ''; activeCategory = 'Semua'"
            class="mt-6 bg-[#174413] text-white px-8 py-3 rounded-2xl font-bold shadow-lg shadow-green-100 hover:bg-[#256020] transition"
        >
            Reset Pencarian
        </button>
    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection
