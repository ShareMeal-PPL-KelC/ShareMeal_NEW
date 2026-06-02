@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Greeting Hero Banner -->
    <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-[#1b1e4b] to-[#2c3175] p-8 md:p-12 text-white border border-white/10 shadow-2xl reveal mb-10">
        <!-- Internal Glowing Blobs -->
        <div class="absolute top-[-30%] left-[-15%] w-[30rem] h-[30rem] bg-indigo-400/20 rounded-full blur-[90px] pointer-events-none"></div>
        <div class="absolute bottom-[-30%] right-[-15%] w-[32rem] h-[32rem] bg-purple-400/15 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
            <div class="md:w-2/3">
                <span class="bg-white/10 text-indigo-300 border border-white/10 backdrop-blur px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-widest mb-6 inline-block">
                    🤝 Aliansi Kemanusiaan
                </span>
                <h1 class="text-4xl md:text-5xl font-black mb-4 leading-tight font-serif text-white">
                    Dashboard Lembaga Sosial
                </h1>
                <p class="text-indigo-100 text-base md:text-lg max-w-xl font-medium opacity-90 leading-relaxed">
                    Kelola penerimaan donasi makanan surplus berkualitas dengan mudah, cepat, dan transparan untuk mereka yang membutuhkan.
                </p>
            </div>
            
            <div class="flex-shrink-0">
                @if($userObj && $userObj->is_verified)
                    <div class="glass-panel px-6 py-4 rounded-3xl border border-white/20 backdrop-blur-md flex items-center gap-3 bg-white/5">
                        <div class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-widest text-green-300">Status Akun</div>
                            <div class="font-bold text-white text-sm">Lembaga Terverifikasi</div>
                        </div>
                    </div>
                @elseif($userObj && !$userObj->is_verified && $userObj->verification_rejection_reason)
                    <div class="glass-panel px-6 py-4 rounded-3xl border border-red-500/30 bg-red-950/20 backdrop-blur-md flex items-center gap-3">
                        <div class="w-10 h-10 bg-red-500 text-white rounded-full flex items-center justify-center animate-pulse">
                            <i data-lucide="shield-alert" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-widest text-red-300">Status Akun</div>
                            <div class="font-bold text-white text-sm">Verifikasi Ditolak</div>
                        </div>
                    </div>
                @else
                    <div class="glass-panel px-6 py-4 rounded-3xl border border-amber-500/30 bg-amber-950/20 backdrop-blur-md flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-500 text-white rounded-full flex items-center justify-center animate-pulse">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-widest text-amber-300">Status Akun</div>
                            <div class="font-bold text-white text-sm">Sedang Ditinjau</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Background Icon Deco -->
        <div class="absolute -right-16 -bottom-16 opacity-10">
            <i data-lucide="heart-handshake" class="w-80 h-80 text-white"></i>
        </div>
    </div>

    @if($userObj && !$userObj->is_verified && $userObj->verification_rejection_reason)
        <!-- Rejection Notice -->
        <div class="bg-red-50 border-2 border-red-200 rounded-[2.5rem] p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mb-10 animate-in fade-in duration-500" x-data="{ showUpload: false }">
            <div class="flex items-start gap-4">
                <div class="h-14 w-14 bg-red-100 text-red-650 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <i data-lucide="shield-alert" class="w-7 h-7"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-red-950 leading-tight">Pengajuan Verifikasi Lembaga Ditolak</h3>
                    <p class="text-red-800 text-sm mt-2 leading-relaxed">
                        <strong>Alasan Penolakan:</strong> <span class="font-semibold">{{ $userObj->verification_rejection_reason }}</span>
                    </p>
                    <p class="text-red-600 text-xs mt-2 italic font-medium">Mohon unggah kembali dokumen legalitas organisasi Anda agar dapat segera mulai mengklaim donasi makanan surplus.</p>
                </div>
            </div>
            <button @click="showUpload = true" class="bg-red-600 text-white px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-wider hover:bg-red-700 transition active:scale-95 shadow-xl shadow-red-100 flex-shrink-0">
                Lengkapi Dokumen Sekarang
            </button>

            <!-- Re-upload Form Modal -->
            <div x-show="showUpload" 
                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
                 x-cloak
                 @keydown.escape.window="showUpload = false">
                
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-[#2c1313]/60 backdrop-blur-md" @click="showUpload = false"></div>

                <!-- Modal Content -->
                <div class="relative bg-white rounded-[3rem] w-full max-w-xl p-10 shadow-2xl border border-red-100 overflow-y-auto max-h-[90vh]">
                    <div class="flex justify-between items-center mb-8 border-b border-gray-100 pb-6">
                        <div>
                            <h3 class="text-2xl font-serif font-bold text-gray-900">Re-upload Dokumen Lembaga</h3>
                            <p class="text-[10px] text-red-600 font-black uppercase tracking-widest mt-1">Lengkapi berkas verifikasi resmi</p>
                        </div>
                        <button @click="showUpload = false" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:bg-gray-100 transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('lembaga.upload.document') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @foreach([
                            'document_ktp' => ['label' => 'Dokumen Legalitas Dasar', 'desc' => 'Akta Pendirian, SK Menkumham, atau Akta Yayasan Resmi'],
                            'document_siup' => ['label' => 'Dokumen Izin Operasional & Sosial', 'desc' => 'Surat Izin Operasional LKS, Tanda Daftar Yayasan Dinas Sosial'],
                            'document_nib' => ['label' => 'Dokumen Identitas & Bukti Fisik', 'desc' => 'KTP Pengurus Aktif, Domisili Lembaga, dan Foto Tampak Depan Kantor']
                        ] as $name => $info)
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $info['label'] }}</label>
                                <p class="text-[11px] text-gray-500 leading-normal">{{ $info['desc'] }}</p>
                                
                                <div class="relative border-2 border-dashed border-gray-200 rounded-[1.2rem] p-4 text-center hover:border-red-500 transition-colors bg-gray-50/50 group overflow-hidden">
                                    <input type="file" name="{{ $name }}" required
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                           @change="
                                               const file = $event.target.files[0];
                                               $el.closest('.group').querySelector('.file-name-text').textContent = file ? file.name : '';
                                           ">
                                    <div class="flex items-center justify-center gap-3">
                                        <i data-lucide="upload-cloud" class="w-5 h-5 text-gray-400 group-hover:text-red-500 transition-colors"></i>
                                        <span class="text-xs font-bold text-gray-700 file-name-text">Pilih Berkas Dokumen (PDF, JPG, atau PNG)</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        <div class="pt-6 border-t border-gray-100 flex justify-end gap-4 mt-8">
                            <button type="button" @click="showUpload = false" class="py-4 px-6 text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 hover:text-gray-900 transition-colors">Batal</button>
                            <button type="submit" class="bg-red-650 text-white py-4 px-8 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] shadow-xl hover:bg-red-700 transition active:scale-95">Kirim Dokumen Baru</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @elseif($userObj && !$userObj->is_verified)
        <!-- Pending Info -->
        <div class="bg-blue-50 border border-blue-100 rounded-[2.5rem] p-8 flex flex-col md:flex-row items-center justify-between gap-6 mb-10 animate-in fade-in duration-500">
            <div class="flex items-center gap-4 text-center md:text-left">
                <div class="h-14 w-14 bg-blue-100 text-blue-650 rounded-2xl flex items-center justify-center flex-shrink-0 animate-pulse">
                    <i data-lucide="clock" class="w-7 h-7"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-blue-950 leading-tight">Akun Sedang Diverifikasi Admin</h3>
                    <p class="text-blue-800 text-sm mt-1">Tim kami sedang memeriksa dokumen organisasi Anda secara cermat. Mohon pantau halaman ini untuk mengetahui pembaruan status resmi.</p>
                </div>
            </div>
        </div>
    @else
        <!-- Verification Status Verified -->
        <div class="bg-green-50/50 border border-green-200/60 rounded-[2.5rem] p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mb-10 animate-in fade-in duration-500">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 bg-green-600 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-md">
                    <i data-lucide="check-circle" class="w-7 h-7 text-white"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-green-950 leading-tight">Lembaga Mitra Terverifikasi Resmi</h3>
                    <p class="text-sm text-green-800 mt-1">Status legalitas dan dokumen resmi organisasi Anda telah disetujui sepenuhnya oleh tim ShareMeal.</p>
                    <div class="flex flex-wrap gap-3 mt-4">
                        <span class="bg-white/80 border border-green-200 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider text-green-700 flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                            Akta Pendirian Valid
                        </span>
                        <span class="bg-white/80 border border-green-200 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider text-green-700 flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                            SK Kemenkumham Valid
                        </span>
                        <span class="bg-white/80 border border-green-200 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider text-green-700 flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                            NPWP Resmi Terverifikasi
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-16">
        <div class="glass-card glass-card-hover p-8 rounded-[2.5rem] group transition-all duration-500 reveal">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 bg-purple-50 text-purple-650 border border-purple-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-purple-100 transition-all duration-300">
                    <i data-lucide="package" class="w-7 h-7"></i>
                </div>
                <div class="text-3xl md:text-4xl font-serif font-black text-luxury-forest group-hover:text-purple-600 transition-colors leading-none">{{ $stats->totalDonations }}</div>
                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-[0.2em] text-center mt-4 group-hover:text-luxury-forest transition-colors">Total Donasi</div>
            </div>
        </div>
        <div class="glass-card glass-card-hover p-8 rounded-[2.5rem] group transition-all duration-500 reveal">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 bg-orange-50 text-orange-600 border border-orange-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-orange-100 transition-all duration-300">
                    <i data-lucide="truck" class="w-7 h-7"></i>
                </div>
                <div class="text-3xl md:text-4xl font-serif font-black text-luxury-forest group-hover:text-orange-600 transition-colors leading-none">{{ $stats->activeDonations }}</div>
                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-[0.2em] text-center mt-4 group-hover:text-luxury-forest transition-colors">Aktif Diproses</div>
            </div>
        </div>
        <div class="glass-card glass-card-hover p-8 rounded-[2.5rem] group transition-all duration-500 reveal">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 border border-blue-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-blue-100 transition-all duration-300">
                    <i data-lucide="smile" class="w-7 h-7"></i>
                </div>
                <div class="text-3xl md:text-4xl font-serif font-black text-luxury-forest group-hover:text-blue-600 transition-colors leading-none">{{ $stats->beneficiaries }}</div>
                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-[0.2em] text-center mt-4 group-hover:text-luxury-forest transition-colors">Penerima Manfaat</div>
            </div>
        </div>
        <div class="glass-card glass-card-hover p-8 rounded-[2.5rem] group transition-all duration-500 reveal">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 bg-green-50 text-green-600 border border-green-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-green-100 transition-all duration-300">
                    <i data-lucide="calendar" class="w-7 h-7"></i>
                </div>
                <div class="text-3xl md:text-4xl font-serif font-black text-luxury-forest group-hover:text-green-600 transition-colors leading-none">{{ $stats->thisMonth }}</div>
                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-[0.2em] text-center mt-4 group-hover:text-luxury-forest transition-colors">Bulan Ini</div>
            </div>
        </div>
    </div>

    <!-- Available Donations -->
    <div class="glass-card rounded-[3rem] overflow-hidden mb-16 reveal">
        <div class="p-10 border-b border-luxury-alabas/60 flex items-center justify-between bg-white/30">
            <div class="flex items-center gap-3">
                <i data-lucide="package" class="w-6 h-6 text-purple-600"></i>
                <h2 class="text-2xl font-serif font-bold text-luxury-forest">Donasi Tersedia ({{ count($availableDonations) }})</h2>
            </div>
            <a href="{{ route('lembaga.donations') }}" class="px-6 py-3 rounded-2xl bg-white/80 border border-luxury-alabas/85 text-[10px] font-black uppercase tracking-[0.2em] text-luxury-forest hover:bg-luxury-forest hover:text-white transition-all duration-500 luxury-shadow">
                Lihat Semua
            </a>
        </div>
        <div class="p-10 space-y-6 bg-white/10">
            <div class="bg-purple-50/70 border border-purple-100 rounded-2xl p-5 flex items-start gap-4 mb-4">
                <i data-lucide="alert-circle" class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0"></i>
                <div class="text-sm text-purple-900 leading-relaxed">
                    <strong>Sistem First-Come, First-Served:</strong> Klaim donasi terbuka untuk lembaga terverifikasi resmi dengan prinsip siapa cepat dia dapat. Pastikan armada penjemputan Anda siap sebelum melakukan klaim.
                </div>
            </div>

            <div class="space-y-4">
                @forelse($availableDonations as $d)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-6 bg-white/40 border border-luxury-alabas rounded-[2rem] gap-6 hover:bg-white/80 hover:shadow-md transition-all duration-500 group animate-in fade-in duration-500">
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <h4 class="font-serif text-2xl font-bold text-luxury-forest group-hover:text-luxury-gold transition-colors">{{ $d['store']['name'] }}</h4>
                            <span class="inline-flex items-center gap-1 rounded-full bg-green-50 border border-green-200 px-3 py-1 text-[10px] font-black text-green-700 uppercase tracking-wider">
                                <i data-lucide="check-circle" class="w-3 h-3"></i> Tersedia
                            </span>
                        </div>
                        <p class="text-sm text-gray-700 mt-2 font-medium">
                            {{ collect($d['items'])->map(fn($i) => $i['name'])->join(', ') }} 
                            <span class="text-luxury-slate/60 text-xs">({{ collect($d['items'])->map(fn($i) => $i['quantity'] . ' ' . ($i['unit'] ?? 'unit'))->join(', ') }})</span>
                        </p>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-4 text-[10px] font-bold text-luxury-slate tracking-wider uppercase">
                            <span class="flex items-center gap-1">📍 {{ $d['store']['address'] }}</span>
                            <span>• {{ $d['distance'] }}</span>
                            <span class="text-orange-650 flex items-center gap-1.5 font-black">
                                <i data-lucide="clock" class="w-3.5 h-3.5 animate-pulse"></i> Sampai {{ $d['available_until'] }}
                            </span>
                        </div>
                    </div>
                    
                    @if($userObj && $userObj->is_verified)
                        <a href="{{ route('lembaga.donations', ['tab' => 'available']) }}" class="bg-purple-600 text-white py-4 px-6 rounded-[1.2rem] font-black uppercase tracking-[0.2em] text-[10px] shadow-xl hover:bg-purple-750 hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-2 text-center">
                            <i data-lucide="heart" class="w-4 h-4 text-white animate-pulse"></i> Klaim Donasi
                        </a>
                    @else
                        <button class="bg-gray-150 text-gray-400 border border-gray-200 py-4 px-6 rounded-[1.2rem] font-black uppercase tracking-[0.2em] text-[10px] cursor-not-allowed flex items-center justify-center gap-2 animate-pulse" title="Akun Anda belum terverifikasi">
                            <i data-lucide="lock" class="w-4 h-4"></i> Klaim Donasi
                        </button>
                    @endif
                </div>
                @empty
                <div class="text-center py-16 bg-white/20 rounded-[2rem] border border-dashed border-gray-200">
                     <i data-lucide="package-open" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                     <p class="text-gray-500 font-serif italic text-lg">Tidak ada donasi tersedia saat ini.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent History -->
    <div class="glass-card rounded-[3rem] overflow-hidden mb-16 reveal">
        <div class="p-10 border-b border-luxury-alabas/60 flex items-center justify-between bg-white/30">
            <div class="flex items-center gap-3">
                <i data-lucide="history" class="w-6 h-6 text-indigo-600"></i>
                <h2 class="text-2xl font-serif font-bold text-luxury-forest">Riwayat Penerimaan Donasi</h2>
            </div>
            <a href="{{ route('lembaga.donations', ['tab' => 'completed']) }}" class="px-6 py-3 rounded-2xl bg-white/80 border border-luxury-alabas/85 text-[10px] font-black uppercase tracking-[0.2em] text-luxury-forest hover:bg-luxury-forest hover:text-white transition-all duration-500 luxury-shadow">
                Lihat Semua
            </a>
        </div>
        <div class="p-10 space-y-4 bg-white/10">
            @forelse($recentDonations as $rd)
            <div class="flex flex-col md:flex-row md:items-center justify-between p-6 bg-white/40 border border-luxury-alabas rounded-[2rem] gap-6 hover:bg-white/80 transition-all duration-500 group animate-in fade-in duration-500">
                <div class="flex-1">
                    <div class="font-serif text-xl font-bold text-luxury-forest group-hover:text-luxury-gold transition-colors">{{ $rd['store']['name'] }}</div>
                    <div class="text-sm text-gray-700 font-medium mt-1">
                        {{ collect($rd['items'])->map(fn($i) => $i['name'])->join(', ') }}
                    </div>
                    <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-2">{{ $rd['claimed_at'] ?? now()->toDateString() }}</div>
                </div>
                <div>
                    <span class="bg-green-50 text-green-700 px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest border border-green-200 flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> 
                        {{ $rd['status'] === 'completed' ? 'Diterima' : 'Diproses' }}
                    </span>
                </div>
            </div>
            @empty
            <div class="text-center py-16 bg-white/20 rounded-[2rem] border border-dashed border-gray-200">
                 <i data-lucide="history" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                 <p class="text-gray-500 font-serif italic text-lg">Belum ada riwayat donasi.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Impact Section -->
    <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-[#0f361d] to-[#175330] p-10 text-white border border-white/10 shadow-2xl reveal mb-10">
        <!-- Glowing Blobs -->
        <div class="absolute top-[-30%] left-[-15%] w-[30rem] h-[30rem] bg-emerald-400/20 rounded-full blur-[90px] pointer-events-none"></div>
        <div class="absolute bottom-[-30%] right-[-15%] w-[32rem] h-[32rem] bg-lime-400/15 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-10">
                <i data-lucide="leaf" class="w-7 h-7 text-emerald-400"></i>
                <h2 class="text-2xl font-serif font-bold text-white">Dampak Positif Bulan Ini</h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center md:border-r border-white/10 last:border-0 pr-4">
                    <div class="text-4xl md:text-5xl font-serif font-black text-emerald-400 leading-none">{{ $stats->totalDonations * 12 }}</div>
                    <div class="text-[10px] text-emerald-200/70 font-black uppercase tracking-[0.2em] mt-4">Porsi Tersalurkan</div>
                </div>
                <div class="text-center md:border-r border-white/10 last:border-0 pr-4">
                    <div class="text-4xl md:text-5xl font-serif font-black text-emerald-400 leading-none">{{ $stats->totalDonations * 3.5 }} kg</div>
                    <div class="text-[10px] text-emerald-200/70 font-black uppercase tracking-[0.2em] mt-4">CO₂ Terselamatkan</div>
                </div>
                <div class="text-center md:border-r border-white/10 last:border-0 pr-4">
                    <div class="text-4xl md:text-5xl font-serif font-black text-emerald-400 leading-none">{{ $stats->beneficiaries }}</div>
                    <div class="text-[10px] text-emerald-200/70 font-black uppercase tracking-[0.2em] mt-4">Penerima Manfaat</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-serif font-black text-emerald-400 leading-none">Rp {{ number_format($stats->totalDonations * 25000 / 1000, 0) }}k</div>
                    <div class="text-[10px] text-emerald-200/70 font-black uppercase tracking-[0.2em] mt-4">Nilai Dampak Sosial</div>
                </div>
            </div>
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
