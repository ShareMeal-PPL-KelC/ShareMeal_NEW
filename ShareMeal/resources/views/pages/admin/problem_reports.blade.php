@extends('layouts.dashboard')

@section('content')
<div class="space-y-12" x-data="{
    isActionDialogOpen: false,
    actionType: '', // 'warn', 'block', 'dismiss'
    actionUrl: '',
    actionTitle: '',
    actionMessage: '',
    reason: '',
    currentStep: 1, // 1: confirm, 2: input reason, 3: loading, 4: success
    isProcessing: false,
    
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
    <!-- Header Page -->
    <div class="flex justify-between items-end reveal">
        <div>
            <span class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em] mb-3 block">Security & Quality</span>
            <h1 class="text-4xl font-extrabold text-luxury-forest tracking-tight">Moderasi Laporan</h1>
            <p class="text-luxury-slate mt-2 text-sm font-medium">Tinjau dan ambil tindakan terhadap laporan kualitas makanan dari pengguna</p>
        </div>
        <div class="hidden md:flex items-center gap-3">
            <div class="glass-panel px-6 py-3 rounded-2xl border-luxury-alabas flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-luxury-gold animate-pulse"></div>
                <span class="text-xs font-bold text-luxury-forest uppercase tracking-widest">Active Monitoring</span>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 reveal delay-100">
        <div class="glass-card p-6 rounded-[2rem] border-luxury-alabas flex flex-col justify-between">
            <span class="text-[10px] font-black text-luxury-gold uppercase tracking-widest">Total Laporan</span>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-black text-luxury-forest leading-none">{{ $reports->total() }}</span>
                <span class="text-xs text-luxury-slate font-bold uppercase">Kasus</span>
            </div>
        </div>
        <div class="glass-card p-6 rounded-[2rem] border-luxury-alabas flex flex-col justify-between">
            <span class="text-[10px] font-black text-orange-400 uppercase tracking-widest">Pending</span>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-black text-orange-600 leading-none">{{ $reports->where('status', 'pending')->count() }}</span>
            </div>
        </div>
        <div class="glass-card p-6 rounded-[2rem] border-luxury-alabas flex flex-col justify-between">
            <span class="text-[10px] font-black text-luxury-emerald uppercase tracking-widest">Resolved</span>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-black text-luxury-emerald leading-none">{{ $reports->where('status', 'resolved')->count() }}</span>
            </div>
        </div>
        <div class="glass-card p-6 rounded-[2rem] border-luxury-alabas flex flex-col justify-between bg-luxury-forest/5">
            <span class="text-[10px] font-black text-luxury-forest uppercase tracking-widest">High Priority</span>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-black text-luxury-forest leading-none">{{ $reports->where('status', 'pending')->count() > 5 ? '🔥' : 'Low' }}</span>
            </div>
        </div>
    </div>

    <!-- Reports Table / List -->
    <div class="glass-card rounded-[2.5rem] border-luxury-alabas overflow-hidden reveal delay-200">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-luxury-forest/[0.02] border-b border-luxury-alabas">
                        <th class="px-10 py-6 text-[10px] font-black text-luxury-gold uppercase tracking-[0.2em]">Pelapor & Mitra</th>
                        <th class="px-10 py-6 text-[10px] font-black text-luxury-gold uppercase tracking-[0.2em]">Detail Masalah</th>
                        <th class="px-10 py-6 text-[10px] font-black text-luxury-gold uppercase tracking-[0.2em]">Status</th>
                        <th class="px-10 py-6 text-[10px] font-black text-luxury-gold uppercase tracking-[0.2em] text-right">Moderasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-alabas/50">
                    @forelse($reports as $report)
                    <tr class="hover:bg-luxury-forest/[0.01] transition-colors group">
                        <td class="px-10 py-8">
                            <div class="space-y-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-2xl bg-white border border-luxury-alabas flex items-center justify-center text-luxury-forest font-black text-xs shadow-sm">
                                        {{ substr($report->reporter->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-luxury-slate uppercase tracking-widest mb-0.5">Pelapor</p>
                                        <span class="text-sm font-bold text-luxury-charcoal">{{ $report->reporter->name }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-2xl bg-red-50 border border-red-100 flex items-center justify-center text-red-600 shadow-sm">
                                        <i data-lucide="store" class="w-4 h-4 stroke-[2.5]"></i>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-red-400 uppercase tracking-widest mb-0.5">Terlapor (Mitra)</p>
                                        <span class="text-sm font-bold text-luxury-charcoal">{{ $report->mitra->displayName }}</span>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] text-luxury-slate font-medium">Warnings:</span>
                                            <span class="text-[10px] font-black text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded-lg border border-orange-100">{{ $report->mitra->warnings_count ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-10 py-8 max-w-md">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-600 text-white text-[9px] font-black uppercase tracking-widest mb-4 shadow-sm">
                                <span class="w-1 h-1 rounded-full bg-white animate-pulse"></span>
                                {{ $report->issue_label }}
                            </div>
                            <p class="text-sm text-luxury-slate leading-relaxed font-medium line-clamp-3 mb-4">{{ $report->description }}</p>
                            
                            <div class="flex items-center gap-6">
                                @if($report->evidence_image)
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($report->evidence_image) }}" target="_blank" class="text-[10px] font-black text-luxury-forest flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-luxury-alabas hover:border-luxury-gold transition-all shadow-sm">
                                    <i data-lucide="image" class="w-3.5 h-3.5"></i> Bukti Visual
                                </a>
                                @endif
                                <div class="text-[10px] text-luxury-slate/60 font-bold uppercase tracking-tighter">
                                    <span class="text-luxury-gold">Via:</span> {{ $report->order_id ? 'Pesanan #' . $report->order_id : 'Donasi #' . $report->donation_id }}
                                    • {{ $report->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </td>
                        <td class="px-10 py-8">
                            <div class="flex flex-col gap-2">
                                <span class="inline-flex items-center justify-center w-fit px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest border shadow-sm
                                    {{ $report->status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : 
                                       ($report->status === 'resolved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 
                                       'bg-slate-50 text-slate-700 border-slate-200') }}">
                                    {{ $report->status === 'pending' ? 'Review' : ($report->status === 'resolved' ? 'Selesai' : 'Abaikan') }}
                                </span>
                                @if($report->admin_note)
                                    <div class="p-3 rounded-2xl bg-white border border-luxury-alabas text-[10px] text-luxury-slate leading-relaxed shadow-sm italic">
                                        <span class="text-luxury-gold not-italic font-black uppercase block mb-1">Catatan Admin:</span>
                                        "{{ $report->admin_note }}"
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-10 py-8 text-right">
                             @if($report->status === 'pending')
                             <div class="flex flex-col gap-2 w-40 ml-auto">
                                 <button type="button" @click="openConfirm('warn', '{{ route('admin.problem-reports.warn', $report->id) }}', 'Kirim Warning?', 'Mitra akan mendapatkan peringatan resmi terkait kualitas produk.')" class="w-full bg-luxury-forest text-white px-4 py-2.5 rounded-xl text-[10px] font-black uppercase hover:bg-black transition shadow-md flex items-center justify-center gap-2 group-hover:scale-[1.02]">
                                     <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-luxury-gold"></i> Warning
                                 </button>
                                 <button type="button" @click="openConfirm('block', '{{ route('admin.problem-reports.block', $report->id) }}', 'Blokir Permanen?', 'Akun mitra akan segera dinonaktifkan secara permanen.')" class="w-full bg-red-600 text-white px-4 py-2.5 rounded-xl text-[10px] font-black uppercase hover:bg-red-800 transition shadow-md flex items-center justify-center gap-2 group-hover:scale-[1.02]">
                                     <i data-lucide="shield-off" class="w-3.5 h-3.5"></i> Blokir
                                 </button>
                                 <button type="button" @click="openConfirm('dismiss', '{{ route('admin.problem-reports.dismiss', $report->id) }}', 'Abaikan Laporan?', 'Laporan ini akan ditutup tanpa tindakan lebih lanjut.')" class="w-full bg-white border border-luxury-alabas text-luxury-slate px-4 py-2.5 rounded-xl text-[10px] font-black uppercase hover:bg-luxury-ivory transition shadow-sm flex items-center justify-center gap-2 group-hover:scale-[1.02]">
                                     <i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Abaikan
                                 </button>
                             </div>
                            @else
                                <div class="flex items-center justify-end gap-2 text-emerald-600 opacity-60">
                                    <i data-lucide="check-check" class="w-4 h-4 stroke-[3]"></i>
                                    <span class="text-[10px] font-black uppercase tracking-widest">Tindakan Selesai</span>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-10 py-32 text-center">
                            <div class="w-24 h-24 bg-luxury-ivory rounded-[2rem] flex items-center justify-center mx-auto mb-6 border-2 border-luxury-alabas border-dashed">
                                <i data-lucide="shield-check" class="w-10 h-10 text-luxury-alabas"></i>
                            </div>
                            <h3 class="text-2xl font-serif font-black text-luxury-forest mb-2 tracking-tight">Kualitas Terjaga</h3>
                            <p class="text-luxury-slate text-sm font-medium">Belum ada laporan masalah makanan yang memerlukan tindakan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="reveal delay-300">
        {{ $reports->links() }}
    </div>

    <!-- Admin Action Modal -->
    <div x-show="isActionDialogOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" x-cloak>
        <div class="fixed inset-0 bg-luxury-charcoal/60 backdrop-blur-xl transition-opacity" 
             x-show="isActionDialogOpen" x-transition:enter="ease-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
             @click="if (currentStep !== 3 && currentStep !== 4) isActionDialogOpen = false"></div>

        <div x-show="isActionDialogOpen"
             x-transition:enter="ease-out duration-600"
             x-transition:enter-start="opacity-0 translate-y-12 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="relative bg-white w-full max-w-lg rounded-[3rem] overflow-hidden shadow-2xl border border-white/20 p-12 z-10 text-left glass-panel">
            
            <!-- STEP 1: CONFIRMATION -->
            <div x-show="currentStep === 1" class="text-center space-y-8 animate-in fade-in zoom-in duration-500">
                <div class="w-24 h-24 rounded-[2rem] flex items-center justify-center mx-auto shadow-lg border-2"
                     :class="{
                        'bg-orange-50 border-orange-100 text-orange-600': actionType === 'warn',
                        'bg-red-50 border-red-100 text-red-600': actionType === 'block',
                        'bg-luxury-ivory border-luxury-alabas text-luxury-slate': actionType === 'dismiss'
                     }">
                    <template x-if="actionType === 'warn'"><i data-lucide="alert-triangle" class="w-12 h-12 stroke-[2]"></i></template>
                    <template x-if="actionType === 'block'"><i data-lucide="shield-off" class="w-12 h-12 stroke-[2]"></i></template>
                    <template x-if="actionType === 'dismiss'"><i data-lucide="x-circle" class="w-12 h-12 stroke-[2]"></i></template>
                </div>

                <div class="space-y-4">
                    <h3 class="text-3xl font-serif font-black text-luxury-forest leading-tight" x-text="actionTitle"></h3>
                    <p class="text-sm font-medium text-luxury-slate max-w-sm mx-auto leading-relaxed" x-text="actionMessage"></p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 pt-4">
                    <button type="button" @click="isActionDialogOpen = false" 
                            class="flex-1 py-5 rounded-2xl font-black uppercase tracking-widest text-[10px] bg-luxury-ivory hover:bg-luxury-alabas text-luxury-slate transition-all duration-300">
                        Batal
                    </button>
                    <button type="button" @click="nextStep()" 
                            class="flex-1 py-5 rounded-2xl font-black uppercase tracking-widest text-[10px] text-white transition-all duration-300 shadow-xl"
                            :class="{
                                'bg-orange-600 hover:bg-orange-700': actionType === 'warn',
                                'bg-red-600 hover:bg-red-700': actionType === 'block',
                                'bg-luxury-forest hover:bg-black': actionType === 'dismiss'
                            }">
                        Konfirmasi
                    </button>
                </div>
            </div>

            <!-- STEP 2: INPUT REASON -->
            <div x-show="currentStep === 2" class="space-y-8 animate-in slide-in-from-right duration-500" x-cloak>
                <div class="text-center">
                    <div class="w-20 h-20 rounded-2xl bg-luxury-ivory border border-luxury-alabas text-luxury-forest flex items-center justify-center mx-auto mb-6 shadow-sm">
                        <i data-lucide="file-edit" class="w-8 h-8 stroke-[2]"></i>
                    </div>
                    <h3 class="text-2xl font-serif font-black text-luxury-forest leading-tight">Alasan Tindakan</h3>
                    <p class="text-xs text-luxury-slate mt-2 font-medium" x-text="actionType === 'warn' ? 'Alasan ini akan dikirimkan sebagai notifikasi resmi ke Mitra.' : 'Alasan pemblokiran akun akan dicatat di sistem.'"></p>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.2em] block text-left">Deskripsi Alasan</label>
                    <textarea x-model="reason" rows="4" required 
                              class="w-full bg-luxury-ivory border border-luxury-alabas rounded-[1.5rem] p-6 outline-none focus:ring-2 focus:ring-luxury-gold transition-all font-medium text-luxury-charcoal placeholder:text-luxury-alabas resize-none h-[140px] text-left" 
                              :placeholder="actionType === 'warn' ? 'Tuliskan pelanggaran yang dilakukan...' : 'Tuliskan alasan pemblokiran permanen...'"></textarea>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 pt-4">
                    <button type="button" @click="currentStep = 1" 
                            class="flex-1 py-5 rounded-2xl font-black uppercase tracking-widest text-[10px] bg-luxury-ivory hover:bg-luxury-alabas text-luxury-slate transition duration-300">
                        Kembali
                    </button>
                    <button type="button" @click="submitAction()" :disabled="!reason.trim()"
                            class="flex-1 py-5 rounded-2xl font-black uppercase tracking-widest text-[10px] text-white transition-all duration-300 shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="{
                                'bg-orange-600 hover:bg-orange-700': actionType === 'warn',
                                'bg-red-600 hover:bg-red-700': actionType === 'block'
                            }">
                        Kirim Tindakan
                    </button>
                </div>
            </div>

            <!-- STEP 3: LOADING -->
            <div x-show="currentStep === 3" class="text-center py-16 space-y-10 animate-in fade-in duration-500" x-cloak>
                <div class="w-24 h-24 relative flex items-center justify-center mx-auto">
                    <div class="absolute inset-0 border-4 border-luxury-alabas border-t-luxury-gold rounded-full animate-spin"></div>
                    <i data-lucide="shield" class="w-10 h-10 text-luxury-forest animate-pulse"></i>
                </div>
                <div class="space-y-3">
                    <h4 class="text-2xl font-serif font-black text-luxury-forest">Memproses Moderasi</h4>
                    <p class="text-[9px] text-luxury-gold font-black uppercase tracking-[0.3em] animate-pulse">Synchronizing with system...</p>
                </div>
            </div>

            <!-- STEP 4: SUCCESS -->
            <div x-show="currentStep === 4" class="text-center py-16 space-y-10 animate-in zoom-in-95 duration-500" x-cloak>
                <div class="w-28 h-24 bg-emerald-50 rounded-[2.5rem] flex items-center justify-center mx-auto border border-emerald-100 shadow-lg relative overflow-hidden group">
                    <i data-lucide="check-circle" class="w-14 h-14 text-emerald-600 stroke-[2.5] animate-in slide-in-from-bottom-4 duration-700"></i>
                    <div class="absolute inset-0 bg-gradient-to-tr from-emerald-500/10 to-transparent"></div>
                </div>
                <div class="space-y-3">
                    <h4 class="text-4xl font-serif font-black text-luxury-forest tracking-tighter">Moderasi Sukses!</h4>
                    <p class="text-sm text-luxury-slate max-w-xs mx-auto leading-relaxed font-medium">Tindakan telah berhasil diproses dan status Mitra telah diperbarui.</p>
                </div>
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

