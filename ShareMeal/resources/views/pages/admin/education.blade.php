@extends('layouts.dashboard')

@section('content')
<div class="space-y-8" x-data="{
    showModal: false,
    editMode: false,
    currentArticle: {},
    imagePreview: null,

    openCreate() {
        this.editMode = false;
        this.currentArticle = { title: '', category: 'Tips', status: 'Draft', content: '' };
        this.imagePreview = null;
        this.showModal = true;
    },

    openEdit(article) {
        this.editMode = true;
        this.currentArticle = article;
        this.imagePreview = article.image || null;
        this.showModal = true;
    },

    handleImageChange(event) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => { this.imagePreview = e.target.result; };
        reader.readAsDataURL(file);
    }
}">
    <!-- Decorative Glow -->
    <div class="absolute top-0 right-0 w-[30rem] h-[30rem] bg-emerald-100/10 rounded-full blur-[120px] pointer-events-none z-0"></div>

    <!-- Title Header -->
    <div class="relative z-10 mb-10 reveal">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
            <div>
                <h1 class="text-5xl font-serif font-black text-luxury-forest leading-tight tracking-tight">Konten Edukasi</h1>
                <p class="text-luxury-slate font-medium mt-3 text-lg leading-relaxed max-w-3xl">Kelola edukasi dan panduan edukatif untuk pengguna platform ShareMeal.</p>
            </div>
            <button @click="openCreate()"
                    class="flex items-center gap-2.5 px-6 py-3.5 bg-gradient-to-r from-[#174413] to-emerald-600 text-white rounded-2xl text-xs font-black uppercase tracking-wider hover:from-emerald-800 hover:to-emerald-700 transition active:scale-95 shadow-md shadow-emerald-950/10 cursor-pointer shrink-0">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Buat Edukasi Baru
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="relative z-10 bg-emerald-50 border border-emerald-200 text-emerald-700 px-6 py-4 rounded-[1.5rem] flex items-center gap-3 animate-in fade-in duration-300">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0 stroke-[2.5]"></i>
        <span class="text-sm font-semibold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Filter & Search Bar -->
    <div class="glass-card rounded-[2rem] p-5 relative z-10 reveal">
        <div class="flex flex-col sm:flex-row items-center gap-4">
            <!-- Tab Pills -->
            <div class="flex p-1.5 bg-white/60 border border-luxury-alabas/70 rounded-2xl gap-1 shrink-0">
                <a href="?tab=all"
                   class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-300
                          {{ $tab === 'all' ? 'bg-gradient-to-r from-[#174413] to-emerald-600 text-white shadow-md' : 'text-luxury-slate hover:text-luxury-forest hover:bg-white/70' }}">
                    Semua
                </a>
                <a href="?tab=published"
                   class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-300
                          {{ $tab === 'published' ? 'bg-gradient-to-r from-emerald-500 to-green-500 text-white shadow-md' : 'text-luxury-slate hover:text-luxury-forest hover:bg-white/70' }}">
                    Published
                </a>
                <a href="?tab=draft"
                   class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-300
                          {{ $tab === 'draft' ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-white shadow-md' : 'text-luxury-slate hover:text-luxury-forest hover:bg-white/70' }}">
                    Draft
                </a>
            </div>

            <!-- Search -->
            <div class="relative flex-1 w-full">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-luxury-slate/50"></i>
                <form action="{{ route('admin.education') }}" method="GET">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul atau kategori..."
                           class="w-full pl-11 pr-4 py-3.5 border border-luxury-alabas/85 rounded-2xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 outline-none bg-white/40 font-medium text-luxury-forest placeholder:text-luxury-slate/40 text-sm transition duration-300">
                </form>
            </div>
        </div>
    </div>

    <!-- Article Table -->
    <div class="glass-card rounded-[2rem] overflow-hidden relative z-10 reveal">
        <!-- Table Header -->
        <div class="px-8 py-5 border-b border-luxury-alabas/60">
            <h2 class="text-xl font-serif font-black text-luxury-forest">Daftar Edukasi</h2>
            <p class="text-xs text-luxury-slate font-medium mt-0.5">{{ count($articles) }} edukasi ditemukan</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr class="bg-white/30 border-b border-luxury-alabas/50">
                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-luxury-slate">Judul Edukasi</th>
                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-luxury-slate">Kategori</th>
                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-luxury-slate">Status</th>
                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-luxury-slate">Tanggal</th>
                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-luxury-slate text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-alabas/40">
                    @forelse($articles as $article)
                    <tr class="hover:bg-emerald-50/30 transition-colors duration-200 group">
                        <!-- Judul -->
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl overflow-hidden shrink-0 bg-gray-100 border border-luxury-alabas/50 shadow-sm">
                                    @if($article['image'])
                                        <img src="{{ $article['image'] }}" class="w-full h-full object-cover" alt="{{ $article['title'] }}">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-tr from-[#174413]/10 to-emerald-50 flex items-center justify-center">
                                            <i data-lucide="book-open" class="w-5 h-5 text-emerald-600"></i>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-bold text-luxury-forest group-hover:text-emerald-700 transition text-sm leading-snug">{{ $article['title'] }}</div>
                                    <div class="text-xs text-luxury-slate font-medium mt-0.5">Oleh {{ $article['author'] }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Kategori -->
                        <td class="px-8 py-5">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200/50">
                                {{ $article['category'] }}
                            </span>
                        </td>

                        <!-- Status -->
                        <td class="px-8 py-5">
                            @if(strtolower($article['status']) === 'published')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Published
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Draft
                                </span>
                            @endif
                        </td>

                        <!-- Tanggal -->
                        <td class="px-8 py-5">
                            <div class="text-sm font-bold text-luxury-forest">{{ $article['date'] }}</div>
                        </td>

                        <!-- Aksi -->
                        <td class="px-8 py-5">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="openEdit({{ json_encode($article) }})"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 border border-luxury-alabas/85 text-luxury-forest hover:bg-blue-50 hover:border-blue-200 hover:text-blue-700 rounded-xl transition duration-300 text-xs font-black uppercase tracking-wider active:scale-95 cursor-pointer">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    Edit
                                </button>
                                <form action="{{ route('admin.education.delete', $article['id']) }}" method="POST"
                                      onsubmit="return confirm('Hapus edukasi ini?')">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 border border-red-100 text-red-500 hover:bg-red-50 hover:border-red-200 rounded-xl transition duration-300 text-xs font-black uppercase tracking-wider active:scale-95 cursor-pointer">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gradient-to-tr from-[#174413]/5 to-emerald-50 rounded-2xl flex items-center justify-center mb-4 border border-luxury-alabas/80">
                                    <i data-lucide="book-open" class="w-8 h-8 text-luxury-forest/40"></i>
                                </div>
                                <p class="font-serif font-black text-xl text-luxury-forest mb-1">Belum Ada Edukasi</p>
                                <p class="text-sm text-luxury-slate font-medium">Mulai buat konten edukasi untuk pengguna ShareMeal.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== MODAL FORM ARTIKEL ===== -->
    <div x-show="showModal"
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-[#0d1f0d]/60 backdrop-blur-md" @click="showModal = false"></div>

        <!-- Panel -->
        <div x-show="showModal"
             x-transition:enter="ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 translate-y-10 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-10 scale-95"
             class="relative w-full max-w-4xl bg-white rounded-[3rem] shadow-2xl z-10 overflow-hidden border border-white/70 max-h-[90vh] overflow-y-auto"
             @click.stop>

            <!-- Header Gradient -->
            <div class="h-24 bg-gradient-to-br from-[#174413] via-emerald-700 to-green-500 relative overflow-hidden shrink-0">
                <div class="absolute inset-0 opacity-20"
                     style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px), radial-gradient(circle at 80% 20%, white 1px, transparent 1px); background-size: 40px 40px;"></div>
                <div class="absolute inset-0 flex items-center px-8 justify-between">
                    <div>
                        <h3 class="text-xl font-serif font-black text-white" x-text="editMode ? 'Edit Edukasi' : 'Buat Edukasi Baru'"></h3>
                        <p class="text-white/70 text-xs font-medium mt-0.5" x-text="editMode ? 'Perbarui konten edukasi yang ada.' : 'Isi detail edukasi baru.'"></p>
                    </div>
                    <button type="button" @click="showModal = false"
                            class="w-9 h-9 flex items-center justify-center rounded-full bg-white/20 hover:bg-white/30 text-white transition cursor-pointer border border-white/30">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Form -->
            <form :action="editMode ? '{{ url('admin/education') }}/' + currentArticle.id : '{{ route('admin.education.store') }}'" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Left Column (Form Fields & Cover Upload) -->
                    <div class="space-y-5">
                        <!-- Judul -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-luxury-gold uppercase tracking-wider block">Judul Edukasi</label>
                            <input type="text" name="title" x-model="currentArticle.title" required
                                   placeholder="Masukkan judul edukasi..."
                                   class="w-full px-5 py-4 border border-luxury-alabas/85 rounded-2xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 outline-none bg-gray-50 font-medium text-luxury-forest placeholder:text-luxury-slate/40 transition duration-300 text-sm">
                        </div>

                        <!-- Kategori & Status (Grid) -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-luxury-gold uppercase tracking-wider block">Kategori</label>
                                <select name="category" x-model="currentArticle.category" required
                                        class="w-full px-5 py-4 border border-luxury-alabas/85 rounded-2xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 outline-none bg-gray-50 font-bold text-luxury-forest transition duration-300 text-sm cursor-pointer">
                                    <option>Tips</option>
                                    <option>Artikel</option>
                                    <option>Panduan</option>
                                    <option>Edukasi</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-luxury-gold uppercase tracking-wider block">Status</label>
                                <select name="status" x-model="currentArticle.status" required
                                        class="w-full px-5 py-4 border border-luxury-alabas/85 rounded-2xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 outline-none bg-gray-50 font-bold text-luxury-forest transition duration-300 text-sm cursor-pointer">
                                    <option value="Published">Published</option>
                                    <option value="Draft">Draft</option>
                                </select>
                            </div>
                        </div>

                        <!-- Cover Image Upload -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-luxury-gold uppercase tracking-wider block">
                                Gambar Cover
                                <span class="text-luxury-slate/60 normal-case font-medium ml-1">(opsional, maks. 2MB · JPG / PNG / WEBP)</span>
                            </label>

                            <!-- Preview jika ada -->
                            <div x-show="imagePreview" class="relative w-full h-40 rounded-2xl overflow-hidden border border-luxury-alabas/60 shadow-sm">
                                <img :src="imagePreview" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                                <button type="button"
                                        @click="imagePreview = null; $refs.imageInput.value = ''"
                                        class="absolute top-3 right-3 w-8 h-8 bg-white/90 text-red-500 rounded-xl flex items-center justify-center hover:bg-white transition shadow-sm cursor-pointer">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                                <span class="absolute bottom-3 left-3 text-white text-[10px] font-black uppercase tracking-wider bg-black/40 backdrop-blur-sm px-2 py-1 rounded-lg">Preview</span>
                            </div>

                            <!-- Dropzone -->
                            <label x-show="!imagePreview" class="w-full flex flex-col items-center justify-center gap-3 px-5 py-8 border-2 border-dashed rounded-2xl cursor-pointer transition duration-300 border-luxury-alabas/70 bg-gray-50 hover:border-emerald-300 hover:bg-emerald-50/20">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-gray-100 text-luxury-slate">
                                    <i data-lucide="image-plus" class="w-5 h-5"></i>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs font-black text-luxury-forest">Klik untuk upload gambar cover</p>
                                    <p class="text-[10px] text-luxury-slate font-medium mt-0.5">atau seret & lepas file ke sini</p>
                                </div>
                                <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                                       x-ref="imageInput"
                                       @change="handleImageChange($event)"
                                       class="hidden">
                            </label>
                        </div>
                    </div>

                    <!-- Right Column (Content) -->
                    <div class="flex flex-col h-full space-y-2">
                        <label class="text-[10px] font-black text-luxury-gold uppercase tracking-wider block">Konten Edukasi</label>
                        <textarea name="content" x-model="currentArticle.content" required
                                  placeholder="Tulis isi edukasi di sini..."
                                  class="w-full flex-1 px-5 py-4 border border-luxury-alabas/85 rounded-2xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 outline-none bg-gray-50 font-medium text-luxury-forest placeholder:text-luxury-slate/40 transition duration-300 text-sm resize-none min-h-[300px] md:min-h-0 md:h-[calc(100%-1.75rem)]"></textarea>
                    </div>

                </div>

                <!-- Footer Buttons -->
                <div class="px-8 pb-8 flex gap-3">
                    <button type="button" @click="showModal = false"
                            class="flex-1 py-4 px-6 text-[10px] font-black uppercase tracking-[0.2em] text-luxury-slate border border-luxury-alabas/70 rounded-2xl hover:bg-gray-50 transition active:scale-95 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 py-4 px-6 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] text-white bg-gradient-to-r from-[#174413] to-emerald-600 hover:from-emerald-800 hover:to-emerald-700 transition active:scale-95 shadow-md shadow-emerald-950/10 cursor-pointer"
                            x-text="editMode ? 'Simpan Perubahan' : 'Terbitkan Edukasi'">
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- ===== END MODAL ===== -->

</div>
@endsection
