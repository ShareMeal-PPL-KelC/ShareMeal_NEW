@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Back Button -->
    <a href="{{ route('consumer.education') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-[#174413] font-bold transition-colors group">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 group-hover:-translate-x-1 transition-transform"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Kembali ke Edukasi
    </a>

    <!-- Article Header -->
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest">
                {{ $article->category }}
            </span>
            <span class="text-gray-400 text-sm font-medium">• {{ $article->readTime }}</span>
        </div>
        <h1 class="text-4xl md:text-5xl font-black text-gray-900 leading-tight">
            {{ $article->title }}
        </h1>
        <div class="flex items-center gap-4 pt-2">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-green-700"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </div>
            <div>
                <p class="text-gray-900 font-bold leading-none">{{ $article->author }}</p>
                <p class="text-gray-400 text-xs mt-1">{{ \Carbon\Carbon::parse($article->date)->format('d M, Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Featured Image -->
    <div class="rounded-[2rem] overflow-hidden shadow-2xl shadow-green-100 h-[400px]">
        <img src="{{ $article->image }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
    </div>

    <!-- Article Content -->
    <div class="prose prose-lg max-w-none text-gray-600 font-medium leading-relaxed">
        <p class="text-xl text-gray-900 font-bold mb-6">
            {{ Str::limit($article->content, 150) }}
        </p>
        <p>
            {{ $article->content }}
        </p>
        <p>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
        </p>
        
        <blockquote class="border-l-4 border-green-500 pl-6 my-8 italic text-gray-900 text-2xl font-black">
            "Menyelamatkan makanan bukan hanya soal menghemat uang, tapi soal menghargai sumber daya bumi kita."
        </blockquote>

        <p>
            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.
        </p>
    </div>

    <!-- Share & Actions -->
    <div class="flex flex-col md:flex-row items-center justify-between border-t border-b border-gray-100 py-8 gap-6">
        <div class="flex items-center gap-4">
            <span class="text-gray-900 font-black">Bagikan Artikel:</span>
            <div class="flex gap-2">
                <button class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center hover:bg-green-50 hover:text-green-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                </button>
                <button class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center hover:bg-green-50 hover:text-green-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path></svg>
                </button>
                <button class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center hover:bg-green-50 hover:text-green-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>
                </button>
            </div>
        </div>
        <button class="flex items-center gap-2 bg-[#174413] text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-green-100 hover:bg-[#256020] transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
            Simpan ke Bookmark
        </button>
    </div>

    <!-- Related Articles -->
    <div class="space-y-6 pt-8">
        <h2 class="text-2xl font-black text-gray-900">Artikel Terkait</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($relatedArticles as $related)
            <a href="{{ route('consumer.education.show', $related->id) }}" class="group block bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
                <div class="h-48 overflow-hidden">
                    <img src="{{ $related->image }}" alt="{{ $related->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <div class="p-6">
                    <span class="text-[10px] font-black uppercase tracking-widest text-green-600">{{ $related->category }}</span>
                    <h3 class="text-lg font-black text-gray-900 mt-2 leading-tight group-hover:text-[#174413] transition-colors">
                        {{ $related->title }}
                    </h3>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
@endsection