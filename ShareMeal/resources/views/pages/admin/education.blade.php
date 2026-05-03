@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen pb-20" x-data="{ showModal: false, editMode: false, currentArticle: {} }">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-green-700 font-bold text-xs uppercase tracking-[0.2em]">
                <span class="w-8 h-[2px] bg-green-700"></span>
                Content Hub
            </div>
            <h1 class="text-4xl font-black text-gray-900 tracking-tight leading-none">
                Edukasi <span class="text-green-700">Lingkungan</span>
            </h1>
            <p class="text-gray-500 font-medium">Kelola artikel, tips, dan panduan edukasi seputar food waste (PBI 27)</p>
        </div>
        <button @click="showModal = true; editMode = false; currentArticle = { title: '', category: 'Tips', status: 'Draft', content: '' }" 
                class="bg-[#1a4414] text-white px-6 py-4 rounded-2xl hover:shadow-lg transition-all flex items-center gap-2 font-bold text-sm">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tulis Artikel Baru
        </button>
    </div>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Artikel</p>
            <h3 class="text-3xl font-black text-gray-900 mt-1">{{ count($articles) }}</h3>
        </div>
        <div class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Published</p>
            <h3 class="text-3xl font-black text-green-600 mt-1">{{ count(array_filter($articles, fn($a) => $a['status'] === 'Published')) }}</h3>
        </div>
        <div class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Drafts</p>
            <h3 class="text-3xl font-black text-orange-600 mt-1">{{ count(array_filter($articles, fn($a) => $a['status'] === 'Draft')) }}</h3>
        </div>
    </div>

    <!-- Article List -->
    <div class="space-y-6">
        @foreach($articles as $article)
        <div class="bg-white border border-gray-100 rounded-[32px] p-6 hover:shadow-md transition-all">
            <div class="flex flex-col md:flex-row gap-8 items-center">
                <div class="w-full md:w-48 h-32 rounded-2xl overflow-hidden bg-gray-100">
                    <img src="{{ $article['image'] }}" class="w-full h-full object-cover" alt="Preview">
                </div>
                <div class="flex-1 space-y-2">
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 bg-green-50 text-green-700 rounded-full text-[10px] font-black uppercase tracking-widest">
                            {{ $article['category'] }}
                        </span>
                        <span class="text-xs font-bold text-gray-400">{{ $article['date'] }}</span>
                    </div>
                    <h3 class="text-xl font-black text-gray-900">{{ $article['title'] }}</h3>
                    <p class="text-sm text-gray-500 line-clamp-1">{{ $article['content'] }}</p>
                </div>
                <div class="flex gap-2">
                    <button @click="showModal = true; editMode = true; currentArticle = {{ json_encode($article) }}" 
                            class="p-3 bg-gray-50 text-gray-700 rounded-xl hover:bg-green-50 hover:text-green-700 transition-colors">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </button>
                    <button class="p-3 bg-gray-50 text-red-600 rounded-xl hover:bg-red-50 transition-colors">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Modal Form (Hidden) -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-[40px] w-full max-w-2xl p-10 shadow-2xl">
            <h3 class="text-2xl font-black text-gray-900 mb-8" x-text="editMode ? 'Edit Artikel' : 'Tulis Artikel Baru'"></h3>
            <div class="space-y-6">
                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Judul Artikel</label>
                    <input type="text" x-model="currentArticle.title" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl mt-2">
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Kategori</label>
                        <select x-model="currentArticle.category" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl mt-2">
                            <option>Tips</option>
                            <option>Artikel</option>
                            <option>Panduan</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Status</label>
                        <select x-model="currentArticle.status" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl mt-2">
                            <option>Published</option>
                            <option>Draft</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Isi Konten</label>
                    <textarea x-model="currentArticle.content" rows="6" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl mt-2"></textarea>
                </div>
                <div class="flex gap-4 pt-4">
                    <button @click="showModal = false" class="flex-1 py-4 bg-gray-100 text-gray-500 rounded-2xl font-bold">Batal</button>
                    <button class="flex-1 py-4 bg-[#1a4414] text-white rounded-2xl font-bold shadow-lg">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
