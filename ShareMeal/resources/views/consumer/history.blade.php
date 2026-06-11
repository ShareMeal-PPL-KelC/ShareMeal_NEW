@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="{
    isReviewDialogOpen: false,
    selectedOrderId: null,
    editingReviewId: null,
    isEditMode: false,
    rating: 0,
    review: '',
    isReviewSubmitting: false,
    isReviewSuccess: false,
    init() {
        this.$watch('isReviewDialogOpen', val => document.body.style.overflow = val ? 'hidden' : '');
    },
    openReviewModal(id) {
        this.isEditMode = false;
        this.selectedOrderId = id;
        this.editingReviewId = null;
        this.rating = 0;
        this.review = '';
        this.isReviewSubmitting = false;
        this.isReviewSuccess = false;
        this.isReviewDialogOpen = true;
    },
    openEditReviewModal(reviewId, currentRating, currentComment) {
        this.isEditMode = true;
        this.editingReviewId = reviewId;
        this.rating = currentRating;
        this.review = currentComment;
        this.isReviewSubmitting = false;
        this.isReviewSuccess = false;
        this.isReviewDialogOpen = true;
    },
    async submitReview(e) {
        e.preventDefault();
        if (this.rating === 0) {
            alert('Pilih rating terlebih dahulu');
            return false;
        }
        
        const form = e.target;
        this.isReviewSubmitting = true;
        this.isReviewSuccess = false;
        
        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            await new Promise(resolve => setTimeout(resolve, 1500));

            if (response.ok) {
                this.isReviewSubmitting = false;
                this.isReviewSuccess = true;
                
                setTimeout(() => {
                    this.isReviewDialogOpen = false;
                    window.location.reload();
                }, 2000);
            } else {
                let errorMessage = 'Gagal mengirim ulasan';
                try {
                    const errorData = await response.json();
                    if (errorData.message) {
                        errorMessage = errorData.message;
                    }
                } catch (err) {}
                alert(errorMessage);
                this.isReviewSubmitting = false;
            }
        } catch (error) {
            this.isReviewSubmitting = false;
            alert(error.message || 'Terjadi kesalahan sistem');
        }
    },
    isReceiptDialogOpen: false,
    receiptData: null,
    isPrinting: false,
    printProgress: 0,
    showPrintSuccess: false,
    openReceiptModal(data) {
        this.receiptData = data;
        this.isReceiptDialogOpen = true;
        this.isPrinting = false;
        this.printProgress = 0;
        this.showPrintSuccess = false;
    },
    downloadReceipt() {
        this.isPrinting = true;
        this.printProgress = 0;
        this.showPrintSuccess = false;
        
        let timer = setInterval(() => {
            if (this.printProgress < 100) {
                this.printProgress += 10;
            } else {
                clearInterval(timer);
                this.isPrinting = false;
                this.showPrintSuccess = true;
            }
        }, 150);
    },
    isReportDialogOpen: false,
    selectedOrderForReport: null,
    issueType: '',
    description: '',
    evidenceImageName: '',
    evidenceImagePreview: '',
    isReporting: false,
    isReportSuccess: false,
    isWarningDialogOpen: false,
    warningTitle: '',
    warningMessage: '',
    openReportModal(order) {
        this.selectedOrderForReport = order;
        this.issueType = 'bad_quality';
        this.description = '';
        this.evidenceImageName = '';
        this.evidenceImagePreview = '';
        this.isReporting = false;
        this.isReportSuccess = false;
        this.isReportDialogOpen = true;
    },
    async submitReport(e) {
        e.preventDefault();
        const form = e.target;
        this.isReporting = true;
        
        // Client-side image size check (max 2MB / 2048KB)
        const fileInput = form.querySelector('input[name=evidence_image]');
        if (fileInput && fileInput.files && fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const maxSize = 2 * 1024 * 1024; // 2MB
            if (file.size > maxSize) {
                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                this.warningTitle = 'Gambar Terlalu Besar';
                this.warningMessage = 'Ukuran file gambar yang Anda pilih terlalu besar (' + fileSizeMB + ' MB). Maksimal ukuran gambar yang diizinkan adalah 2.00 MB. Silakan kompres gambar atau pilih gambar lain yang lebih kecil.';
                this.isWarningDialogOpen = true;
                this.isReporting = false;
                return;
            }
        }
        
        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            await new Promise(resolve => setTimeout(resolve, 1500));

            if (response.ok) {
                this.isReporting = false;
                this.isReportSuccess = true;
                
                setTimeout(() => {
                    this.isReportDialogOpen = false;
                    window.location.reload();
                }, 2000);
            } else {
                let errorMessage = 'Gagal mengirim laporan';
                try {
                    const errorData = await response.json();
                    if (errorData.errors && errorData.errors.evidence_image) {
                        errorMessage = errorData.errors.evidence_image.join(', ');
                    } else if (errorData.message) {
                        errorMessage = errorData.message;
                    }
                } catch (e) {
                    if (response.status === 413) {
                        errorMessage = 'Ukuran file gambar melebihi batas upload server (maksimal 2MB).';
                    }
                }
                throw new Error(errorMessage);
            }
        } catch (error) {
            this.isReporting = false;
            this.warningTitle = 'Gagal Mengirim Laporan';
            this.warningMessage = error.message;
            this.isWarningDialogOpen = true;
        }
    }
}">
    <div class="mb-12 reveal">
        <h1 class="text-5xl font-serif font-bold text-luxury-forest leading-tight">Riwayat Pesanan</h1>
        <p class="text-luxury-slate font-medium mt-2 tracking-wide">Pantau kontribusi Anda dalam menyelamatkan surplus pangan berkualitas.</p>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
        <div class="glass-card glass-card-hover p-8 rounded-[2.5rem] group transition-all duration-500 reveal delay-100">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-emerald-100 transition-all duration-300 mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7 text-emerald-600">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" x2="21" y1="6" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                </div>
                <div class="text-3xl md:text-4xl font-serif font-black text-luxury-forest group-hover:text-emerald-600 transition-colors leading-none">{{ count($transactions) }}</div>
                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-[0.2em] text-center mt-4 group-hover:text-luxury-forest transition-colors">Total Pesanan</div>
            </div>
        </div>
        <div class="glass-card glass-card-hover p-8 rounded-[2.5rem] group transition-all duration-500 reveal delay-200">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 bg-teal-50 text-teal-650 border border-teal-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-teal-100 transition-all duration-300 mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7 text-teal-650">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                </div>
                <div class="text-3xl md:text-4xl font-serif font-black text-luxury-forest group-hover:text-emerald-600 transition-colors leading-none">Rp {{ number_format(collect($transactions)->sum('savedAmount') / 1000, 0) }}k</div>
                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-[0.2em] text-center mt-4 group-hover:text-luxury-forest transition-colors">Total Penghematan</div>
            </div>
        </div>
        <div class="glass-card glass-card-hover p-8 rounded-[2.5rem] group transition-all duration-500 reveal delay-300">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 bg-amber-50 text-amber-600 border border-amber-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-amber-100 transition-all duration-300 mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7 text-amber-600">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                </div>
                <div class="text-3xl md:text-4xl font-serif font-black text-luxury-forest group-hover:text-emerald-600 transition-colors leading-none">
                    @php
                        $rated = collect($transactions)->filter(fn($t) => $t->rating > 0);
                        $avg = $rated->count() > 0 ? $rated->avg('rating') : 0;
                    @endphp
                    {{ number_format($avg, 1) }}
                </div>
                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-[0.2em] text-center mt-4 group-hover:text-luxury-forest transition-colors">Rata-rata Rating</div>
            </div>
        </div>
    </div>

    <!-- Transaction List -->
    <div class="space-y-12">
        @forelse($transactions as $t)
        <div class="glass-card glass-card-hover rounded-[3.5rem] overflow-hidden reveal delay-{{ $loop->iteration * 100 }}">
            <div class="p-10 lg:p-12">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-10">
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-6 mb-6">
                            <h3 class="text-3xl font-serif font-bold text-luxury-forest leading-none">{{ $t->store }}</h3>
                            @if($t->status === 'completed')
                            <span class="bg-luxury-emerald/10 text-luxury-emerald px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-luxury-emerald/20">
                                Selesai
                            </span>
                            @elseif($t->status === 'processing')
                            <span class="bg-amber-50 text-amber-700 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-amber-200">
                                Sedang Dibuat
                            </span>
                            @elseif($t->status === 'ready')
                            <span class="bg-blue-50 text-blue-700 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-blue-200 animate-pulse">
                                {{ $t->receiving_method === 'delivery' ? 'Siap Dikirim' : 'Siap Diambil' }}
                            </span>
                            @elseif($t->status === 'shipping')
                            <span class="bg-indigo-50 text-indigo-700 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-indigo-200">
                                Dalam Perjalanan
                            </span>
                            @elseif($t->status === 'cancelled')
                            <span class="bg-red-50 text-red-700 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-red-200">
                                Dibatalkan
                            </span>
                            @else
                            <span class="bg-orange-50 text-orange-700 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-orange-100">
                                Menunggu Konfirmasi
                            </span>
                            @endif

                            @if($t->receiving_method === 'delivery')
                            <span class="bg-indigo-50/70 text-indigo-700 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-indigo-200/50 flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><rect width="16" height="12" x="2" y="4" rx="2"/><path d="M22 8h-4v8h4V8Z"/><path d="M6 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/><path d="M18 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/></svg>
                                Delivery
                            </span>
                            @else
                            <span class="bg-emerald-50/70 text-emerald-800 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-200/50 flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="m2 22 1-1h18l1 1"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M4 22V4a2 2 0 0 1 2-2h10l4 4v16"/></svg>
                                Pickup
                            </span>
                            @endif
                        </div>
                        
                        <div class="space-y-3 mb-8">
                            <div class="flex items-center gap-4 text-xs font-bold text-luxury-slate uppercase tracking-widest">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-luxury-gold">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
                                </svg>
                                {{ $t->orderTime }}
                                <span class="text-luxury-alabas">•</span>
                                <span class="font-mono text-luxury-forest">#{{ $t->orderId }}</span>
                            </div>
                            <div class="flex items-center gap-4 text-xs font-medium text-luxury-slate italic">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-luxury-gold">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                                </svg>
                                {{ $t->storeAddress }}
                            </div>
                            <div class="flex items-center gap-4 text-xs font-black text-luxury-emerald uppercase tracking-[0.1em]">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                </svg>
                                @if($t->receiving_method === 'delivery')
                                    Perkiraan Tiba: {{ $t->delivery_time_slot }}
                                @else
                                    Jadwal Pengambilan: {{ $t->pickupTime }}
                                @endif
                            </div>
                        </div>

                        @if(($t->status === 'pending' || $t->status === 'ready' || $t->status === 'shipping') && $t->receiving_method === 'pickup')
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
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 animate-pulse text-red-500">
                                <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-[10px] font-black uppercase tracking-widest leading-none mb-1">Batas Waktu Pengambilan</p>
                                <span x-show="!isExpired" class="text-sm font-bold">Berakhir dalam: <span x-text="timeRemaining" class="font-mono bg-white px-2 py-0.5 rounded-lg border border-red-105 ml-2"></span></span>
                                <span x-show="isExpired" class="text-sm font-bold uppercase tracking-widest" x-cloak>Batas Waktu Habis</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="text-right">
                        <div class="text-[10px] font-black uppercase tracking-[0.3em] text-luxury-gold mb-2">Total Pembayaran</div>
                        <div class="text-4xl font-serif font-black text-luxury-forest leading-none">Rp {{ number_format($t->total, 0, ',', '.') }}</div>
                        <div class="mt-4 flex flex-col items-end gap-2">
                            <div class="text-[11px] text-luxury-slate line-through font-bold tracking-widest opacity-50 uppercase">Harga Awal Rp {{ number_format($t->subtotal, 0, ',', '.') }}</div>
                            <div class="text-[10px] text-white font-black uppercase tracking-[0.2em] bg-luxury-emerald px-4 py-1.5 rounded-full shadow-sm">
                                Hemat Rp {{ number_format($t->savedAmount, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cancellation Reason Banner -->
                @if($t->status === 'cancelled')
                <div class="bg-red-50/60 border border-red-150 rounded-[2rem] p-8 flex items-start gap-4 mt-10">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-red-500 shrink-0 mt-0.5">
                        <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
                    </svg>
                    <div>
                        <div class="text-[10px] font-black text-red-600 uppercase tracking-[0.3em] mb-1">Alasan Pembatalan</div>
                        <div class="text-sm font-bold text-luxury-forest">
                            {{ $t->cancel_reason ?: 'Dibatalkan oleh mitra toko.' }}
                        </div>
                    </div>
                </div>
                @endif

                <!-- Pickup Code -->
                @if(($t->status === 'pending' || $t->status === 'ready') && $t->receiving_method === 'pickup')
                <div class="bg-white/40 border border-luxury-alabas/80 rounded-[2rem] p-8 flex flex-col sm:flex-row items-center justify-between gap-8 mt-10">
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-luxury-gold luxury-shadow mx-auto sm:mx-0">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 stroke-[1.5]">
                                <rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16V3m0 18v-5M16 21h5M8 21v-3m0 3H3m5-13h3m2 0h1m2 0h2m-6 3h2m1 0h1m-1 2V8"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em] mb-1 text-center sm:text-left">Kode Otorisasi</div>
                            <div class="font-serif text-lg font-bold text-luxury-forest text-center sm:text-left">Tunjukkan kode ini kepada petugas toko saat mengambil makanan</div>
                        </div>
                    </div>
                    <div class="font-mono text-4xl font-black text-luxury-forest tracking-tighter bg-white px-8 py-4 rounded-2xl border-2 border-luxury-forest shadow-xl">
                        {{ $t->pickupCode }}
                    </div>
                </div>
                @endif

                <!-- Review Section -->
                @if($t->reviewRelation)
                @php
                    $canModifyReview = $t->reviewRelation->created_at && !$t->reviewRelation->created_at->addMinutes(2)->isPast();
                @endphp
                <div class="mt-10 pt-10 border-t border-luxury-alabas/50 relative">
                    <div class="flex flex-col md:flex-row items-start gap-8">
                        <div class="flex gap-1.5 bg-luxury-gold/5 p-3 rounded-2xl border border-luxury-gold/10 flex-shrink-0">
                            @for($i = 1; $i <= 5; $i++)
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 {{ $i <= $t->rating ? 'text-luxury-gold fill-luxury-gold' : 'text-luxury-alabas fill-transparent stroke-current' }}" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                            @endfor
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-[10px] font-black uppercase tracking-[0.3em] text-luxury-gold">Penilaian & Ulasan Anda</div>
                                @if($canModifyReview)
                                    <div class="flex items-center gap-6">
                                        <button @click="openEditReviewModal(@js($t->reviewRelation->id), @js($t->rating), @js($t->review))" class="text-luxury-forest hover:text-luxury-gold text-[10px] font-black uppercase tracking-[0.2em] flex items-center gap-2 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                                                <path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                            </svg>
                                            Ubah
                                        </button>
                                        <form action="{{ route('consumer.review.delete', $t->reviewRelation->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-450 hover:text-red-650 text-[10px] font-black uppercase tracking-[0.2em] flex items-center gap-2 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                                                    <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                                </svg>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="text-[9px] font-black uppercase tracking-widest text-luxury-slate/60 bg-luxury-alabas/30 px-3 py-1 rounded-lg border border-luxury-alabas/50 flex items-center gap-1.5 select-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                        </svg>
                                        Terkunci
                                    </div>
                                @endif
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
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 transition-transform group-hover:rotate-12">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        Berikan Penilaian & Ulasan
                    </button>
                </div>
                @endif

                <div class="flex flex-wrap gap-4 mt-10 pt-10 border-t border-luxury-alabas/50">
                    <button @click="openReceiptModal({
                        id: '{{ addslashes($t->orderId) }}',
                        store: '{{ addslashes($t->store) }}',
                        date: '{{ addslashes($t->orderTime) }}',
                        pickupTime: '{{ addslashes($t->receiving_method === 'delivery' ? $t->delivery_time_slot : $t->pickupTime) }}',
                        status: '{{ addslashes($t->status) }}',
                        subtotal: {{ $t->subtotal }},
                        savedAmount: {{ $t->savedAmount }},
                        total: {{ $t->total }},
                        receivingMethod: '{{ addslashes($t->receiving_method) }}',
                        paymentMethod: '{{ addslashes($t->payment_method) }}',
                        customerName: '{{ addslashes(Auth::user()->name) }}',
                        customerAddress: '{{ addslashes(Auth::user()->profile?->address ?? Auth::user()->address ?? "Jl. Telekomunikasi No. 1, Bandung") }}',
                        storeAddress: '{{ addslashes($t->storeAddress) }}',
                        pickupCode: '{{ addslashes($t->pickupCode) }}',
                        items: [
                            @foreach($t->items as $item)
                            { name: '{{ addslashes($item->product ? $item->product->name : $item->name) }}', qty: {{ $item->quantity }}, price: {{ $item->price }} },
                            @endforeach
                        ]
                    })" class="flex-1 min-w-[160px] bg-white/60 border border-luxury-alabas text-luxury-forest px-8 py-4 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] hover:bg-white hover:text-luxury-gold hover:border-luxury-gold/30 transition-all duration-500 flex items-center justify-center gap-3 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-luxury-gold">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/>
                        </svg>
                        Struk Digital
                    </button>
                    @if($t->receiving_method === 'delivery' && $t->status === 'shipping')
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($t->storeAddress . ' ' . $t->store) }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="flex-1 min-w-[160px] bg-blue-50/80 border border-blue-200/60 text-blue-700 px-8 py-4 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] hover:bg-blue-100 hover:text-blue-800 transition-all duration-500 flex items-center justify-center gap-3 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                            <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" x2="9" y1="3" y2="18"/><line x1="15" x2="15" y1="6" y2="21"/>
                        </svg>
                        Buka Navigasi
                    </a>
                    @endif
                    <button @click="openReportModal({
                        id: {{ $t->id }},
                        store: '{{ addslashes($t->store) }}'
                    })" class="flex-1 min-w-[160px] bg-red-50/50 text-red-650 border border-red-100/50 px-8 py-4 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] hover:bg-red-100 transition-all duration-500 flex items-center justify-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" x2="4" y1="22" y2="15"/>
                        </svg>
                        Laporkan Masalah
                    </button>
                    <a href="{{ route('consumer.search') }}" class="flex-[2] min-w-[200px] bg-luxury-forest text-white px-8 py-4 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] hover:bg-luxury-gold transition-all duration-500 flex items-center justify-center gap-3 luxury-shadow group">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-luxury-gold transition-transform group-hover:scale-125">
                            <line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/>
                        </svg>
                        Pesan Lagi
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="glass-card p-16 rounded-[3rem] text-center reveal">
            <div class="w-24 h-24 bg-white/80 rounded-full flex items-center justify-center mx-auto mb-8 luxury-shadow border border-luxury-alabas/50">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 text-luxury-gold">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
                </svg>
            </div>
            <h3 class="text-3xl font-serif font-bold text-luxury-forest mb-4">Belum Ada Riwayat Pesanan</h3>
            <p class="text-luxury-slate font-medium max-w-md mx-auto mb-10 leading-relaxed">Anda belum melakukan pemesanan surplus pangan berkualitas. Mari mulai berkontribusi menyelamatkan surplus pangan!</p>
            <a href="{{ route('consumer.search') }}" class="inline-flex items-center gap-3 bg-luxury-forest text-white px-10 py-5 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] hover:bg-luxury-gold transition-all duration-500 luxury-shadow group">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-luxury-gold transition-transform group-hover:scale-125">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                Jelajahi Makanan
            </a>
        </div>
        @endforelse
    </div>

    <!-- Review Modal Overlay -->
    <div x-show="isReviewDialogOpen" 
         class="fixed inset-0 z-[100] flex items-start justify-center p-4 pt-24 sm:p-6 sm:pt-32 overflow-y-auto" 
         x-cloak>
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-luxury-forest/60 backdrop-blur-md" @click="if (!isReviewSubmitting && !isReviewSuccess) isReviewDialogOpen = false"></div>

        <!-- Modal Content -->
        <div x-show="isReviewDialogOpen"
             x-transition:enter="ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-y-12 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="relative bg-white/95 backdrop-blur-lg w-full max-w-md rounded-[2.5rem] shadow-2xl border border-white/60 p-8 sm:p-10 mx-auto overflow-hidden">
            
            <!-- Loading Overlay -->
            <div x-show="isReviewSubmitting" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-white/95 backdrop-blur-md z-50 flex flex-col items-center justify-center p-8 text-center rounded-[2.5rem]"
                 x-cloak>
                
                <!-- Elegant Circular Spinner -->
                <div class="relative w-20 h-20 mb-6">
                    <div class="absolute inset-0 border-4 border-luxury-forest/10 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-luxury-forest border-t-transparent rounded-full animate-spin"></div>
                </div>

                <h3 class="text-2xl font-serif font-bold text-luxury-forest mb-2">Mengirim Ulasan</h3>
                <p class="text-luxury-slate/75 text-xs max-w-xs leading-relaxed">
                    Sedang memproses ulasan dan rating Anda secara aman...
                </p>
            </div>

            <!-- Success Overlay -->
            <div x-show="isReviewSuccess" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute inset-0 bg-white/95 backdrop-blur-md z-50 flex flex-col items-center justify-center p-8 text-center rounded-[2.5rem]"
                 x-cloak>
                
                <!-- Animated Success Icon -->
                <div class="w-20 h-20 bg-luxury-emerald/10 rounded-full flex items-center justify-center mb-6 border border-luxury-emerald/20 animate-bounce">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 text-luxury-emerald">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>

                <h3 class="text-2xl font-serif font-bold text-luxury-forest mb-2">Ulasan Terkirim!</h3>
                <p class="text-luxury-slate/85 text-xs max-w-xs leading-relaxed">
                    Terima kasih atas ulasan dan apresiasi yang Anda berikan.
                </p>
            </div>

            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-amber-100 to-amber-200 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-inner rotate-3 hover:rotate-12 transition-transform duration-500">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-amber-600">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                </div>
                <h3 class="text-3xl font-serif font-bold text-luxury-forest leading-tight">Bagikan Pengalaman</h3>
                <p class="text-[10px] text-luxury-gold font-black uppercase tracking-[0.2em] mt-2">Apresiasi Anda sangat berarti bagi kami</p>
            </div>

            <form :action="isEditMode ? '{{ url('/consumer/review') }}/' + editingReviewId : '{{ route('consumer.review.submit') }}'" method="POST" class="space-y-6" @submit="submitReview($event)">
                @csrf
                <template x-if="isEditMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="order_id" :value="selectedOrderId">
                <input type="hidden" name="rating" :value="rating">

                <div class="text-center">
                    <div class="flex justify-center gap-3 bg-amber-50/50 py-4 px-6 rounded-2xl border border-amber-100/40">
                        <template x-for="i in 5">
                            <button type="button" @click="rating = i" class="transition-all duration-300 transform hover:scale-125 active:scale-95 focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-10 h-10 transition-all duration-300" :class="i <= rating ? 'text-amber-500 fill-amber-400 filter drop-shadow-sm' : 'text-gray-300 fill-transparent stroke-current'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block">Komentar atau Ulasan Anda</label>
                    <textarea name="comment" x-model="review" rows="3" 
                              class="w-full bg-gray-50/50 border border-gray-200/50 rounded-2xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition font-semibold text-gray-800 placeholder:text-gray-400 text-sm" 
                              placeholder="Ceritakan tentang rasa makanan dan pengalaman Anda..."></textarea>
                </div>

                <div class="pt-2 flex flex-col gap-3">
                    <button type="submit" :disabled="rating === 0 || isReviewSubmitting" 
                            class="w-full bg-[#174413] hover:bg-[#0f2d0c] text-white py-4 rounded-xl font-bold uppercase tracking-wider text-xs shadow-lg shadow-green-900/20 transition disabled:opacity-40 disabled:cursor-not-allowed" 
                            x-text="isReviewSubmitting ? 'Mengirim...' : (isEditMode ? 'Simpan Perubahan' : 'Kirim Apresiasi')"></button>
                    <button type="button" @click="isReviewDialogOpen = false" :disabled="isReviewSubmitting"
                            class="w-full py-2.5 text-gray-500 hover:text-gray-800 font-bold uppercase tracking-wider text-[11px] transition disabled:opacity-40">Batal</button>
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
             x-transition:enter-start="opacity-0 translate-y-24 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="relative glass-panel w-full max-w-2xl rounded-[2.5rem] shadow-2xl border border-white/40 max-h-[85vh] overflow-y-auto scrollbar-thin"
             :class="{ 'overflow-y-hidden': isPrinting || showPrintSuccess }">
            
            <!-- Top Elegant Accent -->
            <div class="h-2 w-full bg-gradient-to-r from-luxury-forest via-luxury-gold to-luxury-emerald"></div>
            
            <!-- Loading Overlay -->
            <div x-show="isPrinting" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-luxury-forest/95 backdrop-blur-md z-50 flex flex-col items-center justify-center p-6 text-center"
                 x-cloak>
                
                <!-- Circular Spinner with progress -->
                <div class="relative w-24 h-24 mb-6">
                    <!-- Outer Ring -->
                    <svg class="w-full h-full transform -rotate-90">
                        <circle cx="48" cy="48" r="40" stroke="currentColor" stroke-width="6" class="text-white/10" fill="transparent" />
                        <circle cx="48" cy="48" r="40" stroke="currentColor" stroke-width="6" class="text-luxury-gold transition-all duration-150" 
                                fill="transparent" 
                                :stroke-dasharray="2 * Math.PI * 40" 
                                :stroke-dashoffset="2 * Math.PI * 40 * (1 - printProgress / 100)" />
                    </svg>
                    <!-- Progress Text -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-white font-mono text-lg font-black" x-text="printProgress + '%'"></span>
                    </div>
                </div>

                <h3 class="text-xl font-serif font-bold text-white mb-2">Menyiapkan Struk</h3>
                <p class="text-luxury-alabas/70 text-xs tracking-wide max-w-xs leading-relaxed">
                    Sedang memproses dokumen dan mengunduh struk digital Anda secara aman...
                </p>
            </div>

            <!-- Success Overlay -->
            <div x-show="showPrintSuccess" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute inset-0 bg-white/95 backdrop-blur-md z-50 flex flex-col items-center justify-center p-6 text-center"
                 x-cloak>
                
                <!-- Animated Success Icon -->
                <div class="w-20 h-20 bg-luxury-emerald/10 rounded-full flex items-center justify-center mb-6 border border-luxury-emerald/20">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 text-luxury-emerald animate-bounce">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>

                <h3 class="text-2xl font-serif font-bold text-luxury-forest mb-2">Unduhan Berhasil!</h3>
                <p class="text-luxury-slate text-xs font-semibold tracking-wide mb-1" x-text="receiptData ? 'ID: ' + receiptData.id : ''"></p>
                <p class="text-luxury-slate/85 text-xs max-w-xs leading-relaxed mb-6">
                    Struk digital telah berhasil diunduh dan disimpan di perangkat Anda.
                </p>
                
                <button @click="showPrintSuccess = false; isReceiptDialogOpen = false" 
                        class="bg-luxury-forest text-white py-3 px-8 rounded-xl font-black uppercase tracking-[0.2em] text-[10px] shadow-lg hover:bg-luxury-gold transition-all duration-300">
                    Selesai
                </button>
            </div>
            
            <div class="p-6 sm:p-8 lg:p-10">
                <!-- Header: Icon, Title, and Store Name -->
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-luxury-alabas/50 pb-4 text-left gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center luxury-shadow border border-luxury-alabas flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-luxury-forest">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-serif font-bold text-luxury-forest">Struk Digital</h2>
                            <p class="text-luxury-slate text-xs font-semibold tracking-wide" x-text="receiptData ? receiptData.store : ''"></p>
                        </div>
                    </div>
                    
                    <!-- Invoice Info at Top Right -->
                    <div class="text-left sm:text-right">
                        <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-0.5">ID Transaksi</span>
                        <span class="font-mono text-xs font-bold text-luxury-forest tracking-tighter" x-text="receiptData ? receiptData.id : ''"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left" x-show="receiptData">
                    
                    <!-- Left Column: Transaction Details, Method, Address -->
                    <div class="space-y-4">
                        <div class="bg-white/40 rounded-2xl border border-luxury-alabas p-5 relative overflow-hidden h-full flex flex-col justify-between">
                            <!-- Background Decoration -->
                            <div class="absolute top-0 right-0 p-3 opacity-[0.03] pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-20 h-20 text-luxury-forest -rotate-12">
                                    <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 3.5 2 5.5a7 7 0 0 1-10 12.5ZM19 2v4"/>
                                </svg>
                            </div>

                            <div class="space-y-4">
                                <div class="flex justify-between items-center pb-3 border-b border-luxury-alabas/50">
                                    <div>
                                        <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-0.5">Status Pembayaran</span>
                                        <span class="text-[9px] font-black text-luxury-emerald uppercase tracking-widest bg-luxury-emerald/10 px-2 py-0.5 rounded">Lunas</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-0.5">Pembayaran via</span>
                                        <span class="text-xs font-bold text-luxury-forest uppercase" x-text="receiptData && receiptData.paymentMethod ? receiptData.paymentMethod.toUpperCase() : 'QRIS'"></span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-1">Metode Pengambilan</span>
                                        <span class="text-xs font-bold text-luxury-forest uppercase tracking-wider" x-text="receiptData && receiptData.receivingMethod === 'delivery' ? 'Kirim ke Lokasi' : 'Ambil Sendiri'"></span>
                                    </div>
                                    <div>
                                        <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-1" x-text="receiptData && receiptData.receivingMethod === 'delivery' ? 'Perkiraan Tiba' : 'Jadwal Pengambilan'"></span>
                                        <span class="text-xs font-black text-luxury-forest uppercase tracking-wider" x-text="receiptData ? receiptData.pickupTime : ''"></span>
                                    </div>
                                </div>

                                <div class="p-3.5 bg-white rounded-xl border border-luxury-alabas/50 shadow-sm">
                                    <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-1.5" x-text="receiptData && receiptData.receivingMethod === 'delivery' ? 'Alamat Pengiriman' : 'Lokasi Toko'"></span>
                                    <div class="text-xs font-bold text-luxury-forest mb-0.5" x-text="receiptData && receiptData.receivingMethod === 'delivery' ? receiptData.customerName : receiptData.store"></div>
                                    <div class="text-[11px] text-luxury-slate leading-relaxed font-medium italic opacity-85" x-text="receiptData && receiptData.receivingMethod === 'delivery' ? receiptData.customerAddress : receiptData.storeAddress"></div>
                                </div>

                                <div class="flex justify-between items-center pt-2" x-show="receiptData && receiptData.receivingMethod === 'pickup'">
                                    <div>
                                        <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-0.5">Petunjuk</span>
                                        <span class="text-[11px] font-medium text-luxury-slate">Tunjukkan kode ini ke kasir</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-0.5">Kode Klaim</span>
                                        <div class="font-mono text-xl font-black text-luxury-forest tracking-tighter bg-luxury-gold/10 px-3 py-1 rounded-lg border border-luxury-gold/25 inline-block" x-text="receiptData ? receiptData.pickupCode : ''"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Purchased Items, Billing Details, Totals, Actions -->
                    <div class="space-y-4">
                        <div class="bg-white/40 rounded-2xl border border-luxury-alabas p-5 flex flex-col justify-between h-full">
                            <div>
                                <!-- Items Detail Section -->
                                <div class="mb-4">
                                    <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-2 border-b border-luxury-alabas/50 pb-1">Rincian Pembelian</span>
                                    <div class="space-y-2 max-h-[140px] overflow-y-auto pr-1 scrollbar-thin">
                                        <template x-for="(item, index) in (receiptData ? receiptData.items : [])" :key="index">
                                            <div class="flex justify-between items-start gap-4 text-xs">
                                                <div class="flex-1">
                                                    <span class="font-bold text-luxury-forest" x-text="item.name"></span>
                                                    <span class="text-[9px] text-luxury-slate font-black uppercase tracking-widest ml-2" x-text="'x' + item.qty"></span>
                                                </div>
                                                <span class="font-bold text-luxury-forest" x-text="'Rp ' + (item.price * item.qty).toLocaleString('id-ID')"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Price Calculation Section -->
                                <div class="mt-4 pt-3 border-t border-luxury-alabas/50 space-y-1.5">
                                    <div class="flex justify-between text-[11px] font-medium text-luxury-slate">
                                        <span>Harga Awal</span>
                                        <span x-text="'Rp ' + receiptData.subtotal.toLocaleString('id-ID')"></span>
                                    </div>
                                    <div class="flex justify-between text-[11px] font-bold text-luxury-emerald uppercase tracking-widest">
                                        <span>Total Hemat</span>
                                        <span x-text="'- Rp ' + receiptData.savedAmount.toLocaleString('id-ID')"></span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <!-- Total Row -->
                                <div class="mt-4 pt-4 border-t-2 border-dashed border-luxury-alabas/50">
                                    <div class="flex justify-between items-center">
                                        <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em]">Total Pembayaran</span>
                                        <div class="text-xl font-serif font-black text-luxury-forest" x-text="'Rp ' + receiptData.total.toLocaleString('id-ID')"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-end mt-6 pt-4 border-t border-luxury-alabas/30">
                    <button @click="isReceiptDialogOpen = false" class="py-3 px-6 text-[10px] font-black uppercase tracking-[0.2em] text-luxury-slate hover:text-luxury-forest transition-colors sm:w-auto w-full">Tutup</button>
                    <button @click="downloadReceipt()" class="flex items-center justify-center gap-2.5 bg-luxury-forest text-white py-3 px-8 rounded-[1.25rem] font-black uppercase tracking-[0.2em] text-[10px] shadow-lg hover:bg-luxury-gold transition-all duration-500 sm:w-auto w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-luxury-gold">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>
                        </svg>
                        Cetak Struk
                    </button>
                </div>
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
             class="relative glass-panel w-full max-w-2xl rounded-[3.5rem] overflow-hidden shadow-2xl border border-white/40 p-12">
            
            <style>
                @keyframes stroke {
                    100% {
                        stroke-dashoffset: 0;
                    }
                }
            </style>

            <!-- Loading State Overlay -->
            <div x-show="isReporting" 
                 class="absolute inset-0 bg-white/90 backdrop-blur-md z-[110] flex flex-col items-center justify-center p-12 text-center"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-cloak>
                <div class="w-20 h-20 relative flex items-center justify-center">
                    <div class="absolute inset-0 border-4 border-red-200 border-t-red-650 rounded-full animate-spin"></div>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 text-red-650 animate-pulse">
                        <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
                    </svg>
                </div>
                <h4 class="text-xl font-serif font-black text-luxury-forest mt-8 mb-2">Mengirim Laporan Anda</h4>
                <p class="text-[10px] text-luxury-slate font-black uppercase tracking-[0.2em] animate-pulse">Mohon tunggu sebentar...</p>
            </div>

            <!-- Success State Overlay -->
            <div x-show="isReportSuccess" 
                 class="absolute inset-0 bg-white/95 backdrop-blur-md z-[110] flex flex-col items-center justify-center p-12 text-center"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-cloak>
                <div class="w-24 h-24 bg-emerald-50 rounded-full flex items-center justify-center mb-8 border border-emerald-100 luxury-shadow relative overflow-hidden group">
                    <svg class="w-12 h-12 text-luxury-emerald stroke-[3] scale-100" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" style="stroke: #10B981; stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 2; stroke-miterlimit: 10; animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;"/>
                        <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" style="stroke: #10B981; stroke-dasharray: 48; stroke-dashoffset: 48; stroke-width: 4; stroke-linecap: round; stroke-linejoin: round; animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.6s forwards;"/>
                    </svg>
                </div>
                <h4 class="text-3xl font-serif font-black text-luxury-forest mb-4">Laporan Terkirim!</h4>
                <p class="text-xs text-luxury-slate font-semibold max-w-xs leading-relaxed">Terima kasih. Laporan masalah telah diterima dan akan segera ditinjau oleh tim admin kami.</p>
            </div>

            <div class="text-center mb-10">
                <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-red-100">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 text-red-650 mx-auto">
                        <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
                    </svg>
                </div>
                <h3 class="text-3xl font-serif font-bold text-luxury-forest">Laporkan Masalah Pesanan</h3>
                <p class="text-xs text-luxury-slate font-medium uppercase tracking-[0.2em] mt-2">Bantu kami menjaga kualitas makanan terbaik</p>
            </div>

            <form action="{{ route('consumer.report.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-8" @submit="submitReport($event)">
                @csrf
                <input type="hidden" name="order_id" :value="selectedOrderForReport ? selectedOrderForReport.id : ''">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em]">Jenis Masalah</label>
                            <div class="relative">
                                <select name="issue_type" x-model="issueType" class="w-full bg-white/60 border border-luxury-alabas/85 rounded-[1.5rem] px-6 py-5 outline-none focus:ring-2 focus:ring-red-500 transition-all font-bold text-luxury-forest appearance-none">
                                    <option value="bad_quality">Kualitas Buruk / Basi</option>
                                    <option value="expired">Sudah Kedaluwarsa</option>
                                    <option value="mismatch">Tidak Sesuai Deskripsi</option>
                                    <option value="other">Lainnya</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-6 text-luxury-forest">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em]">Bukti Foto</label>
                            <div class="relative border-2 border-dashed rounded-[1.5rem] p-6 text-center transition-all duration-300 bg-white/40 group overflow-hidden"
                                 :class="evidenceImagePreview ? 'border-luxury-emerald bg-luxury-emerald/5' : 'border-luxury-alabas hover:border-red-500'">
                                
                                <input type="file" name="evidence_image" accept="image/*" 
                                       @change="
                                           const file = $event.target.files[0];
                                           if (file) {
                                               evidenceImageName = file.name;
                                               evidenceImagePreview = URL.createObjectURL(file);
                                           } else {
                                               evidenceImageName = '';
                                               evidenceImagePreview = '';
                                           }
                                       "
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">

                                <!-- Standard Placeholder -->
                                <div x-show="!evidenceImagePreview" class="space-y-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 text-luxury-gold mx-auto group-hover:scale-110 transition-transform">
                                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                    </svg>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-luxury-forest block">Unggah Bukti Foto</span>
                                    <span class="text-[9px] font-medium text-luxury-slate block">Format JPG, PNG, atau WEBP</span>
                                </div>

                                <!-- Image Preview Container -->
                                <div x-show="evidenceImagePreview" class="relative z-10 flex flex-col items-center gap-4" x-cloak>
                                    <div class="relative w-24 h-24 rounded-2xl overflow-hidden shadow-md border-2 border-white mx-auto">
                                        <img :src="evidenceImagePreview" class="w-full h-full object-cover">
                                    </div>
                                    <div class="text-center">
                                        <span class="text-[10px] font-bold text-luxury-emerald block truncate max-w-[200px]" x-text="evidenceImageName"></span>
                                        <span class="text-[9px] font-medium text-luxury-slate block">Siap dikirim</span>
                                    </div>
                                    <!-- Remove Button -->
                                    <button type="button" 
                                            @click.prevent.stop="
                                                evidenceImageName = '';
                                                evidenceImagePreview = '';
                                                $el.closest('.group').querySelector('input[type=file]').value = '';
                                            "
                                            class="absolute top-2 right-2 bg-red-650 text-white rounded-full p-2 hover:bg-red-700 transition-colors shadow-md z-30 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="w-3.5 h-3.5">
                                            <line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em]">Detail Kejadian</label>
                            <textarea name="description" x-model="description" rows="5" required 
                                      class="w-full bg-white/60 border border-luxury-alabas/85 rounded-[1.5rem] p-6 outline-none focus:ring-2 focus:ring-red-500 transition-all font-medium text-luxury-forest placeholder:text-luxury-slate/40 resize-none h-[155px]" 
                                      placeholder="Jelaskan detail masalah yang Anda alami secara rinci..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-luxury-alabas/50 flex flex-col sm:flex-row gap-6 justify-end">
                    <button type="button" @click="isReportDialogOpen = false" class="py-5 px-8 text-[10px] font-black uppercase tracking-[0.2em] text-luxury-slate hover:text-luxury-forest transition-colors text-center">Batal</button>
                    <button type="submit" class="bg-red-600 text-white py-5 px-10 rounded-[1.5rem] font-black uppercase tracking-[0.2em] text-[10px] shadow-xl hover:bg-red-750 transition-all duration-500 active:scale-95 text-center">Kirim Laporan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Warning Dialog Modal Overlay -->
    <div x-show="isWarningDialogOpen" 
         class="fixed inset-0 z-[120] flex items-center justify-center p-4 sm:p-6" 
         x-cloak>
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-red-950/50 backdrop-blur-md" @click="isWarningDialogOpen = false"></div>

        <!-- Modal Content -->
        <div x-show="isWarningDialogOpen"
             x-transition:enter="ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-y-12 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="relative bg-white/95 backdrop-blur-lg w-full max-w-md rounded-[2.5rem] overflow-hidden shadow-2xl border border-red-200/50 p-10 z-[130] text-center animate-in fade-in zoom-in duration-300">
            
            <!-- Icon Warning/Alert -->
            <div class="w-20 h-20 bg-red-50 text-red-600 border border-red-100 rounded-[1.75rem] flex items-center justify-center mb-6 mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 animate-bounce">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/>
                </svg>
            </div>

            <!-- Title -->
            <h3 class="text-2xl font-serif font-black text-luxury-forest mb-4" x-text="warningTitle"></h3>
            
            <!-- Message -->
            <p class="text-xs font-medium text-luxury-slate leading-relaxed mb-8 whitespace-pre-line text-center" x-text="warningMessage"></p>
            
            <!-- Action Button -->
            <button type="button" 
                    @click="isWarningDialogOpen = false" 
                    class="w-full py-4 bg-red-600 hover:bg-red-700 text-white font-black rounded-2xl shadow-lg transition-all duration-300 tracking-widest uppercase text-[10px] active:scale-95">
                Mengerti
            </button>
        </div>
    </div>
</div>
@endsection
