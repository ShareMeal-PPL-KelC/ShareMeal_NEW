@extends('layouts.dashboard')

@section('content')
<div class="space-y-6 py-4" x-data="{
    zoomOpen: false,
    zoomUrl: '',
    confirmOpen: false,
    confirmTitle: '',
    confirmMessage: '',
    confirmIcon: '',
    confirmIconBg: '',
    confirmBtnText: '',
    confirmBtnClass: '',
    pendingForm: null,
    openConfirm(title, message, icon, iconBg, btnText, btnClass, formId) {
        this.confirmTitle = title;
        this.confirmMessage = message;
        this.confirmIcon = icon;
        this.confirmIconBg = iconBg;
        this.confirmBtnText = btnText;
        this.confirmBtnClass = btnClass;
        this.pendingForm = document.getElementById(formId);
        this.confirmOpen = true;
    },
    submitConfirmed() {
        if (this.pendingForm) {
            this.pendingForm.submit();
        }
        this.confirmOpen = false;
    }
}">
    <!-- Header -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 font-serif">Feedback &amp; Masukan Pengguna</h1>
            <p class="text-gray-600 mt-1">Kelola dan pelajari masukan yang dikirimkan oleh Consumer, Mitra, dan Lembaga.</p>
        </div>
    </div>

    <!-- Success Alerts -->
    @if(session('success'))
        <div class="rounded-xl border border-green-150 bg-green-50 p-4 text-sm font-medium text-green-800 flex items-center gap-3 shadow-sm animate-in fade-in slide-in-from-top duration-350">
            <div class="bg-green-100 p-1.5 rounded-lg text-green-700">
                <i data-lucide="check" class="w-5 h-5"></i>
            </div>
            <div>
                {{ session('success') }}
            </div>
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-gray-100 luxury-shadow p-6">
        <form action="{{ route('admin.feedbacks.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                <!-- Search Input -->
                <div class="space-y-1 sm:col-span-2 md:col-span-1">
                    <label for="search" class="text-xs font-bold text-gray-500 uppercase tracking-wider">Cari Masukan</label>
                    <div class="relative">
                        <input type="text" name="search" id="search" value="{{ request('search') }}" 
                               placeholder="Subjek, isi, nama user..."
                               class="w-full rounded-xl border-gray-200 bg-gray-50/50 p-3 pl-10 text-sm focus:border-luxury-forest focus:ring-luxury-forest">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="space-y-1">
                    <label for="category" class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kategori</label>
                    <select name="category" id="category" 
                            class="w-full rounded-xl border-gray-200 bg-gray-50/50 p-3 text-sm focus:border-luxury-forest focus:ring-luxury-forest">
                        <option value="">Semua Kategori</option>
                        <option value="fitur" {{ request('category') === 'fitur' ? 'selected' : '' }}>Fitur Baru</option>
                        <option value="bug" {{ request('category') === 'bug' ? 'selected' : '' }}>Laporan Bug</option>
                        <option value="ui_ux" {{ request('category') === 'ui_ux' ? 'selected' : '' }}>Tampilan / UI/UX</option>
                        <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Lain-lain</option>
                    </select>
                </div>

                <!-- Role Filter -->
                <div class="space-y-1">
                    <label for="role" class="text-xs font-bold text-gray-500 uppercase tracking-wider">Role Pengirim</label>
                    <select name="role" id="role" 
                            class="w-full rounded-xl border-gray-200 bg-gray-50/50 p-3 text-sm focus:border-luxury-forest focus:ring-luxury-forest">
                        <option value="">Semua Role</option>
                        <option value="consumer" {{ request('role') === 'consumer' ? 'selected' : '' }}>Consumer</option>
                        <option value="mitra" {{ request('role') === 'mitra' ? 'selected' : '' }}>Mitra</option>
                        <option value="lembaga" {{ request('role') === 'lembaga' ? 'selected' : '' }}>Lembaga</option>
                    </select>
                </div>

                <!-- Rating Filter -->
                <div class="space-y-1">
                    <label for="rating" class="text-xs font-bold text-gray-500 uppercase tracking-wider">Rating</label>
                    <select name="rating" id="rating" 
                            class="w-full rounded-xl border-gray-200 bg-gray-50/50 p-3 text-sm focus:border-luxury-forest focus:ring-luxury-forest">
                        <option value="">Semua Rating</option>
                        <option value="5" {{ request('rating') === '5' ? 'selected' : '' }}>5 Bintang (Sangat Puas)</option>
                        <option value="4" {{ request('rating') === '4' ? 'selected' : '' }}>4 Bintang (Puas)</option>
                        <option value="3" {{ request('rating') === '3' ? 'selected' : '' }}>3 Bintang (Biasa Saja)</option>
                        <option value="2" {{ request('rating') === '2' ? 'selected' : '' }}>2 Bintang (Kecewa)</option>
                        <option value="1" {{ request('rating') === '1' ? 'selected' : '' }}>1 Bintang (Sangat Kecewa)</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="space-y-1">
                    <label for="status" class="text-xs font-bold text-gray-500 uppercase tracking-wider">Status</label>
                    <select name="status" id="status" 
                            class="w-full rounded-xl border-gray-200 bg-gray-50/50 p-3 text-sm focus:border-luxury-forest focus:ring-luxury-forest">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Belum Selesai (Pending)</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Sudah Selesai (Resolved)</option>
                    </select>
                </div>
            </div>

            <!-- Form Action Buttons -->
            <div class="flex justify-end gap-3 pt-2">
                @if(request()->anyFilled(['search', 'category', 'role', 'rating', 'status']))
                    <a href="{{ route('admin.feedbacks.index') }}" 
                       class="inline-flex items-center justify-center gap-2 px-5 py-3 border border-gray-200 text-gray-700 font-semibold rounded-xl bg-white hover:bg-gray-50 transition-colors text-sm">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset Filter
                    </a>
                @endif
                <button type="submit" 
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-luxury-forest text-white font-bold rounded-xl hover:bg-emerald-950 transition-colors text-sm shadow-sm">
                    <i data-lucide="filter" class="w-4 h-4"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Feedbacks List / Grid -->
    <div class="space-y-6">
        @if($feedbacks->isEmpty())
            <div class="bg-white rounded-3xl border border-gray-100 luxury-shadow p-12 text-center">
                <div class="bg-luxury-ivory w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border border-luxury-alabas text-gray-400">
                    <i data-lucide="message-square" class="w-8 h-8"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Belum Ada Feedback</h3>
                <p class="text-gray-500 mt-2 max-w-md mx-auto text-sm">Tidak ada feedback yang cocok dengan kriteria filter yang Anda tentukan.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6">
                @foreach($feedbacks as $feedback)
                    {{-- Hidden forms for actions --}}
                    <form id="toggle-form-{{ $feedback->id }}" 
                          action="{{ route('admin.feedbacks.toggle-status', $feedback) }}" 
                          method="POST" class="hidden">
                        @csrf
                    </form>
                    <form id="delete-form-{{ $feedback->id }}" 
                          action="{{ route('admin.feedbacks.delete', $feedback) }}" 
                          method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>

                    <div class="bg-white rounded-3xl border border-gray-100 luxury-shadow p-6 flex flex-col md:flex-row justify-between gap-6 hover:border-gray-250 transition-all duration-300">
                        <div class="flex-1 space-y-4">
                            <!-- Upper metadata: sender info, category badge, and role badge -->
                            <div class="flex flex-wrap items-center gap-3">
                                <!-- User Role Badge -->
                                @if($feedback->user->role === 'consumer')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        <i data-lucide="user" class="w-3.5 h-3.5"></i> Consumer
                                    </span>
                                @elseif($feedback->user->role === 'mitra')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-100">
                                        <i data-lucide="store" class="w-3.5 h-3.5"></i> Mitra
                                    </span>
                                @elseif($feedback->user->role === 'lembaga')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                        <i data-lucide="heart" class="w-3.5 h-3.5"></i> Lembaga
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-50 text-gray-750 border border-gray-150">
                                        <i data-lucide="shield" class="w-3.5 h-3.5"></i> Admin
                                    </span>
                                @endif

                                <!-- Category Badge -->
                                @if($feedback->category === 'fitur')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-650 border border-blue-100">
                                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Fitur Baru
                                    </span>
                                @elseif($feedback->category === 'bug')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-650 border border-red-100">
                                        <i data-lucide="bug" class="w-3.5 h-3.5"></i> Laporan Bug
                                    </span>
                                @elseif($feedback->category === 'ui_ux')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-650 border border-purple-100">
                                        <i data-lucide="palette" class="w-3.5 h-3.5"></i> Tampilan / UI
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-50 text-gray-600 border border-gray-200">
                                        <i data-lucide="help-circle" class="w-3.5 h-3.5"></i> Lain-lain
                                    </span>
                                @endif

                                <!-- Status Badge -->
                                @if($feedback->status === 'resolved')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-150">
                                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> SELESAI
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-yellow-50 text-yellow-800 border border-yellow-150">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> BELUM SELESAI
                                    </span>
                                @endif

                                <!-- Dot Separator -->
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>

                                <!-- Timestamps -->
                                <span class="text-xs text-gray-500 font-medium flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                    {{ $feedback->created_at->diffForHumans() }} ({{ $feedback->created_at->format('d M Y H:i') }})
                                </span>
                            </div>

                            <!-- User details -->
                            <div class="flex items-center gap-3">
                                <img src="{{ $feedback->user->image ?? 'https://ui-avatars.com/api/?name='.urlencode($feedback->user->name).'&color=7F9CF5&background=EBF4FF' }}" 
                                     alt="Avatar {{ $feedback->user->name }}" 
                                     class="w-10 h-10 rounded-full object-cover ring-2 ring-gray-100">
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">{{ $feedback->user->name }}</h4>
                                    <p class="text-xs text-gray-500">{{ $feedback->user->email }}</p>
                                </div>
                            </div>

                            <!-- Rating stars -->
                            <div class="flex items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" 
                                         class="w-5 h-5 {{ $i <= $feedback->rating ? 'text-amber-400 fill-amber-400' : 'text-gray-200 fill-none' }}" 
                                         stroke="currentColor" stroke-width="2">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                    </svg>
                                @endfor
                                <span class="text-xs font-bold text-gray-500 ml-2">
                                    @if($feedback->rating === 1) Sangat Kecewa
                                    @elseif($feedback->rating === 2) Kecewa
                                    @elseif($feedback->rating === 3) Biasa Saja
                                    @elseif($feedback->rating === 4) Puas
                                    @elseif($feedback->rating === 5) Sangat Puas
                                    @endif
                                </span>
                            </div>

                            <!-- Subject & Description -->
                            <div class="space-y-1.5">
                                <h3 class="text-lg font-bold text-gray-900 font-serif">{{ $feedback->subject }}</h3>
                                <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-wrap">{{ $feedback->description }}</p>
                            </div>

                            <!-- Screenshots attachment (with Lightbox toggle) -->
                            @if($feedback->screenshots && count($feedback->screenshots) > 0)
                                <div class="pt-2 space-y-2">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Lampiran Gambar ({{ count($feedback->screenshots) }}):</p>
                                    <div class="flex flex-wrap gap-3">
                                        @foreach($feedback->screenshots as $screenshot)
                                            <button type="button" 
                                                    @click="zoomOpen = true; zoomUrl = '{{ asset('storage/' . $screenshot) }}'"
                                                    class="group relative block rounded-2xl overflow-hidden border border-gray-150 w-28 h-28 hover:shadow-md transition-all duration-300 bg-gray-50 flex-shrink-0">
                                                <img src="{{ asset('storage/' . $screenshot) }}" 
                                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" 
                                                     alt="Screenshot feedback">
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white">
                                                    <i data-lucide="zoom-in" class="w-5 h-5"></i>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Card Action buttons (Admin Panel) -->
                        <div class="flex md:flex-col justify-end items-end md:justify-start gap-3 border-t md:border-t-0 pt-4 md:pt-0 border-gray-100 min-w-[150px]">
                            <!-- Toggle Status Button -->
                            @if($feedback->status === 'resolved')
                                <button type="button"
                                        @click="openConfirm(
                                            'Batalkan Status Selesai?',
                                            'Apakah Anda yakin ingin membatalkan status selesai untuk feedback dari &lt;strong&gt;{{ addslashes($feedback->user->name) }}&lt;/strong&gt;? Feedback ini akan dikembalikan ke status belum selesai.',
                                            'rotate-ccw',
                                            'bg-amber-100 text-amber-700',
                                            'Ya, Batalkan',
                                            'bg-amber-500 hover:bg-amber-600 text-white font-bold px-5 py-2.5 rounded-xl transition-colors text-sm',
                                            'toggle-form-{{ $feedback->id }}'
                                        )"
                                        class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 border border-amber-200 text-amber-700 hover:bg-amber-50 font-bold rounded-xl transition-colors text-xs">
                                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Batalkan
                                </button>
                            @else
                                <button type="button"
                                        @click="openConfirm(
                                            'Tandai Feedback Selesai?',
                                            'Apakah Anda yakin ingin menandai feedback dari &lt;strong&gt;{{ addslashes($feedback->user->name) }}&lt;/strong&gt; sebagai &lt;strong&gt;Selesai&lt;/strong&gt;? Tindakan ini menandakan bahwa masukan telah diproses.',
                                            'check-circle',
                                            'bg-emerald-100 text-emerald-700',
                                            'Ya, Tandai Selesai',
                                            'bg-luxury-forest hover:bg-emerald-950 text-white font-bold px-5 py-2.5 rounded-xl transition-colors text-sm shadow-sm',
                                            'toggle-form-{{ $feedback->id }}'
                                        )"
                                        class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-luxury-forest text-white hover:bg-emerald-950 font-bold rounded-xl transition-colors text-xs shadow-sm">
                                    <i data-lucide="check-circle" class="w-4 h-4"></i> Tandai Selesai
                                </button>
                            @endif

                            <!-- Delete Button -->
                            <button type="button"
                                    @click="openConfirm(
                                        'Hapus Feedback?',
                                        'Apakah Anda yakin ingin &lt;strong&gt;menghapus permanen&lt;/strong&gt; feedback dari &lt;strong&gt;{{ addslashes($feedback->user->name) }}&lt;/strong&gt;? Tindakan ini tidak dapat dibatalkan.',
                                        'trash-2',
                                        'bg-red-100 text-red-600',
                                        'Ya, Hapus Permanen',
                                        'bg-red-600 hover:bg-red-700 text-white font-bold px-5 py-2.5 rounded-xl transition-colors text-sm',
                                        'delete-form-{{ $feedback->id }}'
                                    )"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 border border-red-200 text-red-650 hover:bg-red-50 font-bold rounded-xl transition-colors text-xs">
                                <i data-lucide="trash-2" class="w-4 h-4"></i> Hapus Feedback
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination Links -->
            <div class="pt-4">
                {{ $feedbacks->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================================ -->
    <!-- Custom Confirmation Modal -->
    <!-- ============================================================ -->
    <div x-show="confirmOpen"
         x-cloak
         @keydown.escape.window="confirmOpen = false"
         class="fixed inset-0 z-[200] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="confirmOpen = false"></div>

        <!-- Modal Panel -->
        <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 translate-y-4"
             @click.stop>

            <!-- Top accent stripe -->
            <div class="h-1.5 w-full bg-gradient-to-r from-luxury-forest via-emerald-400 to-luxury-gold"></div>

            <div class="p-8">
                <!-- Icon -->
                <div class="flex items-center justify-center mb-5">
                    <div :class="confirmIconBg" class="w-16 h-16 rounded-2xl flex items-center justify-center shadow-sm">
                        <i :data-lucide="confirmIcon" class="w-8 h-8" x-effect="if (confirmOpen) { $nextTick(() => lucide.createIcons()) }"></i>
                    </div>
                </div>

                <!-- Title -->
                <h2 class="text-xl font-bold text-gray-900 text-center font-serif mb-3" x-text="confirmTitle"></h2>

                <!-- Message -->
                <p class="text-sm text-gray-600 text-center leading-relaxed mb-7" x-html="confirmMessage"></p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button"
                            @click="confirmOpen = false"
                            class="flex-1 px-5 py-2.5 border border-gray-200 text-gray-700 font-semibold rounded-xl bg-white hover:bg-gray-50 transition-colors text-sm">
                        Batal
                    </button>
                    <button type="button"
                            @click="submitConfirmed()"
                            :class="confirmBtnClass"
                            class="flex-1">
                        <span x-text="confirmBtnText"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- Lightbox Zoom Modal -->
    <!-- ============================================================ -->
    <div x-show="zoomOpen" 
         @keydown.escape.window="zoomOpen = false"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/75 p-4 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-250"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-cloak>
        <div class="relative max-w-5xl max-h-[90vh] overflow-hidden rounded-3xl bg-white p-2 shadow-2xl" @click.away="zoomOpen = false">
            <button type="button" @click="zoomOpen = false" 
                    class="absolute top-4 right-4 p-2 bg-black/60 hover:bg-black/80 text-white rounded-full transition-colors z-10 shadow-sm focus:outline-none">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <img :src="zoomUrl" class="max-w-full max-h-[85vh] object-contain rounded-2xl" alt="Screenshot Zoomed">
        </div>
    </div>
</div>
@endsection
