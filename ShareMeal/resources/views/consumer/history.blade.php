@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="{ 
    isReviewDialogOpen: false,
    selectedOrderId: null,
    rating: 0,
    review: '',
    openReviewModal(id) {
        this.selectedOrderId = id;
        this.isReviewDialogOpen = true;
    },
    submitReview() {
        if (this.rating === 0) {
            alert('Pilih rating terlebih dahulu');
            return;
        }
        alert('Review untuk ' + this.selectedOrderId + ' berhasil dikirim!');
        this.isReviewDialogOpen = false;
        this.rating = 0;
        this.review = '';
    }
}">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Riwayat Transaksi</h1>
        <p class="text-gray-600 mt-1">Pantau status pesanan dan berikan ulasan Anda</p>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm text-center">
            <div class="text-3xl font-black text-green-600">{{ count($transactions) }}</div>
            <div class="text-xs text-gray-500 font-bold uppercase mt-1">Total Transaksi</div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm text-center">
            <div class="text-3xl font-black text-blue-600">
                Rp {{ number_format(collect($transactions)->sum('discount'), 0, ',', '.') }}
            </div>
            <div class="text-xs text-gray-500 font-bold uppercase mt-1">Total Hemat</div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm text-center">
            <div class="text-3xl font-black text-yellow-500">
                @php 
                    $rated = collect($transactions)->filter(fn($t) => $t->rating > 0);
                    $avg = $rated->count() > 0 ? $rated->avg('rating') : 0;
                @endphp
                {{ number_format($avg, 1) }} ⭐
            </div>
            <div class="text-xs text-gray-500 font-bold uppercase mt-1">Rata-rata Rating</div>
        </div>
    </div>

    <!-- Transaction List -->
    <div class="space-y-6">
        @foreach($transactions as $t)
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-8 space-y-6">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h3 class="text-2xl font-black text-gray-900">{{ $t->store }}</h3>
                            @if($t->status === 'completed')
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-[10px] font-black uppercase flex items-center gap-1 border border-green-200">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                Selesai
                            </span>
                            @else
                            <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-[10px] font-black uppercase flex items-center gap-1 border border-orange-200">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                Pending
                            </span>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-4 mt-3 text-sm text-gray-500 font-medium">
                            <span class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-gray-400"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                {{ $t->orderTime }}
                            </span>
                            <span class="bg-gray-100 px-2 py-0.5 rounded text-xs font-mono text-gray-600">#{{ $t->orderId }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 mt-1.5 text-sm text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            {{ $t->storeAddress }}
                        </div>
                    </div>
                    <div class="text-right flex flex-col items-end">
                        <div class="text-3xl font-black text-gray-900 leading-none">Rp {{ number_format($t->total, 0, ',', '.') }}</div>
                        <div class="text-sm text-gray-400 line-through mt-2">Rp {{ number_format($t->subtotal, 0, ',', '.') }}</div>
                        <div class="text-sm text-green-600 font-black mt-1 bg-green-50 px-2 py-0.5 rounded-lg inline-block">
                            Hemat Rp {{ number_format($t->discount, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div class="border-t border-gray-50 pt-6">
                    <h4 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-4">Item Pesanan</h4>
                    <div class="space-y-3">
                        @foreach($t->items as $item)
                        <div class="flex items-center justify-between text-sm font-medium">
                            <div class="text-gray-700">
                                <span class="text-gray-900">{{ $item->name }}</span>
                                <span class="text-gray-400 ml-1">× {{ $item->quantity }}</span>
                            </div>
                            <div class="text-gray-900 font-bold">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Pickup Code -->
                @if($t->status === 'pending')
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><rect x="7" y="7" width="3" height="9"></rect><rect x="14" y="7" width="3" height="5"></rect></svg>
                        </div>
                        <div>
                            <div class="font-bold text-blue-900 text-sm">Kode Pengambilan Anda</div>
                            <div class="text-xs text-blue-700">Tunjukkan kode ini kepada mitra saat mengambil pesanan</div>
                        </div>
                    </div>
                    <div class="font-mono font-black text-xl text-blue-800 bg-white px-3 py-1.5 rounded-lg border border-blue-200">
                        {{ $t->pickupCode }}
                    </div>
                </div>
                @endif

                <!-- Review Section -->
                @if($t->rating > 0)
                <div class="border-t border-gray-50 pt-6 bg-yellow-50/30 -mx-8 px-8 pb-6">
                    <div class="flex items-start gap-3">
                        <div class="flex gap-1 mt-1">
                            @for($i = 1; $i <= 5; $i++)
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 {{ $i <= $t->rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-200 fill-transparent' }}"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            @endfor
                        </div>
                        <div class="flex-1">
                            <div class="text-xs font-black uppercase text-yellow-700 mb-1">Rating Anda</div>
                            @if($t->review)
                            <p class="text-sm text-gray-700 italic font-medium">"{{ $t->review }}"</p>
                            @endif
                        </div>
                    </div>
                </div>
                @elseif($t->status === 'completed')
                <div class="border-t border-gray-50 pt-6">
                    <button @click="openReviewModal('{{ $t->orderId }}')" class="w-full flex items-center justify-center gap-2 border-2 border-dashed border-gray-200 py-3 rounded-2xl text-gray-400 font-bold text-sm hover:border-green-600 hover:text-green-600 transition group">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 group-hover:fill-green-600"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        Berikan Rating & Ulasan
                    </button>
                </div>
                @endif

                <div class="flex gap-3 pt-4">
                    <button class="flex-1 bg-white border border-gray-200 text-gray-700 px-4 py-3 rounded-2xl font-bold text-sm hover:bg-gray-50 transition flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Unduh Bukti
                    </button>
                    <a href="{{ route('consumer.search') }}" class="flex-1 bg-[#174413] text-white px-4 py-3 rounded-2xl font-bold text-sm hover:bg-[#256020] transition flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                        Pesan Lagi
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Review Modal Overlay -->
    <div x-show="isReviewDialogOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white w-full max-w-md rounded-3xl p-8 shadow-2xl space-y-6" @click.away="isReviewDialogOpen = false">
            <div class="text-center">
                <h3 class="text-2xl font-black text-gray-900">Rating & Ulasan</h3>
                <p class="text-gray-500 text-sm mt-1">Bagikan pengalaman belanja Anda</p>
            </div>

            <div class="space-y-4">
                <div class="text-center">
                    <label class="text-sm font-bold text-gray-400 uppercase tracking-widest block mb-3">Rating</label>
                    <div class="flex justify-center gap-2">
                        <template x-for="i in 5">
                            <button @click="rating = i" class="transition transform hover:scale-125">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10" :class="i <= rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-200 fill-transparent'"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-400 uppercase tracking-widest">Ulasan</label>
                    <textarea x-model="review" rows="4" class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 outline-none focus:ring-2 focus:ring-green-600 transition" placeholder="Ceritakan rasa makanannya..."></textarea>
                </div>

                <div class="pt-4 flex gap-3">
                    <button @click="isReviewDialogOpen = false" class="flex-1 border border-gray-100 py-4 rounded-2xl font-bold text-gray-400 hover:bg-gray-50 transition">Batal</button>
                    <button @click="submitReview()" class="flex-1 bg-green-600 text-white py-4 rounded-2xl font-bold shadow-lg shadow-green-100 hover:bg-green-700 transition">Kirim Review</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection