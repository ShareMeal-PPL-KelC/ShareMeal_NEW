@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="{ showModal: false, editMode: false, currentArticle: {} }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">{{ $shell['title'] }}</h1>
            <p class="text-gray-500 mt-1">{{ $shell['subtitle'] }}</p>
        </div>
        <button @click="showModal = true; editMode = false; currentArticle = { title: '', category: 'Tips', status: 'Draft', content: '' }" 
                class="bg-[#174413] text-white px-5 py-2.5 rounded-xl shadow-sm hover:opacity-90 transition flex items-center gap-2 font-bold cursor-pointer">
            <i data-lucide="plus" class="w-5 h-5"></i>
            Buat Artikel Baru
        </button>
    </div>

    <!-- Filters & Stats -->
    <div class="flex flex-col sm:flex-row gap-4 justify-between items-center bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex p-1 bg-gray-50 rounded-xl w-full sm:w-auto">
            <a href="?tab=all" class="px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $tab === 'all' ? 'bg-white text-[#174413] shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">Semua</a>
            <a href="?tab=published" class="px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $tab === 'published' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">Published</a>
            <a href="?tab=draft" class="px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $tab === 'draft' ? 'bg-white text-orange-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">Draft</a>
        </div>
        <div class="relative w-full sm:w-72">
            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            <form action="{{ route('admin.education') }}" method="GET">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul atau kategori..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
            </form>
        </div>
    </div>

    <!-- Article Table -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50/50 text-gray-500 font-bold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Judul Artikel</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($articles as $article)
                    <tr class="hover:bg-gray-50/50 transition group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                                    <img src="{{ $article['image'] ?? 'https://via.placeholder.com/150' }}" class="w-full h-full object-cover">
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900 group-hover:text-green-700 transition">{{ $article['title'] }}</span>
                                    <span class="text-xs text-gray-500">Oleh {{ $article['author'] }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded bg-blue-50 text-blue-600 text-[10px] font-bold uppercase tracking-wider">{{ $article['category'] }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if(strtolower($article['status']) === 'published')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-50 text-green-700 border border-green-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Published
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-orange-50 text-orange-700 border border-orange-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Draft
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-500">
                            {{ $article['date'] }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button @click="showModal = true; editMode = true; currentArticle = {{ json_encode($article) }}" 
                                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition cursor-pointer" title="Edit">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                <form action="{{ route('admin.education.delete', $article['id']) }}" method="POST" onsubmit="return confirm('Hapus artikel ini?')">
                                    @csrf
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Hapus">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="bg-gray-50 w-16 h-16 rounded-full flex items-center justify-center mb-4">
                                    <i data-lucide="book-open" class="w-8 h-8 text-gray-300"></i>
                                </div>
                                <h3 class="text-gray-900 font-bold">Belum Ada Artikel</h3>
                                <p class="text-sm text-gray-500 mt-1">Mulai buat konten edukasi untuk pengguna ShareMeal.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form -->
    <div x-show="showModal" class="fixed inset-0 z-[60] overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" @click="showModal = false">
                <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
            </div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <form :action="editMode ? '{{ url('admin/education') }}/' + currentArticle.id : '{{ route('admin.education.store') }}'" method="POST">
                    @csrf
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900" x-text="editMode ? 'Edit Artikel' : 'Buat Artikel Baru'"></h3>
                        <button type="button" @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <div class="space-y-1">
                            <label class="text-sm font-bold text-gray-700">Judul Artikel</label>
                            <input type="text" name="title" x-model="currentArticle.title" required 
                                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-sm font-bold text-gray-700">Kategori</label>
                                <select name="category" x-model="currentArticle.category" required 
                                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none">
                                    <option>Tips</option>
                                    <option>Artikel</option>
                                    <option>Panduan</option>
                                    <option>Edukasi</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-bold text-gray-700">Status</label>
                                <select name="status" x-model="currentArticle.status" required 
                                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none">
                                    <option value="Published">Published</option>
                                    <option value="Draft">Draft</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-bold text-gray-700">Konten Artikel</label>
                            <textarea name="content" x-model="currentArticle.content" rows="8" required 
                                      class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none resize-none"></textarea>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition">Batal</button>
                        <button type="submit" class="px-6 py-2 bg-[#174413] text-white font-bold rounded-xl hover:opacity-90 transition" x-text="editMode ? 'Simpan Perubahan' : 'Terbitkan Artikel'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
