@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen pb-20" 
     x-data="{ 
        showModal: false, 
        editMode: false, 
        currentArticle: {},
        activeTab: '{{ $tab }}',
        init() {
            lucide.createIcons();
        }
     }">
    
    <!-- Decorative Background Elements -->
    <div class="fixed top-0 right-0 -z-10 opacity-10 pointer-events-none">
        <div class="w-[500px] h-[500px] bg-green-300 rounded-full blur-[120px]"></div>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-green-700 font-bold text-xs uppercase tracking-[0.2em]">
                <span class="w-8 h-[2px] bg-green-700"></span>
                Admin Content Hub
            </div>
            <h1 class="text-4xl font-black text-gray-900 tracking-tight leading-none">
                Edukasi <span class="text-green-700">Lingkungan</span>
            </h1>
            <p class="text-gray-500 font-medium">Kelola artikel, tips, dan panduan edukasi seputar food waste (FR-19)</p>
        </div>
        <button @click="showModal = true; editMode = false; currentArticle = { title: '', category: 'Tips', status: 'Draft', content: '' }" 
                class="group bg-[#1a4414] text-white px-6 py-4 rounded-2xl hover:shadow-[0_20px_40px_-15px_rgba(26,68,20,0.3)] transition-all duration-500 flex items-center gap-3 font-bold text-sm">
            <div class="bg-white/20 p-1 rounded-lg group-hover:rotate-90 transition-transform duration-500">
                <i data-lucide="plus" class="w-4 h-4"></i>
            </div>
            Tulis Artikel Baru
        </button>
    </div>

    <!-- Stats Section with Hover Effects -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        @php
            $statsItems = [
                ['label' => 'Total Artikel', 'value' => $stats['total'], 'icon' => 'file-text', 'color' => 'blue', 'desc' => 'Konten terdaftar'],
                ['label' => 'Published', 'value' => $stats['published'], 'icon' => 'check-circle', 'color' => 'green', 'desc' => 'Aktif di platform'],
                ['label' => 'Drafts', 'value' => $stats['drafts'], 'icon' => 'edit-3', 'color' => 'orange', 'desc' => 'Menunggu review'],
            ];
        @endphp

        @foreach($statsItems as $item)
        <div class="group bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-500 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-{{ $item['color'] }}-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative z-10 flex flex-col gap-4">
                <div class="w-14 h-14 bg-{{ $item['color'] }}-50 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                    <i data-lucide="{{ $item['icon'] }}" class="w-7 h-7 text-{{ $item['color'] }}-500"></i>
                </div>
                <div>
                    <h3 class="text-4xl font-black text-gray-900 mb-1">{{ $item['value'] }}</h3>
                    <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">{{ $item['label'] }}</p>
                    <p class="text-xs text-gray-400 mt-2 font-medium">{{ $item['desc'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Main Content Area -->
    <div class="bg-white rounded-[40px] border border-gray-100 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.05)] overflow-hidden">
        <!-- Control Bar -->
        <div class="p-8 border-b border-gray-50 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 bg-gray-50/30">
            <div class="flex items-center gap-2 p-1.5 bg-gray-100 rounded-2xl w-max">
                @foreach(['all' => 'Semua', 'published' => 'Published', 'draft' => 'Draft'] as $key => $label)
                <a href="?tab={{ $key }}" 
                   class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 {{ $tab === $key ? 'bg-white text-gray-900 shadow-lg shadow-gray-200/50 scale-105' : 'text-gray-500 hover:text-gray-700 hover:bg-white/50' }}">
                    {{ $label }}
                </a>
                @endforeach
            </div>

            <div class="relative w-full lg:w-96 group">
                <i data-lucide="search" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-green-600 transition-colors"></i>
                <form action="{{ route('admin.education') }}" method="GET">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul, kategori, atau penulis..." 
                           class="w-full pl-12 pr-6 py-4 bg-white border-2 border-gray-100 rounded-2xl focus:border-green-500 focus:ring-0 transition-all duration-300 text-sm font-medium placeholder-gray-400 shadow-sm">
                </form>
            </div>
        </div>

        <!-- Article List -->
        <div class="p-8 space-y-6">
            @forelse($articles as $article)
            <div class="group relative bg-white border-2 border-gray-50 rounded-[32px] p-6 hover:border-green-200 hover:shadow-2xl hover:shadow-green-900/5 transition-all duration-500">
                <div class="flex flex-col xl:flex-row gap-8 items-start xl:items-center">
                    <!-- Image Preview -->
                    <div class="w-full xl:w-48 h-32 rounded-2xl overflow-hidden flex-shrink-0 bg-gray-100 relative group-hover:scale-[1.02] transition-transform duration-500">
                        <img src="{{ $article['image'] ?? 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&q=80&w=400' }}" 
                             class="w-full h-full object-cover" alt="Preview">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </div>

                    <!-- Content Info -->
                    <div class="flex-1 space-y-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $article['category'] === 'Tips' ? 'bg-blue-50 text-blue-600' : ($article['category'] === 'Panduan' ? 'bg-purple-50 text-purple-600' : 'bg-green-50 text-green-600') }}">
                                {{ $article['category'] }}
                            </span>
                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                            <span class="text-xs font-bold text-gray-400 flex items-center gap-1">
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                {{ $article['date'] }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ strtolower($article['status']) === 'published' ? 'bg-green-500 text-white' : 'bg-orange-500 text-white' }}">
                                {{ $article['status'] }}
                            </span>
                        </div>

                        <h3 class="text-2xl font-black text-gray-900 group-hover:text-green-700 transition-colors leading-tight">
                            {{ $article['title'] }}
                        </h3>

                        <div class="flex items-center gap-6">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden">
                                    <i data-lucide="user" class="w-3 h-3 text-gray-400"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-500">{{ $article['author'] }}</span>
                            </div>
                            <p class="text-sm text-gray-400 font-medium line-clamp-1 max-w-md italic">
                                "{{ Str::limit(strip_tags($article['content']), 80) }}"
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex xl:flex-col gap-3 w-full xl:w-auto">
                        <button @click="showModal = true; editMode = true; currentArticle = {{ json_encode($article) }}" 
                                class="flex-1 xl:flex-none flex items-center justify-center gap-2 px-6 py-3 bg-gray-50 text-gray-700 rounded-2xl font-bold text-sm hover:bg-green-50 hover:text-green-700 transition-all duration-300">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                            Edit
                        </button>
                        <form action="{{ route('admin.education.delete', $article['id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus konten ini secara permanen?')" class="flex-1 xl:flex-none">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 text-red-100 hover:text-white bg-red-500/10 hover:bg-red-500 rounded-2xl transition-all duration-300">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="py-24 text-center">
                <div class="relative w-48 h-48 mx-auto mb-8">
                    <div class="absolute inset-0 bg-green-50 rounded-full animate-pulse"></div>
                    <div class="absolute inset-4 bg-green-100 rounded-full"></div>
                    <i data-lucide="newspaper" class="w-20 h-20 text-green-200 absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2"></i>
                </div>
                <h3 class="text-2xl font-black text-gray-900">Belum ada konten</h3>
                <p class="text-gray-500 mt-2 font-medium max-w-sm mx-auto">Mulai bangun engagement pengguna dengan artikel edukasi yang inspiratif.</p>
                <button @click="showModal = true" class="mt-8 px-8 py-3 bg-green-700 text-white rounded-2xl font-bold hover:bg-green-800 transition-colors">Buat Artikel Pertama</button>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Premium Modal Form -->
    <div x-show="showModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
        
        <div class="absolute inset-0 bg-gray-900/80 backdrop-blur-md" @click="showModal = false"></div>
        
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 scale-90 translate-y-10"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             class="relative bg-white rounded-[48px] w-full max-w-3xl shadow-2xl overflow-hidden">
            
            <form :action="editMode ? '{{ url('admin/education') }}/' + currentArticle.id : '{{ route('admin.education.store') }}'" method="POST">
                @csrf
                <div class="px-10 pt-10 pb-6 flex justify-between items-start">
                    <div>
                        <span class="text-green-600 font-black text-[10px] uppercase tracking-widest" x-text="editMode ? 'Content Update' : 'New Creation'"></span>
                        <h3 class="text-3xl font-black text-gray-900 mt-1" x-text="editMode ? 'Edit Artikel' : 'Tulis Artikel'"></h3>
                    </div>
                    <button type="button" @click="showModal = false" class="w-12 h-12 flex items-center justify-center bg-gray-50 hover:bg-gray-100 rounded-full transition-colors">
                        <i data-lucide="x" class="w-6 h-6 text-gray-400"></i>
                    </button>
                </div>
                
                <div class="px-10 pb-10 space-y-8 max-h-[65vh] overflow-y-auto custom-scrollbar">
                    <div class="space-y-3">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Judul Artikel Konten</label>
                        <input type="text" name="title" x-model="currentArticle.title" required 
                               class="w-full px-6 py-5 bg-gray-50 border-2 border-transparent focus:border-green-500 focus:bg-white rounded-3xl transition-all outline-none font-bold text-gray-900">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Kategori Konten</label>
                            <select name="category" x-model="currentArticle.category" required 
                                    class="w-full px-6 py-5 bg-gray-50 border-2 border-transparent focus:border-green-500 focus:bg-white rounded-3xl transition-all outline-none font-bold text-gray-900 appearance-none">
                                <option>Tips</option>
                                <option>Artikel</option>
                                <option>Panduan</option>
                                <option>Edukasi</option>
                            </select>
                        </div>
                        <div class="space-y-3">
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Status Publikasi</label>
                            <select name="status" x-model="currentArticle.status" required 
                                    class="w-full px-6 py-5 bg-gray-50 border-2 border-transparent focus:border-green-500 focus:bg-white rounded-3xl transition-all outline-none font-bold text-gray-900 appearance-none">
                                <option value="Published">Published</option>
                                <option value="Draft">Draft</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Isi Konten Lengkap</label>
                        <textarea name="content" x-model="currentArticle.content" rows="8" required 
                                  class="w-full px-6 py-6 bg-gray-50 border-2 border-transparent focus:border-green-500 focus:bg-white rounded-[32px] transition-all outline-none font-medium text-gray-700 leading-relaxed resize-none"></textarea>
                    </div>
                </div>

                <div class="px-10 py-8 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-400 font-bold max-w-[200px]">Pastikan konten sudah sesuai dengan pedoman komunitas.</p>
                    <div class="flex gap-4">
                        <button type="button" @click="showModal = false" class="px-8 py-4 text-sm font-bold text-gray-500 hover:text-gray-900 transition">Batal</button>
                        <button type="submit" class="px-10 py-4 bg-[#1a4414] text-white rounded-[24px] font-bold shadow-xl shadow-green-900/20 hover:scale-105 active:scale-95 transition-all duration-300">
                            <span x-text="editMode ? 'Simpan Perubahan' : 'Terbitkan Sekarang'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>
@endsection
