@extends('layouts.dashboard')

@section('content')
<div class="space-y-8" x-data="{ 
    previewModalOpen: false, 
    previewUrl: '', 
    previewTitle: '',
    rejectModalOpen: false,
    rejectUserId: null,
    rejectUserName: '',
    isPreviewLoading: false,
    
    openPreview(url, title) {
        this.previewUrl = url;
        this.previewTitle = title;
        this.isPreviewLoading = true;
        this.previewModalOpen = true;
    },
    
    openReject(id, name) {
        this.rejectUserId = id;
        this.rejectUserName = name;
        this.rejectModalOpen = true;
    }
}">
    <!-- Decorative Glow Top Right -->
    <div class="absolute top-0 right-0 w-[30rem] h-[30rem] bg-emerald-100/10 rounded-full blur-[120px] pointer-events-none z-0"></div>

    <!-- Title Header -->
    <div class="relative z-10 mb-12 reveal">
        <h1 class="text-5xl font-serif font-black text-luxury-forest leading-tight tracking-tight">Verifikasi Dokumen</h1>
        <p class="text-luxury-slate font-medium mt-3 text-lg leading-relaxed max-w-3xl">Tinjau secara cermat dokumen legalitas dasar, izin operasional, dan lokasi fisik pendaftar baru Mitra & Lembaga Sosial sebelum diizinkan bertransaksi.</p>
    </div>

    @if(count($applications) > 0)
        <div class="space-y-8 relative z-10">
            @foreach($applications as $app)
                <div class="glass-card rounded-[2.5rem] overflow-hidden transition-all duration-500 hover:shadow-2xl hover:shadow-emerald-950/5 group reveal">
                    <div class="p-8 md:p-10">
                        <!-- Top Row: Avatar, Info, Actions -->
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 border-b border-luxury-alabas/70 pb-8">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                                <!-- Avatar -->
                                <div class="h-16 w-16 shrink-0 rounded-[1.3rem] bg-gradient-to-tr from-[#174413] to-emerald-600 text-white border-2 border-white shadow-md flex items-center justify-center font-serif font-black text-2xl transition-transform duration-500 group-hover:scale-105">
                                    {{ substr($app['name'], 0, 1) }}
                                </div>
                                <!-- Applicant Info -->
                                <div>
                                    <h3 class="font-serif text-2xl font-bold text-luxury-forest tracking-tight">{{ $app['name'] }}</h3>
                                    <div class="flex flex-wrap items-center gap-3 mt-3">
                                        <!-- Type Badge -->
                                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $app['type'] === 'mitra' ? 'bg-amber-50 text-amber-800 border border-amber-200/50 shadow-sm' : 'bg-indigo-50 text-indigo-800 border border-indigo-200/50 shadow-sm' }}">
                                            <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $app['type'] === 'mitra' ? 'bg-amber-500' : 'bg-indigo-500' }}"></span>
                                            {{ $app['type'] === 'mitra' ? 'Mitra Resto' : 'Lembaga Sosial' }}
                                        </span>
                                        <!-- Email Badge -->
                                        <span class="inline-flex items-center px-3.5 py-1.5 rounded-full bg-white/50 border border-luxury-alabas/50 text-[10px] font-bold text-luxury-slate uppercase tracking-wider">
                                            <i data-lucide="mail" class="w-3.5 h-3.5 text-luxury-gold mr-1.5"></i>
                                            {{ $app['email'] }}
                                        </span>
                                        <!-- Date Badge -->
                                        <span class="inline-flex items-center px-3.5 py-1.5 rounded-full bg-white/50 border border-luxury-alabas/50 text-[10px] font-bold text-luxury-slate uppercase tracking-wider">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-luxury-gold mr-1.5"></i>
                                            {{ $app['submitted_at'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex items-center gap-3 self-end lg:self-center">
                                <form action="{{ route('admin.verification.approve', $app['id']) }}" method="POST">
                                    @csrf
                                    <button id="btn-approve-{{ $app['id'] }}" type="submit" 
                                            class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-green-600 text-white py-4 px-7 rounded-2xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-emerald-100 hover:shadow-xl hover:shadow-emerald-250/20 hover:from-emerald-700 hover:to-green-700 transition active:scale-95 cursor-pointer">
                                        <i data-lucide="check" class="w-4 h-4 stroke-[3]"></i>
                                        Setujui Pendaftaran
                                    </button>
                                </form>
                                <button id="btn-reject-{{ $app['id'] }}" @click="openReject({{ $app['id'] }}, '{{ $app['name'] }}')" 
                                        class="inline-flex items-center gap-2 bg-white border border-red-200 text-red-650 py-4 px-7 rounded-2xl font-black uppercase tracking-widest text-[10px] hover:bg-red-50 hover:border-red-300 transition active:scale-95 cursor-pointer">
                                        <i data-lucide="x" class="w-4 h-4 stroke-[3]"></i>
                                        Tolak
                                </button>
                            </div>
                        </div>

                        <!-- Documents Section -->
                        <div class="mt-8">
                            <div class="flex items-center gap-2.5 mb-6">
                                <div class="h-1.5 w-6 bg-luxury-gold rounded-full"></div>
                                <h4 class="text-[11px] font-black text-luxury-gold uppercase tracking-[0.25em]">Berkas Dokumen Resmi</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($app['documents'] as $key => $path)
                                    @if($path)
                                        <div class="p-6 rounded-[2rem] border border-luxury-alabas bg-white/45 flex flex-col justify-between hover:bg-white hover:shadow-lg transition-all duration-300 group/doc">
                                            <div class="flex items-start gap-4 mb-6">
                                                <div class="w-12 h-12 bg-emerald-50 text-[#174413] border border-emerald-100/50 rounded-2xl flex items-center justify-center shadow-sm group-hover/doc:scale-110 group-hover/doc:bg-emerald-100 transition-all duration-300 shrink-0">
                                                    <i data-lucide="file-text" class="w-5 h-5 stroke-[2]"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-sm font-bold text-luxury-forest truncate uppercase tracking-wide group-hover/doc:text-luxury-gold transition-colors">{{ str_replace('_', ' ', $key) }}</div>
                                                    <div class="text-[9px] font-bold text-orange-600 bg-orange-50 border border-orange-100 rounded-md px-2 py-0.5 inline-block uppercase tracking-wider mt-1.5">Menunggu Validasi</div>
                                                </div>
                                            </div>
                                            <button id="btn-preview-{{ $app['id'] }}-{{ $key }}" @click="openPreview('{{ asset('storage/' . $path) }}', '{{ strtoupper($key) }} - {{ $app['name'] }}')" 
                                                    class="w-full py-4 bg-white border border-luxury-alabas/85 rounded-xl text-xs font-black uppercase tracking-wider text-[#174413] hover:bg-emerald-50 hover:border-emerald-200 transition duration-300 cursor-pointer shadow-sm">
                                                Preview Dokumen
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Beautiful Glassmorphism Empty State -->
        <div class="bg-white/40 rounded-[3rem] border border-dashed border-gray-250 p-20 text-center shadow-sm relative overflow-hidden reveal">
            <div class="absolute inset-0 bg-gradient-to-b from-white/30 to-transparent pointer-events-none"></div>
            <div class="h-24 w-24 bg-gradient-to-tr from-emerald-50 to-green-50 text-emerald-600 rounded-3xl flex items-center justify-center mx-auto mb-8 border border-emerald-100 shadow-inner group">
                <i data-lucide="shield-check" class="w-12 h-12 text-emerald-600 group-hover:scale-110 transition-transform duration-500"></i>
            </div>
            <h3 class="text-3xl font-serif font-black text-luxury-forest mb-3">Tidak Ada Antrian Verifikasi</h3>
            <p class="text-luxury-slate font-medium max-w-md mx-auto text-base leading-relaxed">Semua berkas Mitra Resto dan Lembaga Sosial pendaftar baru saat ini sudah selesai diperiksa dan terverifikasi secara penuh.</p>
        </div>
    @endif

    <!-- Preview Modal (Z-Index 100 exactly) -->
    <div x-show="previewModalOpen" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-luxury-forest/80 backdrop-blur-md" @click="previewModalOpen = false"></div>
        
        <!-- Modal Content -->
        <div x-show="previewModalOpen"
             x-transition:enter="ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 translate-y-12 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-12 scale-95"
             class="relative bg-white/90 backdrop-blur-2xl rounded-[3rem] w-full max-w-5xl shadow-2xl overflow-hidden z-10 border border-white/60 flex flex-col max-h-[90vh]">
            
            <!-- Close button MUST be the FIRST button inside for Dusk tests to trigger click perfectly -->
            <div class="p-8 border-b border-gray-150 flex items-center justify-between sticky top-0 bg-white/95 backdrop-blur-md z-10">
                <h3 class="text-2xl font-serif font-black text-luxury-forest tracking-tight" x-text="previewTitle"></h3>
                <button @click="previewModalOpen = false" class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-650 transition-all cursor-pointer border border-gray-200/50 shadow-sm">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <!-- Document Frame Content -->
            <div class="p-8 bg-gray-50/50 flex justify-center items-center overflow-y-auto flex-1 min-h-[500px] relative">
                <!-- Loading Indicator -->
                <div x-show="isPreviewLoading" 
                     class="absolute inset-0 bg-white/80 backdrop-blur-sm z-25 flex flex-col items-center justify-center p-6 text-center"
                     x-cloak>
                    <div class="relative w-16 h-16 mb-4">
                        <div class="absolute inset-0 border-4 border-luxury-forest/10 rounded-full"></div>
                        <div class="absolute inset-0 border-4 border-luxury-forest border-t-transparent rounded-full animate-spin"></div>
                    </div>
                    <span class="text-xs font-bold text-luxury-forest uppercase tracking-wider">Memuat Berkas...</span>
                </div>

                <template x-if="previewUrl.toLowerCase().endsWith('.pdf')">
                    <iframe :src="previewUrl" x-on:load="isPreviewLoading = false" class="w-full h-[600px] rounded-2xl shadow-xl border border-luxury-alabas bg-white z-10"></iframe>
                </template>
                <template x-if="!previewUrl.toLowerCase().endsWith('.pdf')">
                    <img :src="previewUrl" x-on:load="isPreviewLoading = false" x-on:error="isPreviewLoading = false" class="max-w-full h-auto rounded-2xl shadow-xl border-4 border-white object-contain max-h-[600px] z-10">
                </template>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="rejectModalOpen" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-red-950/65 backdrop-blur-md" @click="rejectModalOpen = false"></div>
        
        <!-- Modal Content -->
        <div x-show="rejectModalOpen"
             x-transition:enter="ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 translate-y-12 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-12 scale-95"
             class="relative bg-white rounded-[3.5rem] w-full max-w-md p-10 shadow-2xl border border-red-100/50 z-10" @click.stop>
            
            <div class="h-16 w-16 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-red-100 shadow-sm">
                <i data-lucide="alert-triangle" class="w-8 h-8 stroke-[2.5]"></i>
            </div>
            
            <h3 class="text-3xl font-serif font-black text-gray-900 mb-2 text-center tracking-tight">Tolak Verifikasi</h3>
            <p class="text-xs text-luxury-slate font-medium mb-8 text-center leading-relaxed">Berikan alasan penolakan dokumen yang valid untuk <span class="text-red-650 font-extrabold" x-text="rejectUserName"></span> agar mereka dapat segera merevisi berkas.</p>
            
            <form :action="'{{ url('admin/verification') }}/' + rejectUserId + '/reject'" method="POST" class="space-y-6">
                @csrf
                <div>
                    <textarea name="reason" rows="4" required 
                              class="w-full rounded-2xl border border-luxury-alabas/85 p-5 text-sm bg-gray-50/50 outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500 transition-all font-medium text-gray-950 placeholder:text-gray-400 resize-none h-[140px]" 
                              placeholder="Contoh: Berkas KTP tidak terbaca jelas atau Surat Izin Operasional LKS sudah tidak berlaku..."></textarea>
                </div>
                <div class="flex gap-4 border-t border-gray-100 pt-6 justify-end">
                    <button type="button" @click="rejectModalOpen = false" 
                            class="py-4 px-6 text-[10px] font-black uppercase tracking-[0.2em] text-gray-550 hover:text-gray-900 transition active:scale-95 cursor-pointer">
                        Batal
                    </button>
                    <button id="btn-confirm-reject" type="submit" 
                            class="bg-gradient-to-r from-red-600 to-red-700 text-white py-4 px-8 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] shadow-lg shadow-red-100 hover:shadow-xl hover:from-red-700 hover:to-red-800 transition active:scale-95 cursor-pointer">
                        Konfirmasi Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endsection
