@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="{
    isReviewDialogOpen: false,
    selectedOrderId: null,
    editingReviewId: null,
    isEditMode: false,
    rating: 0,
    review: '',
    openReviewModal(id) {
        this.isEditMode = false;
        this.selectedOrderId = id;
        this.editingReviewId = null;
        this.rating = 0;
        this.review = '';
        this.isReviewDialogOpen = true;
    },
    openEditReviewModal(reviewId, currentRating, currentComment) {
        this.isEditMode = true;
        this.editingReviewId = reviewId;
        this.rating = currentRating;
        this.review = currentComment;
        this.isReviewDialogOpen = true;
    },
    submitReview() {
        if (this.rating === 0) {
            alert('Pilih rating terlebih dahulu');
            return;
        }
        // This method is now handled by standard form submission
    },
    isReceiptDialogOpen: false,
    receiptData: null,
    openReceiptModal(data) {
        this.receiptData = data;
        this.isReceiptDialogOpen = true;
    },
    downloadReceipt() {
        alert('Memulai unduhan struk untuk pesanan ' + this.receiptData.id + '...');
        setTimeout(() => {
            this.isReceiptDialogOpen = false;
        }, 500);
    },
    isReportDialogOpen: false,
    selectedOrderForReport: null,
    issueType: '',
    description: '',
    openReportModal(order) {
        this.selectedOrderForReport = order;
        this.issueType = 'bad_quality';
        this.description = '';
        this.isReportDialogOpen = true;
    }
}">
    <div class="mb-12 reveal">
        <h1 class="text-5xl font-serif font-bold text-luxury-forest leading-tight">My Collections</h1>
        <p class="text-luxury-slate font-medium mt-2 tracking-wide">Pantau kontribusi Anda dalam menyelamatkan surplus pangan berkualitas.</p>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
        <div class="glass-card glass-card-hover p-10 rounded-[2.5rem] group hover:bg-luxury-forest/90 transition-all duration-700 reveal delay-100">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 bg-luxury-ivory rounded-2xl flex items-center justify-center mb-6 group-hover:bg-white/10 transition-colors">
                    <i data-lucide="shopping-bag" class="w-7 h-7 text-luxury-gold"></i>
                </div>
                <div class="text-4xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">{{ count($transactions) }}</div>
                <div class="text-[10px] text-luxury-gold mt-3 font-black uppercase tracking-[0.2em]">Total Orders</div>
            </div>
        </div>
        <div class="glass-card glass-card-hover p-10 rounded-[2.5rem] group hover:bg-luxury-emerald/90 transition-all duration-700 reveal delay-200">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 bg-luxury-ivory rounded-2xl flex items-center justify-center mb-6 group-hover:bg-white/10 transition-colors">
                    <i data-lucide="sparkles" class="w-7 h-7 text-luxury-gold"></i>
                </div>
                <div class="text-4xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">Rp {{ number_format(collect($transactions)->sum('savedAmount') / 1000, 0) }}k</div>
                <div class="text-[10px] text-luxury-gold mt-3 font-black uppercase tracking-[0.2em]">Eco Savings</div>
            </div>
        </div>
        <div class="glass-card glass-card-hover p-10 rounded-[2.5rem] group hover:bg-luxury-gold/90 transition-all duration-700 reveal delay-300">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 bg-luxury-ivory rounded-2xl flex items-center justify-center mb-6 group-hover:bg-white/10 transition-colors">
                    <i data-lucide="star" class="w-7 h-7 text-luxury-emerald"></i>
                </div>
                <div class="text-4xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">
                    @php
                        $rated = collect($transactions)->filter(fn($t) => $t->rating > 0);
                        $avg = $rated->count() > 0 ? $rated->avg('rating') : 0;
                    @endphp
                    {{ number_format($avg, 1) }}
                </div>
                <div class="text-[10px] text-luxury-forest group-hover:text-white mt-3 font-black uppercase tracking-[0.2em]">Average Appreciation</div>
            </div>
        </div>
    </div>

    <!-- Transaction List -->
    <div class="space-y-12">
        @foreach($transactions as $t)
        <div class="glass-card glass-card-hover rounded-[3.5rem] overflow-hidden reveal delay-{{ $loop->iteration * 100 }}">
            <div class="p-10 lg:p-12">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-10">
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-6 mb-6">
                            <h3 class="text-3xl font-serif font-bold text-luxury-forest leading-none">{{ $t->store }}</h3>
                            @if($t->status === 'completed')
                            <span class="bg-luxury-emerald/10 text-luxury-emerald px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-luxury-emerald/20">
                                Settle
                            </span>
                            @else
                            <span class="bg-orange-50 text-orange-700 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-orange-100">
                                In Process
                            </span>
                            @endif
                        </div>
                        
                        <div class="space-y-3 mb-8">
                            <div class="flex items-center gap-4 text-xs font-bold text-luxury-slate uppercase tracking-widest">
                                <i data-lucide="calendar" class="w-4 h-4 text-luxury-gold"></i>
                                {{ $t->orderTime }}
                                <span class="text-luxury-alabas">•</span>
                                <span class="font-mono text-luxury-forest">#{{ $t->orderId }}</span>
                            </div>
                            <div class="flex items-center gap-4 text-xs font-medium text-luxury-slate italic">
                                <i data-lucide="map-pin" class="w-4 h-4 text-luxury-gold"></i>
                                {{ $t->storeAddress }}
                            </div>
                            <div class="flex items-center gap-4 text-xs font-black text-luxury-emerald uppercase tracking-[0.1em]">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                Window: {{ $t->pickupTime }}
                            </div>
                        </div>

                        @if($t->status === 'pending')
                        <div x-data="{
                            endTime: new Date('{{ $t->expires_at->toIso8601String() }}').getTime(),
                            timeRemaining: '',
                            isExpired: false,
                            init() {
                                this.updateTime();
                                setInterval(() => this.updateTime(), 1000);
                            },
                            updateTime() {
                                const now = new Date().getTime();
                                const distance = this.endTime - now;

                                if (distance < 0) {
                                    this.isExpired = true;
                                    return;
                                }

                                const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                const s = Math.floor((distance % (1000 * 60)) / 1000);

                                this.timeRemaining = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                            }
                        }" class="flex items-center gap-4 p-4 bg-red-50/60 rounded-2xl border border-red-100/50 text-red-650">
                            <i data-lucide="alert-circle" class="w-5 h-5 animate-pulse"></i>
                            <div class="flex-1">
                                <p class="text-[10px] font-black uppercase tracking-widest leading-none mb-1">Consumption Window</p>
                                <span x-show="!isExpired" class="text-sm font-bold">Expires in: <span x-text="timeRemaining" class="font-mono bg-white px-2 py-0.5 rounded-lg border border-red-105 ml-2"></span></span>
                                <span x-show="isExpired" class="text-sm font-bold uppercase tracking-widest" x-cloak>Exceeded</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="text-right">
                        <div class="text-[10px] font-black uppercase tracking-[0.3em] text-luxury-gold mb-2">Total Contribution</div>
                        <div class="text-4xl font-serif font-black text-luxury-forest leading-none">Rp {{ number_format($t->total, 0, ',', '.') }}</div>
                        <div class="mt-4 flex flex-col items-end gap-2">
                            <div class="text-[11px] text-luxury-slate line-through font-bold tracking-widest opacity-50 uppercase">Origin Rp {{ number_format($t->subtotal, 0, ',', '.') }}</div>
                            <div class="text-[10px] text-white font-black uppercase tracking-[0.2em] bg-luxury-emerald px-4 py-1.5 rounded-full shadow-sm">
                                Saved Rp {{ number_format($t->savedAmount, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pickup Code -->
                @if($t->status === 'pending')
                <div class="bg-white/40 border border-luxury-alabas/80 rounded-[2rem] p-8 flex flex-col sm:flex-row items-center justify-between gap-8 mt-10">
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-luxury-gold luxury-shadow">
                            <i data-lucide="qr-code" class="w-8 h-8 stroke-[1.5]"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em] mb-1">Authorization Code</div>
                            <div class="font-serif text-lg font-bold text-luxury-forest">Present this to the boutique curator</div>
                        </div>
                    </div>
                    <div class="font-mono text-4xl font-black text-luxury-forest tracking-tighter bg-white px-8 py-4 rounded-2xl border-2 border-luxury-forest shadow-xl">
                        {{ $t->pickupCode }}
                    </div>
                </div>
                @endif

                <!-- Review Section -->
                @if($t->rating > 0)
                <div class="mt-10 pt-10 border-t border-luxury-alabas/50 relative">
                    <div class="flex flex-col md:flex-row items-start gap-8">
                        <div class="flex gap-1.5 bg-luxury-gold/5 p-3 rounded-2xl border border-luxury-gold/10">
                            @for($i = 1; $i <= 5; $i++)
                            <i data-lucide="star" class="w-5 h-5 {{ $i <= $t->rating ? 'text-luxury-gold fill-luxury-gold' : 'text-luxury-alabas' }}"></i>
                            @endfor
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-[10px] font-black uppercase tracking-[0.3em] text-luxury-gold">Your Appreciation</div>
                                <div class="flex items-center gap-6">
                                    <button @click="openEditReviewModal(@js($t->reviewRelation->id), @js($t->rating), @js($t->review))" class="text-luxury-forest hover:text-luxury-gold text-[10px] font-black uppercase tracking-[0.2em] flex items-center gap-2 transition-colors">
                                        <i data-lucide="edit" class="w-3 h-3"></i>
                                        Refine
                                    </button>
                                    <form action="{{ route('consumer.review.delete', $t->reviewRelation->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-450 hover:text-red-650 text-[10px] font-black uppercase tracking-[0.2em] flex items-center gap-2 transition-colors">
                                            <i data-lucide="trash" class="w-3 h-3"></i>
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @if($t->review)
                            <p class="text-lg font-serif text-luxury-forest italic leading-relaxed">&ldquo;{{ $t->review }}&rdquo;</p>
                            @endif
                        </div>
                    </div>
                </div>
                @elseif($t->status === 'completed')
                <div class="mt-10 pt-10 border-t border-luxury-alabas/50">
                    <button @click="openReviewModal('{{ $t->id }}')" class="w-full flex items-center justify-center gap-3 border-2 border-dashed border-luxury-alabas py-5 rounded-[2rem] text-luxury-slate font-black text-[10px] uppercase tracking-[0.3em] hover:border-luxury-gold hover:text-luxury-gold transition-all duration-500 group bg-white/20">
                        <i data-lucide="pen-tool" class="w-4 h-4 transition-transform group-hover:rotate-12"></i>
                        Share Your Appreciation
                    </button>
                </div>
                @endif

                <div class="flex flex-wrap gap-4 mt-10 pt-10 border-t border-luxury-alabas/50">
                    <button @click="openReceiptModal({
                        id: '{{ $t->orderId }}',
                        store: '{{ $t->store }}',
                        date: '{{ $t->orderTime }}',
                        pickupTime: '{{ $t->pickupTime }}',
                        status: '{{ $t->status }}',
                        subtotal: {{ $t->subtotal }},
                        savedAmount: {{ $t->savedAmount }},
                        total: {{ $t->total }},
                        items: [
                            @foreach($t->items as $item)
                            { name: '{{ $item->product ? addslashes($item->product->name) : addslashes($item->name) }}', qty: {{ $item->quantity }}, price: {{ $item->price }} },
                            @endforeach
                        ]
                    })" class="flex-1 min-w-[160px] bg-white/60 border border-luxury-alabas text-luxury-forest px-8 py-4 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] hover:bg-white hover:text-luxury-gold hover:border-luxury-gold/30 transition-all duration-500 flex items-center justify-center gap-3 shadow-sm">
                        <i data-lucide="file-text" class="w-4 h-4 text-luxury-gold"></i>
                        E-Receipt
                    </button>
                    <button @click="openReportModal({
                        id: '{{ $t->id }}',
                        store: '{{ $t->store }}'
                    })" class="flex-1 min-w-[160px] bg-red-50/50 text-red-650 border border-red-100/50 px-8 py-4 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] hover:bg-red-100 transition-all duration-500 flex items-center justify-center gap-3">
                        <i data-lucide="flag" class="w-4 h-4"></i>
                        Raise Inquiry
                    </button>
                    <a href="{{ route('consumer.search') }}" class="flex-[2] min-w-[200px] bg-luxury-forest text-white px-8 py-4 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] hover:bg-luxury-gold transition-all duration-500 flex items-center justify-center gap-3 luxury-shadow group">
                        <i data-lucide="plus" class="w-4 h-4 text-luxury-gold transition-transform group-hover:scale-125"></i>
                        New Collection
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Review Modal Overlay -->
    <div x-show="isReviewDialogOpen" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" 
         x-cloak>
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-luxury-forest/60 backdrop-blur-md" @click="isReviewDialogOpen = false"></div>

        <!-- Modal Content -->
        <div x-show="isReviewDialogOpen"
             x-transition:enter="ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-y-12 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="relative glass-panel w-full max-w-md rounded-[3rem] overflow-hidden shadow-2xl border border-white/40 p-12">
            
            <div class="text-center mb-10">
                <div class="w-16 h-16 bg-white/80 rounded-full flex items-center justify-center mx-auto mb-6 luxury-shadow">
                    <i data-lucide="award" class="w-8 h-8 text-luxury-gold"></i>
                </div>
                <h3 class="text-3xl font-serif font-bold text-luxury-forest leading-tight">Share Your Experience</h3>
                <p class="text-xs text-luxury-slate font-medium uppercase tracking-[0.2em] mt-2">Apresiasi Anda sangat berarti bagi kami</p>
            </div>

            <form :action="isEditMode ? '{{ url('/consumer/review') }}/' + editingReviewId : '{{ route('consumer.review.submit') }}'" method="POST" class="space-y-8">
                @csrf
                <template x-if="isEditMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="order_id" :value="selectedOrderId">
                <input type="hidden" name="rating" :value="rating">

                <div class="text-center">
                    <div class="flex justify-center gap-3">
                        <template x-for="i in 5">
                            <button type="button" @click="rating = i" class="transition-all duration-300 transform hover:scale-125 group">
                                <i data-lucide="star" 
                                   class="w-10 h-10 transition-colors" 
                                   :class="i <= rating ? 'text-luxury-gold fill-luxury-gold' : 'text-luxury-alabas'"></i>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em]">Your Thoughts</label>
                    <textarea name="comment" x-model="review" rows="4" 
                              class="w-full bg-white/60 border border-luxury-alabas/85 rounded-[1.5rem] p-6 outline-none focus:ring-2 focus:ring-luxury-forest transition-all font-medium text-luxury-forest placeholder:text-luxury-slate/40" 
                              placeholder="Tell us about the flavors and your experience..."></textarea>
                </div>

                <div class="pt-4 flex flex-col gap-4">
                    <button type="submit" :disabled="rating === 0" 
                            class="w-full bg-luxury-forest text-white py-5 rounded-[1.5rem] font-black uppercase tracking-[0.2em] text-[10px] shadow-xl hover:bg-luxury-gold transition-all duration-500 disabled:opacity-30 disabled:cursor-not-allowed" 
                            x-text="isEditMode ? 'Refine Appreciation' : 'Post Appreciation'"></button>
                    <button type="button" @click="isReviewDialogOpen = false" 
                            class="w-full py-4 text-luxury-slate font-black uppercase tracking-[0.2em] text-[10px] hover:text-luxury-forest transition-colors">Discard</button>
                </div>
            </form>
        </div>
    </div>

    <!-- E-Receipt Modal Overlay -->
    <div x-show="isReceiptDialogOpen" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" 
         x-cloak>
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-luxury-forest/65 backdrop-blur-xl" @click="isReceiptDialogOpen = false"></div>

        <!-- Receipt Content -->
        <div x-show="isReceiptDialogOpen"
             x-transition:enter="ease-out duration-700"
             x-transition:enter-start="opacity-0 translate-y-24"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="relative glass-panel w-full max-w-sm rounded-[3.5rem] overflow-hidden shadow-2xl border border-white/40">
            
            <div class="h-1.5 w-full bg-gradient-to-r from-luxury-forest via-luxury-gold to-luxury-emerald"></div>

            <div class="p-10 text-center border-b-2 border-dashed border-luxury-alabas/60 bg-white/30">
                <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center mx-auto mb-6 luxury-shadow">
                    <i data-lucide="leaf" class="w-7 h-7 text-luxury-forest"></i>
                </div>
                <h3 class="text-2xl font-serif font-bold text-luxury-forest tracking-tighter">Collection Receipt</h3>
                <p class="text-[10px] text-luxury-gold font-black uppercase tracking-[0.2em] mt-2" x-text="receiptData ? receiptData.store : ''"></p>
            </div>

            <div class="p-10 space-y-6 bg-white/10">
                <div class="space-y-4" x-show="receiptData">
                    <template x-for="(item, index) in (receiptData ? receiptData.items : [])" :key="index">
                        <div class="flex justify-between items-start gap-4">
                            <div class="flex-1">
                                <div class="text-sm font-bold text-luxury-forest" x-text="item.name"></div>
                                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-widest mt-1" x-text="'Qty: ' + item.qty"></div>
                            </div>
                            <div class="text-sm font-bold text-luxury-forest" x-text="'Rp ' + (item.price * item.qty).toLocaleString('id-ID')"></div>
                        </div>
                    </template>
                </div>

                <div class="pt-6 border-t border-luxury-alabas/60 space-y-3" x-show="receiptData">
                    <div class="flex justify-between text-xs font-medium text-luxury-slate">
                        <span>Original Value</span>
                        <span x-text="'Rp ' + receiptData.subtotal.toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex justify-between text-xs font-bold text-luxury-emerald uppercase tracking-widest">
                        <span>Eco-Contribution</span>
                        <span x-text="'- Rp ' + receiptData.savedAmount.toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <div class="pt-6 border-t-2 border-luxury-forest/10 flex justify-between items-end" x-show="receiptData">
                    <span class="text-[10px] font-black uppercase tracking-[0.3em] text-luxury-gold mb-1">Total Settled</span>
                    <span class="text-3xl font-serif font-black text-luxury-forest leading-none" x-text="'Rp ' + receiptData.total.toLocaleString('id-ID')"></span>
                </div>

                <div class="bg-white/60 rounded-2xl p-4 border border-luxury-alabas/80 text-center shadow-sm" x-show="receiptData">
                    <div class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.2em] mb-1">Authenticated At</div>
                    <div class="font-mono text-[10px] font-bold text-luxury-forest" x-text="receiptData.date"></div>
                </div>
            </div>

            <div class="p-8 bg-white/40 flex gap-4 border-t border-luxury-alabas/60">
                <button @click="isReceiptDialogOpen = false" class="flex-1 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-luxury-slate hover:text-luxury-forest transition-colors">Close</button>
                <button @click="downloadReceipt()" class="flex-1 bg-luxury-forest text-white py-4 rounded-[1rem] font-black uppercase tracking-[0.2em] text-[10px] shadow-lg hover:bg-luxury-gold transition-all duration-500 flex items-center justify-center gap-2">
                    <i data-lucide="download" class="w-3 h-3"></i>
                    Export
                </button>
            </div>
        </div>
    </div>

    <!-- Report Modal Overlay -->
    <div x-show="isReportDialogOpen" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" 
         x-cloak>
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-red-900/40 backdrop-blur-md" @click="isReportDialogOpen = false"></div>

        <!-- Modal Content -->
        <div x-show="isReportDialogOpen"
             x-transition:enter="ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-y-12 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="relative glass-panel w-full max-w-md rounded-[3rem] overflow-hidden shadow-2xl border border-white/40 p-12">
            
            <div class="text-center mb-10">
                <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-red-100">
                    <i data-lucide="shield-alert" class="w-8 h-8 text-red-650"></i>
                </div>
                <h3 class="text-3xl font-serif font-bold text-luxury-forest">Raise Inquiry</h3>
                <p class="text-xs text-luxury-slate font-medium uppercase tracking-[0.2em] mt-2">Help us maintain the highest standards</p>
            </div>

            <form action="{{ route('consumer.report.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-6" @submit="isReportDialogOpen = false">
                @csrf
                <input type="hidden" name="order_id" :value="selectedOrderForReport?.id">

                <div class="space-y-3">
                    <label class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em]">Nature of Issue</label>
                    <select name="issue_type" x-model="issueType" class="w-full bg-white/60 border border-luxury-alabas/85 rounded-[1.2rem] px-6 py-4 outline-none focus:ring-2 focus:ring-red-600 transition-all font-bold text-luxury-forest appearance-none">
                        <option value="bad_quality">Kualitas Buruk / Basi</option>
                        <option value="expired">Sudah Kedaluwarsa</option>
                        <option value="mismatch">Tidak Sesuai Deskripsi</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em]">Detailed Observations</label>
                    <textarea name="description" x-model="description" rows="3" required 
                              class="w-full bg-white/60 border border-luxury-alabas/85 rounded-[1.2rem] p-6 outline-none focus:ring-2 focus:ring-red-600 transition-all font-medium text-luxury-forest placeholder:text-luxury-slate/40" 
                              placeholder="Please describe the incident with precision..."></textarea>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em]">Visual Evidence</label>
                    <input type="file" name="evidence_image" accept="image/*" 
                           class="w-full text-[10px] text-luxury-slate font-bold file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-luxury-forest file:text-white hover:file:bg-luxury-gold file:transition-all">
                </div>

                <div class="pt-4 flex flex-col gap-4">
                    <button type="submit" class="w-full bg-red-600 text-white py-5 rounded-[1.5rem] font-black uppercase tracking-[0.2em] text-[10px] shadow-xl hover:bg-red-750 transition-all duration-500 active:scale-95">Submit Report</button>
                    <button type="button" @click="isReportDialogOpen = false" class="w-full py-4 text-luxury-slate font-black uppercase tracking-[0.2em] text-[10px] hover:text-luxury-forest transition-colors">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
