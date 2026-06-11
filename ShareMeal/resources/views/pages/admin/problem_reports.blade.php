@extends('layouts.dashboard')

@section('content')
<div class="space-y-8 relative" x-data="{
    isActionDialogOpen: false,
    actionType: '', // 'warn', 'block', 'dismiss'
    actionUrl: '',
    actionTitle: '',
    actionMessage: '',
    reason: '',
    currentStep: 1, // 1: confirm, 2: input reason, 3: loading, 4: success
    isProcessing: false,
    isImageDialogOpen: false,
    currentImageUrl: '',
    isImageLoading: true,
    
    openImage(url) {
        this.currentImageUrl = url;
        this.isImageLoading = true;
        this.isImageDialogOpen = true;
    },
    
    openConfirm(type, url, title, msg) {
        this.actionType = type;
        this.actionUrl = url;
        this.actionTitle = title;
        this.actionMessage = msg;
        this.reason = '';
        this.currentStep = 1;
        this.isProcessing = false;
        this.isActionDialogOpen = true;
    },
    
    nextStep() {
        if (this.actionType === 'dismiss') {
            this.submitAction();
        } else {
            this.currentStep = 2;
        }
    },
    
    async submitAction() {
        this.currentStep = 3;
        
        try {
            const response = await fetch(this.actionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    reason: this.reason
                })
            });
            
            await new Promise(resolve => setTimeout(resolve, 1200));
            
            if (response.ok) {
                this.currentStep = 4;
                setTimeout(() => {
                    this.isActionDialogOpen = false;
                    window.location.reload();
                }, 1800);
            } else {
                throw new Error('Gagal memproses tindakan');
            }
        } catch (error) {
            this.currentStep = 1;
            window.dispatchEvent(new CustomEvent('notify', { detail: { title: 'Gagal', message: error.message, type: 'error' } }));
        }
    }
}">
    <!-- Decorative Glow Top Right -->
    <div class="absolute top-0 right-0 w-[30rem] h-[30rem] bg-emerald-100/10 rounded-full blur-[120px] pointer-events-none z-0"></div>

    <!-- Title Header -->
    <div class="relative z-10 mb-12 reveal">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
            <div>
                <h1 class="text-5xl font-serif font-black text-luxury-forest leading-tight tracking-tight">Moderasi Laporan</h1>
                <p class="text-luxury-slate font-medium mt-3 text-lg leading-relaxed max-w-3xl">Tinjau dan ambil tindakan terhadap laporan kualitas makanan dari pengguna secara real-time demi kenyamanan dan keamanan platform.</p>
            </div>
            <div class="hidden md:flex items-center gap-3 shrink-0">
                <div class="glass-panel px-6 py-3.5 rounded-2xl border-luxury-alabas flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-luxury-gold animate-pulse"></div>
                    <span class="text-xs font-bold text-luxury-forest uppercase tracking-widest">Active Monitoring</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 relative z-10 reveal delay-100">
        <!-- Total Laporan -->
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group text-center">
            <div class="w-11 h-11 bg-slate-50 text-slate-650 border border-slate-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300 mx-auto">
                <i data-lucide="shield-alert" class="w-5 h-5"></i>
            </div>
            <div class="text-3xl font-serif font-black text-luxury-forest leading-none">{{ $reports->total() }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Total Laporan</div>
        </div>

        <!-- Pending (Review) -->
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group text-center">
            <div class="w-11 h-11 bg-orange-50 text-orange-600 border border-orange-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300 mx-auto">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
            <div class="text-3xl font-serif font-black text-luxury-forest leading-none">{{ $reports->where('status', 'pending')->count() }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Pending Review</div>
        </div>

        <!-- Resolved -->
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group text-center">
            <div class="w-11 h-11 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300 mx-auto">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div class="text-3xl font-serif font-black text-luxury-forest leading-none">{{ $reports->where('status', 'resolved')->count() }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Resolved</div>
        </div>

        <!-- High Priority -->
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group text-center">
            <div class="w-11 h-11 bg-red-50 text-red-600 border border-red-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300 mx-auto">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
            <div class="text-3xl font-serif font-black text-red-600 leading-none">
                {{ $reports->where('status', 'pending')->count() > 5 ? 'High' : 'Normal' }}
            </div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Priority Level</div>
        </div>
    </div>

    <!-- Reports Table / List -->
    <div class="glass-card rounded-[2.5rem] border-luxury-alabas overflow-hidden relative z-10 reveal delay-200">
        <!-- Table Title Header -->
        <div class="px-8 py-5 border-b border-luxury-alabas/60">
            <h2 class="text-xl font-serif font-black text-luxury-forest">Daftar Laporan Kualitas</h2>
            <p class="text-xs text-luxury-slate font-medium mt-0.5">{{ $reports->total() }} laporan masuk</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/30 border-b border-luxury-alabas/50">
                        <th class="px-8 py-4 text-[10px] font-black text-luxury-gold uppercase tracking-[0.2em]">Pelapor & Mitra</th>
                        <th class="px-8 py-4 text-[10px] font-black text-luxury-gold uppercase tracking-[0.2em]">Detail Masalah</th>
                        <th class="px-8 py-4 text-[10px] font-black text-luxury-gold uppercase tracking-[0.2em]">Status</th>
                        <th class="px-8 py-4 text-[10px] font-black text-luxury-gold uppercase tracking-[0.2em] text-right">Moderasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-alabas/40">
                    @forelse($reports as $report)
                    <tr class="hover:bg-emerald-50/30 transition-colors duration-200 group">
                        <!-- Pelapor & Mitra -->
                        <td class="px-8 py-6">
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#174413] to-emerald-600 text-white border-2 border-white shadow-md flex items-center justify-center font-serif font-black text-sm shrink-0">
                                        {{ substr($report->reporter->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-luxury-slate uppercase tracking-widest mb-0.5">Pelapor</p>
                                        <span class="text-sm font-bold text-luxury-forest">{{ $report->reporter->name }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-red-50/50 border border-red-200/50 text-red-650 flex items-center justify-center shadow-sm shrink-0">
                                        <i data-lucide="store" class="w-4 h-4 stroke-[2.5]"></i>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-red-400 uppercase tracking-widest mb-0.5">Terlapor (Mitra)</p>
                                        <span class="text-sm font-bold text-luxury-forest">{{ $report->mitra->displayName }}</span>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] text-luxury-slate font-semibold">Peringatan:</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-250/30">
                                                {{ $report->mitra->warnings_count ?? 0 }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Detail Masalah -->
                        <td class="px-8 py-6 max-w-md">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-50 text-red-750 border border-red-200/50 text-[10px] font-black uppercase tracking-wider mb-3 shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                                {{ $report->issue_label }}
                            </div>
                            <p class="text-sm text-luxury-slate leading-relaxed font-medium line-clamp-3 mb-4">{{ $report->description }}</p>
                            
                            <div class="flex flex-wrap items-center gap-4">
                                @if($report->evidence_image)
                                <button type="button" @click="openImage('{{ \Illuminate\Support\Facades\Storage::url($report->evidence_image) }}')" 
                                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 border border-luxury-alabas/85 text-luxury-forest hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-700 rounded-xl transition duration-300 text-[10px] font-black uppercase tracking-wider active:scale-95 cursor-pointer shadow-sm">
                                    <i data-lucide="image" class="w-3.5 h-3.5 text-luxury-gold"></i>
                                    Bukti Visual
                                </button>
                                @endif
                                <div class="text-[10px] text-luxury-slate/60 font-bold uppercase tracking-widest bg-white/40 border border-luxury-alabas/50 px-3 py-1.5 rounded-full">
                                    <span class="text-luxury-gold font-black">Via:</span> {{ $report->order_id ? 'Pesanan #' . $report->order_id : 'Donasi #' . $report->donation_id }}
                                    <span class="mx-1.5 text-luxury-slate/30">•</span>
                                    {{ $report->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-8 py-6">
                            <div class="flex flex-col gap-2">
                                @if($report->status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-250/30 w-fit shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Review
                                    </span>
                                @elseif($report->status === 'resolved')
                                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-250/30 w-fit shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-50 text-slate-600 border border-slate-250/30 w-fit shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Abaikan
                                    </span>
                                @endif

                                @if($report->admin_note)
                                    <div class="p-3.5 rounded-2xl bg-white/40 border border-luxury-alabas text-[10px] text-luxury-slate leading-relaxed shadow-sm italic max-w-[220px]">
                                        <span class="text-luxury-gold not-italic font-black uppercase block mb-1">Catatan Admin:</span>
                                        "{{ $report->admin_note }}"
                                    </div>
                                @endif
                            </div>
                        </td>

                        <!-- Moderasi -->
                        <td class="px-8 py-6 text-right">
                             @if($report->status === 'pending')
                             <div class="flex flex-col gap-2 w-36 ml-auto">
                                 <button type="button" @click="openConfirm('warn', '{{ route('admin.problem-reports.warn', $report->id) }}', 'Kirim Warning?', 'Mitra akan mendapatkan peringatan resmi terkait kualitas produk.')" 
                                         class="w-full bg-gradient-to-r from-amber-600 to-orange-500 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider hover:from-amber-700 hover:to-orange-600 transition-all duration-300 shadow-md active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                                     <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> Warning
                                 </button>
                                 <button type="button" @click="openConfirm('block', '{{ route('admin.problem-reports.block', $report->id) }}', 'Blokir Permanen?', 'Akun mitra akan segera dinonaktifkan secara permanen.')" 
                                         class="w-full bg-gradient-to-r from-red-600 to-rose-700 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider hover:from-red-700 hover:to-rose-800 transition-all duration-300 shadow-md active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                                     <i data-lucide="shield-off" class="w-3.5 h-3.5"></i> Blokir
                                 </button>
                                 <button type="button" @click="openConfirm('dismiss', '{{ route('admin.problem-reports.dismiss', $report->id) }}', 'Abaikan Laporan?', 'Laporan ini akan ditutup tanpa tindakan lebih lanjut.')" 
                                         class="w-full bg-white/60 border border-luxury-alabas/85 hover:border-slate-350 hover:bg-slate-50 text-luxury-slate px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider hover:text-luxury-forest transition-all duration-300 shadow-sm active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                                     <i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Abaikan
                                 </button>
                             </div>
                             @else
                                <div class="flex items-center justify-end gap-2 text-emerald-600/70">
                                    <i data-lucide="check-check" class="w-4 h-4 stroke-[3]"></i>
                                    <span class="text-[10px] font-black uppercase tracking-widest">Tindakan Selesai</span>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gradient-to-tr from-[#174413]/5 to-emerald-50 rounded-2xl flex items-center justify-center mb-4 border border-luxury-alabas/80">
                                    <i data-lucide="shield-check" class="w-8 h-8 text-luxury-forest/40"></i>
                                </div>
                                <h3 class="font-serif font-black text-xl text-luxury-forest mb-1">Kualitas Terjaga</h3>
                                <p class="text-sm text-luxury-slate font-medium">Belum ada laporan masalah makanan yang memerlukan tindakan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="reveal delay-300 relative z-10">
        {{ $reports->links() }}
    </div>

    <!-- Admin Action Modal -->
    <div x-show="isActionDialogOpen" 
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-[#0d1f0d]/60 backdrop-blur-md" @click="if (currentStep !== 3 && currentStep !== 4) isActionDialogOpen = false"></div>

        <!-- Panel -->
        <div x-show="isActionDialogOpen"
             x-transition:enter="ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 translate-y-10 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-10 scale-95"
             class="relative w-full max-w-lg bg-white rounded-[3rem] shadow-2xl z-10 overflow-hidden border border-white/70 p-10 md:p-12 text-center"
             @click.stop>
            
            <!-- STEP 1: CONFIRMATION -->
            <div x-show="currentStep === 1" class="text-center space-y-6 animate-in fade-in zoom-in duration-500">
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto shadow-lg border"
                      :class="{
                        'bg-orange-50 border-orange-100 text-orange-600': actionType === 'warn',
                        'bg-red-50 border-red-100 text-red-650': actionType === 'block',
                        'bg-slate-50 border-slate-100 text-slate-600': actionType === 'dismiss'
                     }">
                    <i x-show="actionType === 'warn'" data-lucide="alert-triangle" class="w-10 h-10 stroke-[2]"></i>
                    <i x-show="actionType === 'block'" data-lucide="shield-off" class="w-10 h-10 stroke-[2]"></i>
                    <i x-show="actionType === 'dismiss'" data-lucide="x-circle" class="w-10 h-10 stroke-[2]"></i>
                </div>

                <div class="space-y-2">
                    <h3 class="text-3xl font-serif font-black text-luxury-forest leading-tight" x-text="actionTitle"></h3>
                    <p class="text-xs font-semibold text-luxury-slate max-w-sm mx-auto leading-relaxed" x-text="actionMessage"></p>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="button" @click="isActionDialogOpen = false" 
                            class="flex-1 py-4 px-6 text-[10px] font-black uppercase tracking-[0.2em] text-luxury-slate hover:text-luxury-forest transition active:scale-95 cursor-pointer">
                        Batal
                    </button>
                    <button type="button" @click="nextStep()" 
                            class="flex-1 py-4 px-6 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] text-white transition active:scale-95 shadow-md cursor-pointer"
                            :class="{
                                'bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 shadow-orange-100': actionType === 'warn',
                                'bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 shadow-red-100': actionType === 'block',
                                'bg-gradient-to-r from-[#174413] to-emerald-600 hover:from-emerald-700 hover:to-emerald-800 shadow-emerald-100': actionType === 'dismiss'
                            }">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>

            <!-- STEP 2: INPUT REASON -->
            <div x-show="currentStep === 2" class="space-y-6 animate-in slide-in-from-right duration-500" x-cloak>
                <div class="text-center space-y-2">
                    <div class="w-16 h-16 rounded-2xl bg-luxury-ivory border border-luxury-alabas text-luxury-forest flex items-center justify-center mx-auto shadow-sm">
                        <i data-lucide="file-edit" class="w-7 h-7 stroke-[2]"></i>
                    </div>
                    <h3 class="text-2xl font-serif font-black text-luxury-forest leading-tight">Alasan Tindakan</h3>
                    <p class="text-xs text-luxury-slate font-medium" x-text="actionType === 'warn' ? 'Alasan ini akan dikirimkan sebagai notifikasi resmi ke Mitra.' : 'Alasan pemblokiran akun akan dicatat di sistem.'"></p>
                </div>

                <div class="space-y-2.5">
                    <label class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.2em] block text-left">Deskripsi Alasan</label>
                    <textarea x-model="reason" rows="4" required 
                              class="w-full bg-gray-50 border border-luxury-alabas/85 rounded-2xl p-5 outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 transition-all font-medium text-luxury-forest placeholder:text-luxury-slate/40 resize-none h-[110px] text-left" 
                              :placeholder="actionType === 'warn' ? 'Tuliskan pelanggaran yang dilakukan...' : 'Tuliskan alasan pemblokiran permanen...'"></textarea>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="button" @click="currentStep = 1" 
                            class="flex-1 py-4 px-6 text-[10px] font-black uppercase tracking-[0.2em] text-luxury-slate hover:text-luxury-forest transition active:scale-95 cursor-pointer">
                        Kembali
                    </button>
                    <button type="button" @click="submitAction()" :disabled="!reason.trim()"
                            class="flex-1 py-4 px-6 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] text-white transition active:scale-95 shadow-md disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            :class="actionType === 'warn' ? 'bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 shadow-orange-100' : 'bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 shadow-red-100'">
                        Kirim Tindakan
                    </button>
                </div>
            </div>

            <!-- STEP 3: LOADING -->
            <div x-show="currentStep === 3" class="text-center py-12 space-y-6 animate-in fade-in duration-500" x-cloak>
                <div class="w-20 h-20 relative flex items-center justify-center mx-auto">
                    <div class="absolute inset-0 border-4 border-luxury-alabas border-t-luxury-gold rounded-full animate-spin"></div>
                    <i data-lucide="shield" class="w-8 h-8 text-luxury-forest animate-pulse"></i>
                </div>
                <div class="space-y-1.5">
                    <h4 class="text-2xl font-serif font-black text-luxury-forest">Memproses Moderasi</h4>
                    <p class="text-[10px] text-luxury-gold font-black uppercase tracking-[0.2em] animate-pulse">Synchronizing with system...</p>
                </div>
            </div>

            <!-- STEP 4: SUCCESS -->
            <div x-show="currentStep === 4" class="text-center py-12 space-y-8 animate-in zoom-in-95 duration-500" x-cloak>
                <div class="w-24 h-24 bg-emerald-50 rounded-[2rem] flex items-center justify-center mx-auto border border-emerald-100 shadow-lg relative overflow-hidden">
                    <i data-lucide="check-circle" class="w-12 h-12 text-emerald-650 stroke-[3]"></i>
                    <div class="absolute inset-0 bg-gradient-to-tr from-emerald-500/10 to-transparent"></div>
                </div>
                <div class="space-y-2">
                    <h4 class="text-3xl font-serif font-black text-luxury-forest tracking-tight">Tindakan Berhasil!</h4>
                    <p class="text-xs text-luxury-slate font-semibold max-w-xs mx-auto leading-relaxed">Tindakan moderasi berhasil diterapkan dan status Mitra telah diperbarui.</p>
                </div>
            </div>

        </div>
    </div>

    <!-- Visual Evidence Modal -->
    <div x-show="isImageDialogOpen" 
         class="fixed inset-0 z-[120] flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-[#0d1f0d]/60 backdrop-blur-md" @click="isImageDialogOpen = false"></div>

        <!-- Panel -->
        <div x-show="isImageDialogOpen"
             x-transition:enter="ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 translate-y-10 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-10 scale-95"
             class="relative w-full max-w-2xl bg-white rounded-[3rem] shadow-2xl z-10 overflow-hidden border border-white/70 p-8 text-center"
             @click.stop>
            
            <button type="button" @click="isImageDialogOpen = false" 
                    class="absolute top-6 right-6 w-10 h-10 rounded-full bg-luxury-ivory hover:bg-luxury-alabas text-luxury-slate hover:text-luxury-forest transition-all flex items-center justify-center border border-luxury-alabas shadow-sm cursor-pointer active:scale-95 z-20">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <h3 class="text-2xl font-serif font-black text-luxury-forest mb-4 text-left">Bukti Visual</h3>
            <div class="w-full h-[55vh] rounded-[2rem] overflow-hidden border border-luxury-alabas/80 shadow-sm bg-slate-50 flex items-center justify-center relative">
                <!-- Loader Skeleton -->
                <div x-show="isImageLoading" class="absolute inset-0 bg-slate-50 flex flex-col items-center justify-center gap-3">
                    <div class="w-12 h-12 border-4 border-luxury-alabas border-t-luxury-gold rounded-full animate-spin"></div>
                    <span class="text-xs font-bold text-luxury-slate animate-pulse">Memuat Bukti Visual...</span>
                </div>

                <img :src="currentImageUrl" @load="isImageLoading = false" x-show="!isImageLoading" class="max-w-full max-h-[55vh] object-contain rounded-[2rem]" alt="Bukti Visual">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endpush
@endsection

