@extends('layouts.dashboard')

@section('content')
@php
    $mitraUser = Auth::user() ?? \App\Models\User::find(session('sharemeal.current_user_id'));
@endphp

<div class="space-y-6">
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-750 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm animate-in fade-in duration-355 mb-6">
        <i data-lucide="check-circle" class="w-5 h-5"></i>
        <span class="font-bold text-sm">{{ session('success') }}</span>
    </div>
    @endif
    
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-750 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm animate-in fade-in duration-355 mb-6">
        <i data-lucide="alert-circle" class="w-5 h-5"></i>
        <span class="font-bold text-sm">{{ session('error') }}</span>
    </div>
    @endif

    @if($mitraUser && !$mitraUser->is_verified && $mitraUser->verification_rejection_reason)
        <!-- Rejection Notice -->
        <div class="bg-red-50 border-2 border-red-200 rounded-[2.5rem] p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mb-10 animate-in fade-in duration-500 text-left" x-data="{ showUpload: false, errors: { document_ktp: '', document_siup: '', document_nib: '', document_halal: '' } }">
            <div class="flex items-start gap-4">
                <div class="h-14 w-14 bg-red-100 text-red-650 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <i data-lucide="shield-alert" class="w-7 h-7"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-red-950 leading-tight">Pengajuan Verifikasi Toko Ditolak</h3>
                    <p class="text-red-800 text-sm mt-2 leading-relaxed text-left">
                        <strong>Alasan Penolakan:</strong> <span class="font-semibold text-left">{{ $mitraUser->verification_rejection_reason }}</span>
                    </p>
                    <p class="text-red-600 text-xs mt-2 italic font-medium text-left">Mohon lengkapi kembali berkas pendaftaran toko Anda agar dapat segera mulai mengaktifkan fitur penjualan makanan.</p>
                </div>
            </div>
            <button @click="showUpload = true" class="bg-red-600 text-white px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-wider hover:bg-red-700 transition active:scale-95 shadow-xl shadow-red-100 flex-shrink-0 cursor-pointer">
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
                    <div class="flex justify-between items-center mb-8 border-b border-gray-100 pb-6 text-left">
                        <div>
                            <h3 class="text-2xl font-serif font-bold text-gray-900">Re-upload Dokumen Usaha</h3>
                            <p class="text-[10px] text-red-600 font-black uppercase tracking-widest mt-1">Lengkapi berkas verifikasi resmi</p>
                        </div>
                        <button @click="showUpload = false" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:bg-gray-100 transition-colors cursor-pointer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('mitra.upload.document') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @foreach([
                            'document_ktp' => ['label' => 'KTP Pemilik Usaha', 'desc' => 'Foto/Scan Kartu Tanda Penduduk pemilik usaha aktif', 'required' => true],
                            'document_siup' => ['label' => 'SIUP / Izin Operasional', 'desc' => 'Dokumen Surat Izin Usaha Perdagangan atau Izin Operasional Usaha Kuliner', 'required' => true],
                            'document_nib' => ['label' => 'NIB (Nomor Induk Berusaha)', 'desc' => 'Foto/Scan NIB dari platform OSS', 'required' => true],
                            'document_halal' => ['label' => 'Sertifikat Halal', 'desc' => 'Foto/Scan Sertifikat Halal MUI (Opsional)', 'required' => false]
                        ] as $name => $info)
                            <div class="space-y-2 text-left">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest text-left">{{ $info['label'] }} {!! $info['required'] ? '<span class="text-red-500">*</span>' : '' !!}</label>
                                <p class="text-[11px] text-gray-500 leading-normal text-left">{{ $info['desc'] }} (Maks. 2 MB | JPG, PNG, PDF)</p>
                                
                                <div class="relative border-2 border-dashed border-gray-200 rounded-[1.2rem] p-4 text-center hover:border-red-500 transition-colors bg-gray-50/50 group overflow-hidden">
                                    <input type="file" name="{{ $name }}" {{ $info['required'] ? 'required' : '' }} accept=".jpg,.jpeg,.png,.pdf"
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                           @change="
                                               const file = $event.target.files[0];
                                               errors['{{ $name }}'] = '';
                                               if (file) {
                                                   const maxSize = 2 * 1024 * 1024;
                                                   const allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                                                   const ext = file.name.split('.').pop().toLowerCase();
                                                   
                                                   if (file.size > maxSize) {
                                                       errors['{{ $name }}'] = 'Ukuran berkas melebihi batas 2 MB. Silakan pilih berkas yang lebih kecil.';
                                                       $event.target.value = '';
                                                       $el.closest('.group').querySelector('.file-name-text').textContent = 'Pilih Berkas Dokumen (PDF, JPG, atau PNG)';
                                                       return;
                                                   }
                                                   if (!allowedExtensions.includes(ext)) {
                                                       errors['{{ $name }}'] = 'Format tidak valid. Hanya JPG, PNG, atau PDF yang diperbolehkan.';
                                                       $event.target.value = '';
                                                       $el.closest('.group').querySelector('.file-name-text').textContent = 'Pilih Berkas Dokumen (PDF, JPG, atau PNG)';
                                                       return;
                                                   }
                                                   $el.closest('.group').querySelector('.file-name-text').textContent = file.name;
                                               } else {
                                                   $el.closest('.group').querySelector('.file-name-text').textContent = 'Pilih Berkas Dokumen (PDF, JPG, atau PNG)';
                                               }
                                           ">
                                    <div class="flex items-center justify-center gap-3">
                                        <i data-lucide="upload-cloud" class="w-5 h-5 text-gray-400 group-hover:text-red-500 transition-colors"></i>
                                        <span class="text-xs font-bold text-gray-700 file-name-text">Pilih Berkas Dokumen (PDF, JPG, atau PNG)</span>
                                    </div>
                                </div>
                                <template x-if="errors['{{ $name }}']">
                                    <p class="text-[11px] font-bold text-red-600 mt-2 flex items-center gap-1.5 animate-in fade-in duration-300 text-left">
                                        <svg class="w-3.5 h-3.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <span x-text="errors['{{ $name }}']"></span>
                                    </p>
                                </template>
                            </div>
                        @endforeach
                        
                        <div class="pt-6 border-t border-gray-100 flex justify-end gap-4 mt-8">
                            <button type="button" @click="showUpload = false" class="py-4 px-6 text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 hover:text-gray-900 transition-colors cursor-pointer">Batal</button>
                            <button type="submit" class="bg-red-600 text-white py-4 px-8 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] shadow-xl hover:bg-red-700 transition active:scale-95 cursor-pointer">Kirim Dokumen Baru</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @elseif($mitraUser && !$mitraUser->is_verified)
        <!-- Pending Info -->
        <div class="bg-blue-50 border border-blue-100 rounded-[2.5rem] p-8 flex flex-col md:flex-row items-center justify-between gap-6 mb-10 animate-in fade-in duration-500 text-left">
            <div class="flex items-center gap-4 text-center md:text-left">
                <div class="h-14 w-14 bg-blue-100 text-blue-650 rounded-2xl flex items-center justify-center flex-shrink-0 animate-pulse">
                    <i data-lucide="clock" class="w-7 h-7 text-blue-600"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-blue-950 leading-tight">Akun Sedang Diverifikasi Admin</h3>
                    <p class="text-blue-800 text-sm mt-1 text-left">Tim kami sedang memeriksa dokumen usaha Anda secara cermat. Mohon pantau halaman ini untuk mengetahui pembaruan status resmi.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Welcome Greeting Hero Banner -->
    <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-[#0c2f1e] to-[#1e523c] p-8 md:p-12 text-white border border-white/10 shadow-2xl reveal mb-10">
        <!-- Internal Glowing Blobs -->
        <div class="absolute top-[-30%] left-[-15%] w-[30rem] h-[30rem] bg-emerald-400/20 rounded-full blur-[90px] pointer-events-none"></div>
        <div class="absolute bottom-[-30%] right-[-15%] w-[32rem] h-[32rem] bg-teal-400/15 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
            <div class="md:w-2/3 text-left">
                <span class="bg-white/10 text-emerald-300 border border-white/10 backdrop-blur px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-widest mb-6 inline-block">
                    🏪 Kemitraan ShareMeal
                </span>
                <h1 class="text-4xl md:text-5xl font-black mb-4 leading-tight font-serif text-white">
                    Dashboard Mitra Kuliner
                </h1>
                <p class="text-emerald-100 text-base md:text-lg max-w-xl font-medium opacity-90 leading-relaxed text-left">
                    Kelola penjualan produk surplus Anda secara efisien, pantau inventaris aktif, dan salurkan donasi untuk mengurangi food waste demi kelestarian lingkungan.
                </p>
            </div>
            
            <div class="flex-shrink-0">
                @if($mitraUser && $mitraUser->is_verified)
                    <div class="glass-panel px-6 py-4 rounded-3xl border border-white/20 backdrop-blur-md flex items-center gap-3 bg-white/5">
                        <div class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-widest text-green-300">Status Akun</div>
                            <div class="font-bold text-white text-sm">Mitra Terverifikasi</div>
                        </div>
                    </div>
                @elseif($mitraUser && !$mitraUser->is_verified && $mitraUser->verification_rejection_reason)
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
                    <div class="glass-panel px-6 py-4 rounded-3xl border border-blue-500/30 bg-blue-950/20 backdrop-blur-md flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center animate-pulse">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-widest text-blue-300">Status Akun</div>
                            <div class="font-bold text-white text-sm">Menunggu Verifikasi</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Background Icon Deco -->
        <div class="absolute -right-16 -bottom-16 opacity-10 pointer-events-none">
            <i data-lucide="store" class="w-80 h-80 text-white"></i>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
        <div class="bg-white p-8 rounded-[2rem] luxury-shadow border border-luxury-alabas hover:bg-luxury-forest transition-all duration-500 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-luxury-ivory rounded-xl flex items-center justify-center group-hover:bg-white/10">
                    <i data-lucide="package" class="w-6 h-6 text-luxury-gold"></i>
                </div>
                <div class="text-[10px] font-black text-luxury-gold uppercase tracking-widest">Stok Aktif</div>
            </div>
            <div class="text-4xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">{{ $stats->totalProducts }} Produk</div>
            <p class="text-[10px] text-luxury-slate group-hover:text-white/60 mt-3 font-bold uppercase tracking-wider italic">Dalam inventaris aktif</p>
        </div>

        <div class="bg-white p-8 rounded-[2rem] luxury-shadow border border-luxury-alabas hover:bg-luxury-forest transition-all duration-500 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-luxury-ivory rounded-xl flex items-center justify-center group-hover:bg-white/10">
                    <i data-lucide="dollar-sign" class="w-6 h-6 text-luxury-gold"></i>
                </div>
                <div class="text-[10px] font-black text-luxury-gold uppercase tracking-widest">Pendapatan</div>
            </div>
            <div class="text-3xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">Rp {{ number_format($stats->totalRevenue / 1000, 0) }}rb</div>
            <p class="text-[10px] text-luxury-emerald group-hover:text-white mt-3 font-black uppercase tracking-wider bg-luxury-emerald/10 group-hover:bg-white/10 px-3 py-1 rounded-full inline-block">
                +12.5% bln/bln
            </p>
        </div>

        <div class="bg-white p-8 rounded-[2rem] luxury-shadow border border-luxury-alabas hover:bg-luxury-forest transition-all duration-500 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-luxury-ivory rounded-xl flex items-center justify-center group-hover:bg-white/10">
                    <i data-lucide="star" class="w-6 h-6 text-luxury-gold"></i>
                </div>
                <div class="text-[10px] font-black text-luxury-gold uppercase tracking-widest">Apresiasi</div>
            </div>
            <div class="text-4xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">{{ $stats->averageRating }} <span class="text-sm opacity-40">/ 5.0</span></div>
            <p class="text-[10px] text-luxury-slate group-hover:text-white/60 mt-3 font-bold uppercase tracking-wider italic">Dari {{ $stats->totalReviews }} ulasan</p>
        </div>

        <div class="bg-white p-8 rounded-[2rem] luxury-shadow border border-luxury-alabas hover:bg-luxury-forest transition-all duration-500 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-luxury-ivory rounded-xl flex items-center justify-center group-hover:bg-white/10">
                    <i data-lucide="leaf" class="w-6 h-6 text-luxury-gold"></i>
                </div>
                <div class="text-[10px] font-black text-luxury-gold uppercase tracking-widest">Dampak Sosial</div>
            </div>
            <div class="text-4xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">{{ $stats->foodSaved }}kg</div>
            <p class="text-[10px] text-luxury-slate group-hover:text-white/60 mt-3 font-bold uppercase tracking-wider italic">Makanan terselamatkan</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-16">
        <!-- Expiring Items Alert -->
        <div class="bg-white rounded-[2.5rem] border border-luxury-alabas luxury-shadow overflow-hidden flex flex-col">
            <div class="p-8 border-b border-luxury-alabas flex items-center justify-between bg-luxury-ivory/30">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></div>
                    <h2 class="text-xl font-serif font-bold text-luxury-forest">Inventaris Mendesak</h2>
                </div>
                <a href="{{ route('mitra.inventory') }}" class="text-[10px] font-black uppercase tracking-[0.2em] text-luxury-gold hover:text-luxury-forest transition-colors">Kelola</a>
            </div>
            <div class="p-8 space-y-4 flex-1">
                @forelse($expiringItems as $item)
                <div class="flex items-center justify-between p-6 bg-luxury-ivory/50 rounded-2xl border border-luxury-alabas hover:bg-white hover:luxury-shadow transition-all duration-300 group">
                    <div>
                        <div class="font-bold text-luxury-forest group-hover:text-luxury-gold transition-colors">{{ $item->name }}</div>
                        <div class="text-[10px] text-luxury-slate font-black uppercase tracking-widest mt-1">Stok: {{ $item->stock }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-black text-orange-600 uppercase tracking-widest mb-1">{{ $item->expires_at->locale('id')->diffForHumans() }}</div>
                        <div class="w-16 h-1 bg-luxury-alabas rounded-full overflow-hidden">
                            <div class="h-full bg-orange-400 w-2/3"></div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <i data-lucide="check-circle" class="w-10 h-10 text-luxury-emerald/30 mx-auto mb-4"></i>
                    <p class="text-luxury-slate font-serif italic text-lg">Inventaris teroptimalkan dengan baik.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Reviews -->
        <div class="bg-white rounded-[2.5rem] border border-luxury-alabas luxury-shadow overflow-hidden flex flex-col">
            <div class="p-8 border-b border-luxury-alabas flex items-center justify-between bg-luxury-ivory/30">
                <div class="flex items-center gap-3">
                    <i data-lucide="star" class="w-5 h-5 text-luxury-gold"></i>
                    <h2 class="text-xl font-serif font-bold text-luxury-forest">Apresiasi Terbaru</h2>
                </div>
                <a href="{{ route('mitra.reviews') }}" class="text-[10px] font-black uppercase tracking-[0.2em] text-luxury-gold hover:text-luxury-forest transition-colors">Lihat Semua</a>
            </div>
            <div class="p-8 space-y-6 flex-1">
                @forelse($recentReviews as $review)
                <div class="p-6 bg-white border border-luxury-alabas rounded-2xl hover:luxury-shadow transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-sm font-bold text-luxury-forest">{{ $review->customer->name }}</div>
                        <div class="flex gap-1">
                            @for($i = 1; $i <= 5; $i++)
                            <i data-lucide="star" class="w-3 h-3 {{ $i <= $review->rating ? 'text-luxury-gold fill-luxury-gold' : 'text-luxury-alabas' }}"></i>
                            @endfor
                        </div>
                    </div>
                    @if($review->comment)
                    <p class="text-sm font-serif text-luxury-forest italic leading-relaxed opacity-80 line-clamp-2">&ldquo;{{ $review->comment }}&rdquo;</p>
                    @else
                    <p class="text-xs text-luxury-slate italic">Apresiasi tanpa komentar</p>
                    @endif
                    <div class="text-[9px] text-luxury-gold font-black uppercase tracking-widest mt-4">{{ $review->created_at->locale('id')->diffForHumans() }}</div>
                </div>
                @empty
                <div class="text-center py-12">
                    <i data-lucide="message-square" class="w-10 h-10 text-luxury-alabas/30 mx-auto mb-4"></i>
                    <p class="text-luxury-slate font-serif italic text-lg">Menunggu umpan balik.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Interactive Analytics Chart -->
    <div class="bg-white rounded-[2.5rem] border border-luxury-alabas luxury-shadow overflow-hidden mb-16" x-data="analyticsChart()">
        <div class="p-10 border-b border-luxury-alabas flex flex-col sm:flex-row sm:items-center justify-between gap-6 bg-luxury-ivory/20">
            <div>
                <h2 class="text-3xl font-serif font-bold text-luxury-forest">Analisis Kinerja Mitra</h2>
                <p class="text-[10px] text-luxury-gold font-black uppercase tracking-[0.2em] mt-1">Arahkan kursor ke titik grafik untuk menampilkan kartu detail interaktif di atas grafik</p>
            </div>
            
            <!-- Controls -->
            <div class="flex flex-wrap items-center gap-4">
                <!-- Metric Selector -->
                <div class="flex bg-luxury-ivory p-1.5 rounded-xl border border-luxury-alabas">
                    <button @click="setMetric('revenue')" :class="metric === 'revenue' ? 'bg-luxury-forest text-white shadow-md' : 'text-luxury-slate hover:text-luxury-forest'" class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg transition-all duration-300">
                        Pendapatan
                    </button>
                    <button @click="setMetric('impact')" :class="metric === 'impact' ? 'bg-luxury-forest text-white shadow-md' : 'text-luxury-slate hover:text-luxury-forest'" class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg transition-all duration-300">
                        Dampak Sosial
                    </button>
                </div>

                <!-- Time Range Selector -->
                <div class="flex bg-luxury-ivory p-1.5 rounded-xl border border-luxury-alabas">
                    <button @click="setTimeframe('weekly')" :class="timeframe === 'weekly' ? 'bg-luxury-gold text-luxury-forest shadow-md' : 'text-luxury-slate hover:text-luxury-forest'" class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg transition-all duration-300">
                        Mingguan
                    </button>
                    <button @click="setTimeframe('monthly')" :class="timeframe === 'monthly' ? 'bg-luxury-gold text-luxury-forest shadow-md' : 'text-luxury-slate hover:text-luxury-forest'" class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg transition-all duration-300">
                        Bulanan
                    </button>
                </div>
            </div>
        </div>
        
              <div class="p-10 bg-white/50 relative" @mouseleave="clearChartHighlight()">
            <div class="h-[350px] w-full relative">
                <canvas id="mitraPerformanceChart"></canvas>
                
                <!-- Interactive HTML Overlay floating over the chart point -->
                <div x-show="showOverlay" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                     class="absolute text-white p-4 rounded-2xl shadow-2xl border border-white/20 pointer-events-auto z-20 w-64 cursor-pointer hover:scale-105 active:scale-95 transition-all duration-300"
                     :class="metric === 'revenue' ? 'bg-[#174413]' : 'bg-[#c5a880] text-luxury-forest'"
                     :style="`left: ${overlayLeft}px; top: ${overlayTop}px; transform: translate(-50%, -115%);`"
                     @click="showModal = true"
                     x-cloak>
                     <div class="flex justify-between items-center mb-1.5 pb-1.5 border-b border-white/10">
                          <span class="text-[9px] font-black uppercase tracking-widest opacity-80" x-text="overlayTitle"></span>
                          <i data-lucide="arrow-right-circle" class="w-4 h-4"></i>
                      </div>
                      <div class="text-xl font-serif font-black" x-text="overlayValue"></div>
                      <div class="text-[10px] opacity-90 font-medium leading-relaxed mt-2 line-clamp-2" x-text="overlayDetail"></div>
                      <div class="text-[9px] font-black uppercase tracking-wider text-center mt-3 bg-white/20 py-1.5 rounded-lg">
                          Klik Untuk Detail
                      </div>
                </div>
            </div>

            <!-- Interactive Cards Grid -->
            <div class="grid grid-cols-4 sm:grid-cols-7 gap-4 mt-8 pt-6 border-t border-luxury-alabas/40">
                <template x-for="(hl, idx) in chartData[metric][timeframe].highlights" :key="idx">
                    <div @click="openHighlightModal(idx)" 
                         @mouseenter="highlightChartPoint(idx)"
                         @mouseleave="clearChartHighlight()"
                         class="cursor-pointer p-4 bg-luxury-ivory/30 hover:bg-luxury-forest hover:text-white border border-luxury-alabas/50 rounded-2xl transition-all duration-300 text-center group active:scale-95 flex flex-col justify-between hover:shadow-md h-full transform hover:-translate-y-1">
                        <div class="text-[9px] font-black uppercase tracking-wider text-luxury-gold group-hover:text-luxury-ivory" x-text="hl.label"></div>
                        <div class="text-sm font-serif font-black mt-2" x-text="hl.value"></div>
                        <div class="text-[8px] font-black uppercase tracking-widest text-luxury-slate group-hover:text-white/80 mt-1.5 opacity-0 group-hover:opacity-100 transition-opacity">Detail</div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Custom Modal for Chart Point Detail -->
    <div x-show="showModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
         x-cloak
         @keydown.escape.window="showModal = false">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-[#174413]/60 backdrop-blur-md" 
             @click="showModal = false"
             x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <!-- Modal Content -->
        <div x-show="showModal"
             x-transition:enter="ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-y-12 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-12 scale-95"
             class="relative bg-white rounded-[3rem] w-full max-w-lg p-10 shadow-2xl border border-luxury-alabas overflow-hidden transform transition-all">
            <div class="flex justify-between items-center mb-6 border-b border-luxury-alabas/40 pb-4">
                <div>
                    <h3 class="text-2xl font-serif font-bold text-luxury-forest">Analisis Capaian</h3>
                    <p class="text-[9px] text-luxury-gold font-black uppercase tracking-widest mt-1" x-text="overlayTitle"></p>
                </div>
                <button @click="showModal = false" class="w-10 h-10 flex items-center justify-center rounded-full bg-luxury-ivory text-luxury-forest hover:bg-luxury-forest hover:text-white transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <div class="space-y-6">
                <div class="bg-luxury-ivory p-6 rounded-2xl border border-luxury-alabas flex justify-between items-center">
                    <span class="text-xs font-black uppercase text-luxury-slate">Nilai Capaian</span>
                    <span class="text-3xl font-serif font-black text-luxury-forest" x-text="overlayValue"></span>
                </div>
                <p class="text-sm font-sans font-semibold text-luxury-forest leading-relaxed" x-text="overlayDetail"></p>
                
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-xs text-green-800 flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                    <span>Metrik kinerja optimal terdeteksi di platform ShareMeal.</span>
                </div>
            </div>
            
            <div class="mt-8 pt-6 border-t border-luxury-alabas/40 flex justify-end">
                <button @click="showModal = false" class="bg-luxury-forest text-white py-3 px-6 rounded-xl font-black uppercase tracking-wider text-[10px] shadow-md hover:bg-luxury-forest/90 active:scale-95 transition-all">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded-[3rem] border border-luxury-alabas luxury-shadow overflow-hidden mb-12">
        <div class="p-10 border-b border-luxury-alabas flex items-center justify-between bg-luxury-ivory/20">
            <div>
                <h2 class="text-3xl font-serif font-bold text-luxury-forest">Transaksi Terbaru</h2>
                <p class="text-[10px] text-luxury-gold font-black uppercase tracking-[0.2em] mt-1">Pantau pesanan terbaru dari komunitas Anda</p>
            </div>
            <a href="{{ route('mitra.orders') }}" class="px-8 py-4 rounded-2xl bg-white border border-luxury-alabas text-[10px] font-black uppercase tracking-[0.2em] text-luxury-forest hover:bg-luxury-forest hover:text-white transition-all duration-500 luxury-shadow">
                Log Lengkap
            </a>
        </div>
        <div class="divide-y divide-luxury-alabas">
            @forelse($recentOrders as $order)
            <div class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-8 hover:bg-luxury-ivory/30 transition-all duration-500 group">
                <div class="flex items-center gap-8 flex-1">
                    <div class="w-16 h-16 bg-luxury-forest/5 rounded-2xl flex items-center justify-center text-luxury-forest luxury-shadow border border-luxury-alabas transition-transform group-hover:scale-110">
                        <i data-lucide="shopping-bag" class="w-7 h-7 stroke-[1.5]"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-4">
                            <div class="font-serif text-2xl font-bold text-luxury-forest group-hover:text-luxury-gold transition-colors duration-500">{{ $order->customer->name }}</div>
                            <span class="font-mono text-[10px] text-luxury-slate opacity-50 uppercase tracking-tighter">#{{ $order->id }}</span>
                        </div>
                        <div class="text-sm text-luxury-slate font-medium mt-2 italic opacity-80">{{ $order->items_string }}</div>
                        <div class="text-[9px] text-luxury-gold font-black uppercase tracking-widest mt-3">{{ $order->created_at ? $order->created_at->locale('id')->diffForHumans() : '-' }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-10">
                    <div class="text-right">
                        <div class="text-2xl font-serif font-black text-luxury-forest">Rp {{ number_format($order->amount, 0, ',', '.') }}</div>
                        <div class="mt-2">
                            @php
                                $statusLabel = $order->status;
                                $isCompleted = $order->status === 'completed';
                                if ($order->status === 'ready') {
                                    $statusLabel = $order->receiving_method === 'delivery' ? 'Siap Diantar' : 'Siap Diambil';
                                } else {
                                    $statusLabel = [
                                        'pending' => 'Menunggu',
                                        'processing' => 'Diproses',
                                        'shipping' => 'Dikirim',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Batal'
                                    ][$order->status] ?? $order->status;
                                }
                            @endphp
                            <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-lg {{ $isCompleted ? 'bg-luxury-emerald/10 text-luxury-emerald' : 'bg-orange-50 text-orange-600' }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('mitra.orders') }}" class="w-14 h-14 bg-luxury-ivory rounded-2xl flex items-center justify-center text-luxury-forest hover:bg-luxury-forest hover:text-white transition-all duration-500 luxury-shadow group/btn active:scale-90">
                        <i data-lucide="chevron-right" class="w-6 h-6 transition-transform group-hover/btn:translate-x-1"></i>
                    </a>
                </div>
            </div>
            @empty
            <div class="p-20 text-center">
                <div class="w-20 h-20 bg-luxury-ivory rounded-full flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="inbox" class="w-8 h-8 text-luxury-alabas"></i>
                </div>
                <p class="text-luxury-slate font-serif text-xl italic">Menunggu transaksi pertama.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const initChart = () => {
        Alpine.data('analyticsChart', () => ({
            metric: 'revenue',
            timeframe: 'weekly',
            chartInstance: null,
            showOverlay: false,
            overlayTitle: '',
            overlayValue: '',
            overlayDetail: '',
            overlayLeft: 0,
            overlayTop: 0,
            showModal: false,

            // Simulated real-time performance data with narratives
            chartData: {
                revenue: {
                    weekly: {
                        labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                        data: [120000, 185000, 95000, 240000, 180000, 350000, 290000],
                        label: 'Pendapatan (Rp)',
                        color: '#174413', // Deep forest green
                        highlights: [
                            { label: 'Senin', value: 'Rp 120.000', detail: 'Awal pekan yang stabil dengan rata-rata belanja Rp 40.000 per transaksi.' },
                            { label: 'Selasa', value: 'Rp 185.000', detail: 'Peningkatan penjualan makanan surplus menu makan siang kantor.' },
                            { label: 'Rabu', value: 'Rp 95.000', detail: 'Penjualan terendah pekan ini karena stok surplus resto habis terjual lebih awal.' },
                            { label: 'Kamis', value: 'Rp 240.000', detail: 'Peningkatan permintaan sore hari untuk hidangan roti surplus & kue kering.' },
                            { label: 'Jumat', value: 'Rp 180.000', detail: 'Pembelian stabil dari pelanggan tetap menjelang akhir pekan.' },
                            { label: 'Sabtu', value: 'Rp 350.000', detail: 'Puncak penjualan tertinggi pekan ini! Menu sarapan & kopi surplus sangat diminati.' },
                            { label: 'Minggu', value: 'Rp 290.000', detail: 'Penjualan tinggi didominasi oleh komunitas olahraga pagi di sekitar outlet.' }
                        ]
                    },
                    monthly: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                        data: [1450000, 1850000, 1600000, 2900000, 2200000, 3850000],
                        label: 'Pendapatan (Rp)',
                        color: '#174413',
                        highlights: [
                            { label: 'Januari', value: 'Rp 1.450.000', detail: 'Pembukaan tahun baru dengan tingkat klaim makanan surplus yang stabil.' },
                            { label: 'Februari', value: 'Rp 1.850.000', detail: 'Kenaikan penjualan berkat promosi paket hemat penyelamat makanan.' },
                            { label: 'Maret', value: 'Rp 1.600.000', detail: 'Kinerja stabil dengan penyesuaian menu takjil surplus saat menjelang Ramadhan.' },
                            { label: 'April', value: 'Rp 2.900.000', detail: 'Lonjakan tertinggi! Banyak konsumen membeli paket porsi sahur & buka puasa surplus.' },
                            { label: 'Mei', value: 'Rp 2.200.000', detail: 'Kinerja pasca-Lebaran tetap produktif dan stabil didukung pelanggan loyal.' },
                            { label: 'Juni', value: 'Rp 3.850.000', detail: 'Rekor pendapatan bulanan baru didorong oleh kemitraan corporate gathering.' }
                        ]
                    }
                },
                impact: {
                    weekly: {
                        labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                        data: [15, 8, 19, 12, 25, 14, 30],
                        label: 'Makanan Terselamatkan (kg)',
                        color: '#c5a880', // Gold
                        highlights: [
                            { label: 'Senin', value: '15 kg', detail: 'Menyediakan sekitar 30 porsi makanan gratis bagi yayasan penerima manfaat.' },
                            { label: 'Selasa', value: '8 kg', detail: 'Menyelamatkan setara dengan 16 piring makanan segar siap konsumsi.' },
                            { label: 'Rabu', value: '19 kg', detail: 'Membantu menekan emisi gas metana sebanyak 38 kg CO2 ekivalen.' },
                            { label: 'Kamis', value: '12 kg', detail: 'Penyaluran donasi skala menengah sukses untuk Panti Asuhan lokal.' },
                            { label: 'Jumat', value: '25 kg', detail: 'Penyaluran berkah sedekah Jumat berupa nasi kotak bergizi seimbang.' },
                            { label: 'Sabtu', value: '14 kg', detail: 'Penyelamatan makanan berjalan lancar untuk kelompok pemukiman marginal.' },
                            { label: 'Minggu', value: '30 kg', detail: 'Puncak dampak sosial! 60 porsi makanan surplus didistribusikan habis.' }
                        ]
                    },
                    monthly: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                        data: [120, 95, 180, 140, 230, 190],
                        label: 'Makanan Terselamatkan (kg)',
                        color: '#c5a880',
                        highlights: [
                            { label: 'Januari', value: '120 kg', detail: 'Menyelamatkan makanan senilai total Rp 3.000.000 dari pembuangan sampah.' },
                            { label: 'Februari', value: '95 kg', detail: 'Mencegah pembuangan makanan setara dengan 190 porsi layak saji.' },
                            { label: 'Maret', value: '180 kg', detail: 'Membantu 5 panti jompo setempat mendapatkan suplai makanan surplus higienis.' },
                            { label: 'April', value: '140 kg', detail: 'Penyaluran donasi berjalan stabil untuk sahur & buka puasa.' },
                            { label: 'Mei', value: '230 kg', detail: 'Kolaborasi sukses dengan 3 aliansi lembaga sosial baru di sekitar restoran.' },
                            { label: 'Juni', value: '190 kg', detail: 'Pencapaian dampak pertengahan tahun yang luar biasa bagi ketahanan pangan lokal.' }
                        ]
                    }
                }
            },

            init() {
                this.$nextTick(() => {
                    const ctx = document.getElementById('mitraPerformanceChart').getContext('2d');
                    const config = this.getChartConfig();
                    this.chartInstance = new Chart(ctx, config);
                });
            },

            setMetric(metric) {
                this.metric = metric;
                this.showOverlay = false;
                this.updateChart();
            },

            setTimeframe(timeframe) {
                this.timeframe = timeframe;
                this.showOverlay = false;
                this.updateChart();
            },

            setActiveHighlight(index, x, y) {
                const current = this.chartData[this.metric][this.timeframe];
                if (current.highlights && current.highlights[index]) {
                    this.overlayTitle = current.highlights[index].label;
                    this.overlayValue = current.highlights[index].value;
                    this.overlayDetail = current.highlights[index].detail;
                    this.overlayLeft = x;
                    this.overlayTop = y;
                    this.showOverlay = true;
                    
                    // Trigger Lucide to render icons inside overlay if needed
                    setTimeout(() => {
                        if (window.lucide) window.lucide.createIcons();
                    }, 10);
                }
            },

            highlightChartPoint(index) {
                if (!this.chartInstance) return;
                const meta = this.chartInstance.getDatasetMeta(0);
                const point = meta.data[index];
                if (point) {
                    this.chartInstance.setActiveElements([{
                        datasetIndex: 0,
                        index: index
                    }]);
                    this.setActiveHighlight(index, point.x, point.y);
                    this.chartInstance.update();
                }
            },

            clearChartHighlight() {
                if (!this.chartInstance) return;
                this.chartInstance.setActiveElements([]);
                this.showOverlay = false;
                this.chartInstance.update();
            },

            openHighlightModal(index) {
                const current = this.chartData[this.metric][this.timeframe];
                if (current.highlights && current.highlights[index]) {
                    this.overlayTitle = current.highlights[index].label;
                    this.overlayValue = current.highlights[index].value;
                    this.overlayDetail = current.highlights[index].detail;
                    this.showModal = true;
                }
            },

            getChartConfig() {
                const current = this.chartData[this.metric][this.timeframe];
                return {
                    type: 'line',
                    data: {
                        labels: [...current.labels],
                        datasets: [{
                            label: current.label,
                            data: [...current.data],
                            borderColor: current.color,
                            backgroundColor: current.color + '18', // transparent fill
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3,
                            pointBackgroundColor: current.color,
                            pointHoverRadius: 9,
                            pointRadius: 5,
                            pointHoverBackgroundColor: current.color,
                            pointHoverBorderColor: '#ffffff',
                            pointHoverBorderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animations: {
                            y: {
                                duration: 1200,
                                easing: 'easeOutBack'
                            },
                            x: {
                                duration: 1200,
                                easing: 'easeOutBack'
                            }
                        },
                        onHover: (event, activeElements) => {
                            if (activeElements && activeElements.length > 0) {
                                const index = activeElements[0].index;
                                const element = activeElements[0].element;
                                this.setActiveHighlight(index, element.x, element.y);
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: false // Disable default tooltips to show custom HTML overlay
                            }
                        },
                        scales: {
                            y: {
                                grid: {
                                    color: 'rgba(23, 68, 19, 0.05)'
                                },
                                ticks: {
                                    font: { size: 10, weight: 'bold' },
                                    color: '#8c9597',
                                    callback: (value) => {
                                        if (this.metric === 'revenue') {
                                            if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                                            if (value >= 1000) return 'Rp ' + (value / 1000) + 'rb';
                                            return 'Rp ' + value;
                                        }
                                        return value + ' kg';
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: { size: 10, weight: 'bold' },
                                    color: '#8c9597'
                                }
                            }
                        }
                    }
                };
            },

            updateChart() {
                if (!this.chartInstance) return;
                const current = this.chartData[this.metric][this.timeframe];
                
                // Update datasets
                this.chartInstance.data.labels = [...current.labels];
                this.chartInstance.data.datasets[0].label = current.label;
                this.chartInstance.data.datasets[0].data = [...current.data];
                this.chartInstance.data.datasets[0].borderColor = current.color;
                this.chartInstance.data.datasets[0].backgroundColor = current.color + '18';
                this.chartInstance.data.datasets[0].pointBackgroundColor = current.color;
                this.chartInstance.data.datasets[0].pointHoverBackgroundColor = current.color;
                
                this.chartInstance.update();
            }
        }));
    };

    if (window.Alpine) {
        initChart();
    } else {
        document.addEventListener('alpine:init', initChart);
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endsection
