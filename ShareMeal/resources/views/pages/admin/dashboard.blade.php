@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-12 reveal">
        <h1 class="text-5xl font-serif font-bold text-luxury-forest leading-tight">Dashboard Admin</h1>
        <p class="text-luxury-slate font-medium mt-2 tracking-wide">Pantau metrik utama, verifikasi legalitas, dan moderasi platform ShareMeal secara real-time.</p>
    </div>

    <!-- Stats Grid 6 Items -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-12">
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group transition-all duration-500 reveal text-center">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 border border-blue-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-blue-100 transition-all duration-300 mx-auto">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <div class="text-2xl font-serif font-black text-luxury-forest group-hover:text-blue-600 transition-colors leading-none">{{ $stats['total_user'] }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Total User</div>
        </div>
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group transition-all duration-500 reveal text-center">
            <div class="w-12 h-12 bg-orange-50 text-orange-600 border border-orange-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-orange-100 transition-all duration-300 mx-auto animate-pulse">
                <i data-lucide="shield" class="w-5 h-5"></i>
            </div>
            <div class="text-2xl font-serif font-black text-luxury-forest group-hover:text-orange-600 transition-colors leading-none">{{ $stats['pending'] }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Pending</div>
        </div>
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group transition-all duration-500 reveal text-center">
            <div class="w-12 h-12 bg-green-50 text-green-600 border border-green-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-green-100 transition-all duration-300 mx-auto">
                <i data-lucide="store" class="w-5 h-5"></i>
            </div>
            <div class="text-2xl font-serif font-black text-luxury-forest group-hover:text-green-600 transition-colors leading-none">{{ $stats['mitra_aktif'] }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Mitra Aktif</div>
        </div>
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group transition-all duration-500 reveal text-center">
            <div class="w-12 h-12 bg-purple-50 text-purple-600 border border-purple-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-purple-100 transition-all duration-300 mx-auto">
                <i data-lucide="heart" class="w-5 h-5"></i>
            </div>
            <div class="text-2xl font-serif font-black text-luxury-forest group-hover:text-purple-600 transition-colors leading-none">{{ $stats['lembaga_aktif'] }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Lembaga Aktif</div>
        </div>
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group transition-all duration-500 reveal text-center">
            <div class="w-12 h-12 bg-blue-50 text-blue-650 border border-blue-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-blue-100 transition-all duration-300 mx-auto">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
            </div>
            <div class="text-2xl font-serif font-black text-luxury-forest group-hover:text-blue-650 transition-colors leading-none">{{ $stats['transaksi'] }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Transaksi</div>
        </div>
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group transition-all duration-500 reveal text-center">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-emerald-100 transition-all duration-300 mx-auto">
                <i data-lucide="package" class="w-5 h-5"></i>
            </div>
            <div class="text-2xl font-serif font-black text-luxury-forest group-hover:text-green-600 transition-colors leading-none">{{ $stats['makanan_saved'] }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Makanan Saved</div>
        </div>
    </div>

    <!-- Alert Verification -->
    @if(($stats['pending'] ?? 0) > 0)
    <div class="bg-orange-50 border border-orange-100 rounded-[2rem] p-6 flex flex-col sm:flex-row items-center justify-between gap-6 mb-10 shadow-sm animate-in fade-in duration-300">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center shrink-0 shadow-sm animate-pulse">
                <i data-lucide="alert-circle" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="font-serif text-lg font-bold text-orange-900 leading-tight">{{ $stats['pending'] }} Pendaftaran Menunggu Verifikasi</h3>
                <p class="text-xs text-orange-700 mt-1.5 leading-relaxed">Terdapat {{ $stats['pending'] }} berkas kemitraan dan lembaga sosial baru yang perlu diverifikasi keabsahan dokumennya sebelum diizinkan bertransaksi.</p>
            </div>
        </div>
        <a href="{{ route('admin.verification') }}" class="whitespace-nowrap bg-orange-600 text-white text-[10px] font-black uppercase tracking-widest px-6 py-4 rounded-xl hover:bg-orange-700 transition active:scale-95 shadow-lg shadow-orange-100 flex-shrink-0">
            Verifikasi Sekarang
        </a>
    </div>
    @endif

    <!-- Two Columns: Pending & Activities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        <!-- Pending Verification List -->
        <div class="glass-card rounded-[2.5rem] overflow-hidden flex flex-col reveal">
            <div class="p-8 flex items-center justify-between border-b border-luxury-alabas/60 bg-white/30">
                <div class="flex items-center gap-3">
                    <i data-lucide="shield" class="w-6 h-6 text-orange-500 animate-pulse"></i>
                    <h2 class="font-serif text-2xl font-bold text-luxury-forest">Pending Verifikasi</h2>
                </div>
                <a href="{{ route('admin.verification') }}" class="px-5 py-2.5 rounded-xl bg-white/80 border border-luxury-alabas/85 text-[10px] font-black uppercase tracking-widest text-luxury-forest hover:bg-luxury-forest hover:text-white transition-all duration-300">Lihat Semua</a>
            </div>
            <div class="p-8 space-y-4 flex-1 bg-white/10">
                <!-- Item 1 -->
                <div class="border border-luxury-alabas rounded-[1.5rem] p-6 flex justify-between items-center bg-white/40 hover:bg-white hover:shadow-md transition-all duration-500 group">
                    <div>
                        <h4 class="font-bold text-luxury-forest text-base mb-1 group-hover:text-luxury-gold transition-colors">Toko Roti Sejahtera</h4>
                        <p class="text-xs text-luxury-slate font-medium">Mitra • 3 dokumen legalitas</p>
                        <p class="text-[10px] text-gray-400 font-mono mt-2 uppercase tracking-wide">Diajukan: 2026-03-31 09:00</p>
                    </div>
                    <a href="{{ route('admin.verification') }}" class="bg-luxury-forest text-white text-[10px] font-black uppercase tracking-widest px-5 py-3 rounded-xl hover:bg-luxury-gold transition active:scale-95">Review</a>
                </div>
                <!-- Item 2 -->
                <div class="border border-luxury-alabas rounded-[1.5rem] p-6 flex justify-between items-center bg-white/40 hover:bg-white hover:shadow-md transition-all duration-500 group">
                    <div>
                        <h4 class="font-bold text-luxury-forest text-base mb-1 group-hover:text-luxury-gold transition-colors">Yayasan Harapan Bangsa</h4>
                        <p class="text-xs text-luxury-slate font-medium">Lembaga Sosial • 4 dokumen legalitas</p>
                        <p class="text-[10px] text-gray-400 font-mono mt-2 uppercase tracking-wide">Diajukan: 2026-03-31 08:30</p>
                    </div>
                    <a href="{{ route('admin.verification') }}" class="bg-luxury-forest text-white text-[10px] font-black uppercase tracking-widest px-5 py-3 rounded-xl hover:bg-luxury-gold transition active:scale-95">Review</a>
                </div>
                <!-- Item 3 -->
                <div class="border border-luxury-alabas rounded-[1.5rem] p-6 flex justify-between items-center bg-white/40 hover:bg-white hover:shadow-md transition-all duration-500 group">
                    <div>
                        <h4 class="font-bold text-luxury-forest text-base mb-1 group-hover:text-luxury-gold transition-colors">Healthy Cafe</h4>
                        <p class="text-xs text-luxury-slate font-medium">Mitra • 3 dokumen legalitas</p>
                        <p class="text-[10px] text-gray-400 font-mono mt-2 uppercase tracking-wide">Diajukan: 2026-03-30 16:45</p>
                    </div>
                    <a href="{{ route('admin.verification') }}" class="bg-luxury-forest text-white text-[10px] font-black uppercase tracking-widest px-5 py-3 rounded-xl hover:bg-luxury-gold transition active:scale-95">Review</a>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="glass-card rounded-[2.5rem] overflow-hidden flex flex-col reveal">
            <div class="p-8 flex items-center gap-3 border-b border-luxury-alabas/60 bg-white/30">
                <i data-lucide="trending-up" class="w-6 h-6 text-blue-500 animate-pulse"></i>
                <h2 class="font-serif text-2xl font-bold text-luxury-forest">Aktivitas Terbaru</h2>
            </div>
            <div class="p-8 space-y-4 flex-1 bg-white/10 max-h-[460px] overflow-y-auto custom-scrollbar">
                @foreach($activities as $activity)
                    @php
                        $iconColor = match($activity['type']) {
                            'success' => 'text-green-600 bg-green-50/80 border-green-100',
                            'warning' => 'text-orange-605 bg-orange-50/80 border-orange-100',
                            'danger' => 'text-red-600 bg-red-50/80 border-red-100',
                            default => 'text-blue-600 bg-blue-50/80 border-blue-100',
                        };
                    @endphp
                    <div class="flex items-start gap-4 bg-white/40 border border-luxury-alabas rounded-[1.5rem] p-5 hover:bg-white transition-all duration-300">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 border {{ $iconColor }}">
                            <i data-lucide="{{ $activity['icon'] }}" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-luxury-forest text-sm">{{ $activity['title'] }}</h4>
                            <p class="text-xs text-luxury-slate font-medium mt-1 leading-normal">{{ $activity['description'] }}</p>
                            <p class="text-[9px] text-gray-400 font-mono mt-2 uppercase tracking-wider">{{ $activity['time'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Dampak Platform -->
    <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-[#0f361d] to-[#175330] p-10 text-white border border-white/10 shadow-2xl reveal mb-12">
        <!-- Glowing Blobs -->
        <div class="absolute top-[-30%] left-[-15%] w-[30rem] h-[30rem] bg-emerald-400/20 rounded-full blur-[90px] pointer-events-none"></div>
        <div class="absolute bottom-[-30%] right-[-15%] w-[32rem] h-[32rem] bg-lime-400/15 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-10">
                <i data-lucide="leaf" class="w-7 h-7 text-emerald-400 animate-pulse"></i>
                <h2 class="text-2xl font-serif font-bold text-white">Dampak Sosial & Penyelamatan Lingkungan</h2>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center md:border-r border-white/10 last:border-0 pr-4 animate-in fade-in duration-500">
                    <div class="text-4xl md:text-5xl font-serif font-black text-emerald-400 leading-none">{{ $stats['makanan_saved'] }} kg</div>
                    <div class="text-[10px] text-emerald-200/70 font-black uppercase tracking-[0.2em] mt-4">Makanan Diselamatkan</div>
                </div>
                <div class="text-center md:border-r border-white/10 last:border-0 pr-4 animate-in fade-in duration-500">
                    <div class="text-4xl md:text-5xl font-serif font-black text-emerald-400 leading-none">{{ $stats['co2_dikurangi'] }} kg</div>
                    <div class="text-[10px] text-emerald-200/70 font-black uppercase tracking-[0.2em] mt-4">CO₂ Dikurangi</div>
                </div>
                <div class="text-center md:border-r border-white/10 last:border-0 pr-4 animate-in fade-in duration-500">
                    <div class="text-4xl md:text-5xl font-serif font-black text-emerald-400 leading-none">{{ $stats['transaksi'] }}</div>
                    <div class="text-[10px] text-emerald-200/70 font-black uppercase tracking-[0.2em] mt-4">Total Transaksi</div>
                </div>
                <div class="text-center animate-in fade-in duration-500">
                    <div class="text-4xl md:text-5xl font-serif font-black text-emerald-400 leading-none">{{ $stats['gmv_platform'] }}</div>
                    <div class="text-[10px] text-emerald-200/70 font-black uppercase tracking-[0.2em] mt-4">GMV Platform</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aksi Cepat -->
    <div class="glass-card rounded-[2.5rem] p-8 shadow-sm reveal mb-10">
        <h2 class="font-serif text-2xl font-bold text-luxury-forest mb-6">Aksi Cepat Admin</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.verification') }}" class="flex flex-col items-center justify-center gap-3 border border-luxury-alabas/80 bg-white/40 rounded-2xl py-6 px-4 hover:bg-white hover:border-[#174413] hover:shadow-md transition-all duration-300 group text-center active:scale-95">
                <div class="w-12 h-12 bg-orange-50 text-orange-600 border border-orange-100 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:bg-orange-100 transition-all duration-300">
                    <i data-lucide="shield" class="w-5 h-5"></i>
                </div>
                <span class="font-bold text-xs text-luxury-forest uppercase tracking-wider mt-1">Verifikasi Akun</span>
            </a>
            <a href="{{ route('admin.users') }}" class="flex flex-col items-center justify-center gap-3 border border-luxury-alabas/80 bg-white/40 rounded-2xl py-6 px-4 hover:bg-white hover:border-[#174413] hover:shadow-md transition-all duration-300 group text-center active:scale-95">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 border border-blue-100 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:bg-blue-100 transition-all duration-300">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <span class="font-bold text-xs text-luxury-forest uppercase tracking-wider mt-1">Kelola User</span>
            </a>
            <a href="{{ route('admin.reports') }}" class="flex flex-col items-center justify-center gap-3 border border-luxury-alabas/80 bg-white/40 rounded-2xl py-6 px-4 hover:bg-white hover:border-[#174413] hover:shadow-md transition-all duration-300 group text-center active:scale-95">
                <div class="w-12 h-12 bg-green-50 text-green-600 border border-green-100 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:bg-green-100 transition-all duration-300">
                    <i data-lucide="line-chart" class="w-5 h-5"></i>
                </div>
                <span class="font-bold text-xs text-luxury-forest uppercase tracking-wider mt-1">Lihat Laporan</span>
            </a>
            <a href="{{ route('admin.problem-reports.index') }}" class="flex flex-col items-center justify-center gap-3 border border-luxury-alabas/80 bg-white/40 rounded-2xl py-6 px-4 hover:bg-white hover:border-[#174413] hover:shadow-md transition-all duration-300 group text-center active:scale-95">
                <div class="w-12 h-12 bg-red-50 text-red-600 border border-red-100 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:bg-red-100 transition-all duration-300">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <span class="font-bold text-xs text-luxury-forest uppercase tracking-wider mt-1">Moderasi Konten</span>
            </a>
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
