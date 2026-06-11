@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="educationPage()">
    <!-- Header Hero Section -->
    <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-[#12360f] to-[#1c5317] p-8 md:p-12 text-white border border-white/10 shadow-2xl reveal">
        <!-- Internal Blobs -->
        <div class="absolute top-[-20%] left-[-10%] w-[25rem] h-[25rem] bg-emerald-400/20 rounded-full blur-[80px]"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[30rem] h-[30rem] bg-lime-400/15 rounded-full blur-[90px]"></div>

        <div class="relative z-10 md:w-2/3">
            <span class="bg-white/10 text-emerald-300 border border-white/10 backdrop-blur px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest mb-6 inline-block">
                Edukasi Lingkungan
            </span>
            <h1 class="text-4xl md:text-5xl font-black mb-4 leading-tight font-serif text-white">
                Mari Bersama Kurangi Food Waste
            </h1>
            <p class="text-emerald-100 text-lg mb-8 max-w-xl font-medium opacity-90 leading-relaxed">
                Tingkatkan pengetahuanmu tentang dampak sampah makanan dan temukan tips praktis 
                untuk mulai menyelamatkan makanan hari ini.
            </p>
            <div class="relative max-w-md group">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-luxury-gold transition-transform group-focus-within:scale-110"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input 
                    type="text" 
                    placeholder="Cari edukasi atau topik..." 
                    x-model="searchQuery"
                    class="w-full pl-12 pr-4 py-4 bg-white/95 text-luxury-forest rounded-2xl outline-none focus:ring-4 focus:ring-emerald-500/30 transition shadow-lg font-medium"
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
        <div class="glass-card glass-card-hover p-6 rounded-2xl flex items-center gap-5 bg-emerald-50/20 reveal delay-100">
            <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 border border-emerald-200/50 flex items-center justify-center shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
            </div>
            <div>
                <p class="text-xs text-emerald-800 font-black uppercase tracking-widest">Edukasi Dibaca</p>
                <h3 class="text-2xl font-black text-luxury-forest font-serif mt-1">{{ $stats->readCount }} Edukasi</h3>
            </div>
        </div>
        <div class="glass-card glass-card-hover p-6 rounded-2xl flex items-center gap-5 bg-blue-50/20 reveal delay-200">
            <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-600 border border-blue-200/50 flex items-center justify-center shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>
            </div>
            <div>
                <p class="text-xs text-blue-800 font-black uppercase tracking-widest">Level Edukasi</p>
                <h3 class="text-2xl font-black text-luxury-forest font-serif mt-1">{{ $stats->level }}</h3>
            </div>
        </div>
        <div class="glass-card glass-card-hover p-6 rounded-2xl flex items-center gap-5 bg-orange-50/20 reveal delay-300">
            <div class="w-14 h-14 rounded-2xl bg-orange-100 text-orange-600 border border-orange-200/50 flex items-center justify-center shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"></path><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"></path></svg>
            </div>
            <div>
                <p class="text-xs text-orange-800 font-black uppercase tracking-widest">Poin Pengetahuan</p>
                <h3 class="text-2xl font-black text-luxury-forest font-serif mt-1">{{ $stats->points }} Poin</h3>
            </div>
        </div>
    </div>

    <!-- Categories Filter -->
    <div class="flex gap-3 overflow-x-auto pb-4 no-scrollbar reveal">
        @foreach($categories as $category)
        <button
            @click="activeCategory = '{{ addslashes($category) }}'"
            :class="activeCategory === '{{ addslashes($category) }}' ? 'bg-[#174413] text-white shadow-md' : 'bg-white/60 text-gray-600 border border-luxury-alabas/80 hover:bg-white hover:text-[#174413]'"
            class="px-8 py-3 rounded-2xl font-black text-sm transition-all flex-shrink-0">
            {{ $category }}
        </button>
        @endforeach
    </div>

    <!-- Article Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <template x-for="(article, index) in filteredArticles" :key="article.id">
            <div class="glass-card glass-card-hover rounded-3xl overflow-hidden flex flex-col hover:shadow-xl transition-all duration-300 group reveal">
                <div class="h-56 relative overflow-hidden">
                    <img :src="article.image" :alt="article.title" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute top-4 left-4">
                        <span class="bg-white/90 backdrop-blur-md text-gray-900 px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-tighter shadow-sm" x-text="article.category"></span>
                    </div>
                </div>
                <div class="p-8 flex-1 flex flex-col bg-white/10">
                    <div class="flex items-center text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4 gap-4">
                        <span class="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3 text-green-600"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> <span x-text="article.author"></span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3 text-orange-600"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> <span x-text="article.readTime"></span>
                        </span>
                    </div>
                    <a :href="'/consumer/education/' + article.id" class="block group">
                        <h3 class="text-xl font-bold font-serif text-luxury-forest mb-3 leading-tight group-hover:text-luxury-gold transition-colors" x-text="article.title"></h3>
                    </a>
                    <p class="text-luxury-slate text-sm font-medium line-clamp-3 mb-6 flex-1 leading-relaxed" x-text="article.content"></p>
                    
                    <div class="pt-6 border-t border-luxury-alabas/50 flex items-center justify-between mt-auto">
                        <a :href="'/consumer/education/' + article.id" class="text-luxury-forest font-black text-sm flex items-center gap-1.5 hover:gap-3 transition-all group/btn">
                            Baca Selengkapnya 
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </a>
                        <button @click="handleShare(article.title)" class="w-10 h-10 rounded-xl bg-white/60 text-gray-400 flex items-center justify-center hover:bg-white hover:text-green-600 transition border border-luxury-alabas/80 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="filteredArticles.length === 0" class="glass-card p-20 text-center" style="display: none;">
        <div class="bg-white/80 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 border border-luxury-alabas/70 luxury-shadow">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 text-gray-300"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
        </div>
        <h3 class="text-xl font-serif font-bold text-luxury-forest mb-2">Tidak ada edukasi ditemukan</h3>
        <p class="text-luxury-slate font-medium">Coba gunakan kata kunci lain atau reset filter Anda.</p>
        <button 
            @click="searchQuery = ''; activeCategory = 'Semua'"
            class="mt-6 bg-[#174413] text-white px-8 py-3 rounded-2xl font-bold shadow-md hover:bg-luxury-gold transition"
        >
            Reset Pencarian
        </button>
    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script>
function educationPage() {
    var source = @json($articles);

    function doFilter(articles, query, category) {
        var q   = query.trim().toLowerCase();
        var cat = category.trim().toLowerCase();
        return articles.filter(function(a) {
            if (!a) return false;
            var title   = String(a.title    || '').toLowerCase();
            var content = String(a.content  || '').toLowerCase();
            var artCat  = String(a.category || '').trim().toLowerCase();
            var okSearch = q === '' || title.indexOf(q) !== -1 || content.indexOf(q) !== -1;
            var okCat    = cat === 'semua' || artCat === cat;
            return okSearch && okCat;
        });
    }

    return {
        searchQuery:      '',
        activeCategory:   'Semua',
        allArticles:      source,
        filteredArticles: source.slice(),

        init: function() {
            var self = this;
            this.$watch('searchQuery', function() {
                self.filteredArticles = doFilter(self.allArticles, self.searchQuery, self.activeCategory);
            });
            this.$watch('activeCategory', function() {
                self.filteredArticles = doFilter(self.allArticles, self.searchQuery, self.activeCategory);
            });
        },

        handleShare: function(title) {
            alert('Tautan untuk \'' + title + '\' berhasil disalin!');
        }
    };
}
</script>
@endsection