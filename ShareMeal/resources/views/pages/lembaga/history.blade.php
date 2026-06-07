@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <div class="mb-12 reveal">
        <h1 class="text-5xl font-serif font-bold text-luxury-forest leading-tight">Riwayat Penerimaan Donasi</h1>
        <p class="text-luxury-slate font-medium mt-2 tracking-wide font-sans">Daftar donasi makanan surplus yang berhasil disalurkan dan diterima oleh lembaga Anda.</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-750 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm animate-in fade-in duration-350">
        <i data-lucide="check-circle" class="w-5 h-5"></i>
        <span class="font-bold text-sm">{{ session('success') }}</span>
    </div>
    @endif

    <!-- History Stats Card -->
    <div class="p-8 rounded-[2.5rem] mb-10 flex items-center justify-between bg-gradient-to-br from-[#1b1e4b] to-[#2c3175] text-white border border-white/10 shadow-2xl relative overflow-hidden">
        <div class="absolute top-[-30%] left-[-15%] w-[25rem] h-[25rem] bg-indigo-400/20 rounded-full blur-[80px] pointer-events-none"></div>
        <div class="relative z-10">
            <span class="bg-white/10 text-indigo-300 border border-white/10 backdrop-blur px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest mb-3 inline-block">
                Ringkasan Kontribusi
            </span>
            <h2 class="text-3xl font-serif font-bold text-white">Donasi Tersalurkan</h2>
            <p class="text-indigo-100/80 text-sm mt-1">Lembaga Anda telah berhasil menyalurkan berbagai donasi makanan untuk masyarakat.</p>
        </div>
        <div class="relative z-10 text-right">
            <div class="text-6xl font-serif font-black text-white leading-none">{{ count($completedDonations) }}</div>
            <div class="text-[9px] text-indigo-300 font-black uppercase tracking-[0.2em] mt-3">Total Donasi Diterima</div>
        </div>
    </div>

    <!-- Completed Donations List -->
    <div class="space-y-6">
        @if(count($completedDonations) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 animate-in fade-in duration-500">
                @foreach($completedDonations as $d)
                    <div class="glass-card rounded-[2.5rem] overflow-hidden transition-all duration-500 flex flex-col justify-between group hover:shadow-lg hover:bg-white/80">
                        <div class="p-8 flex-1 flex flex-col justify-between bg-white/10">
                            <div>
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <h3 class="font-serif text-2xl font-bold text-luxury-forest group-hover:text-luxury-gold transition-colors">{{ $d['store']['name'] }}</h3>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-green-50 border border-green-200 px-3 py-1 text-[10px] font-black text-green-700 uppercase tracking-wider">
                                                <i data-lucide="check-circle" class="w-3 h-3"></i> Selesai
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 mt-3 text-[10px] font-bold text-luxury-slate uppercase tracking-wider">
                                            <span class="flex items-center gap-1">📍 {{ $d['store']['address'] }}</span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-mono font-black text-luxury-slate tracking-widest">#{{ $d['id'] }}</span>
                                </div>

                                <!-- Items Section -->
                                <div class="border-t border-luxury-alabas/50 mt-6 pt-6">
                                    <h4 class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.25em] mb-3">Item Donasi</h4>
                                    <div class="space-y-2">
                                        @foreach($d['items'] as $item)
                                            <div class="flex items-center justify-between text-xs bg-white/40 p-4 rounded-xl border border-luxury-alabas">
                                                <span class="text-luxury-forest font-bold">{{ $item['name'] }}</span>
                                                <span class="font-black text-luxury-gold uppercase tracking-wider">{{ $item['quantity'] }} {{ $item['unit'] ?? 'unit' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Transaction Detail Timeline -->
                                <div class="border-t border-luxury-alabas/50 mt-6 pt-6">
                                    <div class="bg-green-50/50 border border-green-100 rounded-2xl p-5 shadow-sm">
                                        <div class="flex items-center gap-3 mb-3 text-green-800 font-bold text-sm">
                                            <i data-lucide="info" class="w-5 h-5 text-green-600"></i>
                                            <span>Detail Penyaluran</span>
                                        </div>
                                        <div class="text-xs text-green-900 space-y-2 leading-relaxed font-sans">
                                            <p class="flex justify-between"><span>Diklaim pada:</span> <span class="font-bold">{{ $d['claimed_at'] ?? '-' }}</span></p>
                                            <p class="flex justify-between"><span>Jadwal Penjemputan:</span> <span class="font-bold">{{ $d['pickup_time'] ?? '-' }}</span></p>
                                            <p class="flex justify-between"><span>Diterima pada:</span> <span class="font-bold">{{ $d['delivered_at'] ?? $d['claimed_at'] ?? '-' }}</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white/40 rounded-[2.5rem] border border-dashed border-gray-200 p-16 text-center shadow-sm">
                <i data-lucide="history" class="w-16 h-16 text-gray-300 mx-auto mb-4 animate-pulse"></i>
                <h3 class="text-xl font-serif font-bold text-gray-900 mb-2">Belum Ada Donasi Selesai</h3>
                <p class="text-gray-500 max-w-sm mx-auto text-sm font-sans">Semua donasi yang disalurkan dan diterima oleh lembaga Anda akan tercatat di halaman ini.</p>
            </div>
        @endif
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
