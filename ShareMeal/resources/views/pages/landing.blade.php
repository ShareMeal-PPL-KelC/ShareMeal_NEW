<x-layouts.app title="ShareMeal - Selamatkan Makanan">
    <!-- Style & Google Fonts Import -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        
        .landing-font {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        
        /* Glassmorphism Cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 10px 40px -10px rgba(23, 68, 19, 0.05);
        }
        
        .glass-card-dark {
            background: rgba(23, 68, 19, 0.04);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(23, 68, 19, 0.08);
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.03);
        }

        .glass-nav-effect {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Sticky Header Scrolled State */
        header {
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        header.scrolled {
            background: rgba(255, 255, 255, 0.78);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border-bottom: 1px solid rgba(23, 68, 19, 0.12);
            box-shadow: 0 10px 30px -10px rgba(23, 68, 19, 0.06);
        }
        
        /* Slow floating animations for background blobs */
        @keyframes float-1 {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.08); }
            66% { transform: translate(-20px, 20px) scale(0.96); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        @keyframes float-2 {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(-40px, 30px) scale(0.96); }
            66% { transform: translate(30px, -30px) scale(1.1); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        @keyframes float-3 {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, 40px) scale(1.06); }
            66% { transform: translate(-30px, -30px) scale(0.94); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        
        .animate-float-1 {
            animation: float-1 25s infinite alternate ease-in-out;
        }
        .animate-float-2 {
            animation: float-2 30s infinite alternate ease-in-out;
        }
        .animate-float-3 {
            animation: float-3 28s infinite alternate ease-in-out;
        }

        /* Card Hover Effects */
        .glass-card-hover {
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .glass-card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 48px -8px rgba(23, 68, 19, 0.08);
            border-color: rgba(23, 68, 19, 0.25);
            background: rgba(255, 255, 255, 0.65);
        }

        /* Scroll Reveal System */
        .reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }
        
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Delays */
        .delay-100 { transition-delay: 100ms; }
        .delay-200 { transition-delay: 200ms; }
        .delay-300 { transition-delay: 300ms; }
        .delay-400 { transition-delay: 400ms; }
        .delay-500 { transition-delay: 500ms; }
    </style>

    <div class="landing-font min-h-screen bg-slate-50/40 relative overflow-x-hidden">
        
        <!-- Ambient Glowing Background Blobs -->
        <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
            <div id="blob-1" class="absolute top-[5%] left-[-15%] w-[45rem] h-[45rem] bg-emerald-200/35 rounded-full blur-[140px] animate-float-1 transition-transform duration-300 ease-out"></div>
            <div id="blob-2" class="absolute top-[25%] right-[-15%] w-[42rem] h-[42rem] bg-teal-100/25 rounded-full blur-[130px] animate-float-2 transition-transform duration-300 ease-out"></div>
            <div id="blob-3" class="absolute top-[55%] left-[-20%] w-[50rem] h-[50rem] bg-lime-100/30 rounded-full blur-[150px] animate-float-3 transition-transform duration-300 ease-out"></div>
            <div id="blob-4" class="absolute top-[75%] right-[-10%] w-[38rem] h-[38rem] bg-amber-100/25 rounded-full blur-[120px] animate-float-1 transition-transform duration-300 ease-out"></div>
        </div>

        <!-- Sticky Glassmorphic Header -->
        <header id="main-header" class="sticky top-0 z-40 glass-nav-effect">
            <div class="container-shell flex h-16 items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logo.png') }}" class="h-10 w-10 object-cover rounded-full group-hover:scale-105 transition duration-300" alt="ShareMeal Logo">
                    <span class="text-xl font-bold text-[#174413] tracking-tight group-hover:text-emerald-800 transition">ShareMeal</span>
                </a>
                <nav class="hidden items-center gap-8 md:flex">
                    <a href="#eksplorasi" class="text-sm font-semibold text-slate-600 hover:text-[#174413] transition-colors">Eksplorasi</a>
                    <a href="#fitur" class="text-sm font-semibold text-slate-600 hover:text-[#174413] transition-colors">Fitur</a>
                    <a href="#bergabung" class="text-sm font-semibold text-slate-600 hover:text-[#174413] transition-colors">Bergabung</a>
                    <div class="flex items-center gap-3 border-l border-slate-200/80 pl-6">
                        <a href="{{ route('login') }}" class="rounded-xl border border-slate-200 bg-white/40 backdrop-blur px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-white hover:text-[#174413] hover:border-emerald-200 transition shadow-sm">Masuk</a>
                        <a href="{{ route('register') }}" class="rounded-xl bg-gradient-to-tr from-[#174413] to-[#25661e] px-4 py-2.5 text-sm font-semibold text-white hover:shadow-md hover:shadow-emerald-900/10 hover:brightness-110 active:scale-[0.98] transition">Daftar</a>
                    </div>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative py-24 md:py-32 z-10">
            <div class="container-shell grid items-center gap-16 md:grid-cols-2">
                <div class="relative z-20 reveal">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1 text-xs font-bold bg-emerald-100/90 text-emerald-850 border border-emerald-200/80 backdrop-blur-md mb-6 tracking-wide uppercase">🌿 Gerakan Zero Food Waste</span>
                    <h1 class="text-5xl md:text-6xl font-black text-slate-900 leading-[1.1] tracking-tight">
                        Selamatkan Makanan,<br/>
                        <span class="bg-gradient-to-r from-[#174413] to-emerald-500 bg-clip-text text-transparent">Selamatkan Bumi</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-lg md:text-xl text-slate-600 leading-relaxed font-medium">
                        ShareMeal menghubungkan bisnis pangan dengan konsumen dan lembaga sosial untuk mengurangi food waste dan membangun ekosistem pangan yang berkelanjutan.
                    </p>
                    <div class="mt-10 flex flex-wrap gap-4">
                        <a class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-tr from-[#174413] to-[#25661e] px-7 py-4 text-base font-bold text-white shadow-lg shadow-emerald-950/20 hover:shadow-xl hover:shadow-emerald-900/25 hover:brightness-110 active:scale-[0.98] transition-all duration-300" href="{{ route('register') }}">
                            Mulai Sekarang
                            <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                        <a class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white/40 backdrop-blur-md px-7 py-4 text-base font-bold text-slate-700 hover:bg-white hover:text-[#174413] hover:border-emerald-200 transition-all duration-300 shadow-sm" href="{{ route('consumer.search') }}">
                            Cari Makanan
                        </a>
                    </div>
                </div>
                <div class="relative z-10 flex justify-center reveal delay-200">
                    <div class="absolute inset-0 bg-gradient-to-tr from-emerald-500/10 to-teal-500/5 rounded-[2.5rem] blur-2xl transform rotate-2"></div>
                    <div class="relative p-4 glass-card rounded-[2.5rem] shadow-2xl transition-all duration-500 hover:rotate-1 hover:scale-[1.01]">
                        <div class="overflow-hidden rounded-[2rem]">
                            <img src="https://images.unsplash.com/photo-1563485571829-7032f428f3ce?auto=format&fit=crop&q=80&w=1200" alt="Fresh food" class="h-[28rem] w-full object-cover">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-10 relative z-10">
            <div class="container-shell">
                <div class="glass-card rounded-3xl py-12 px-8 grid grid-cols-2 gap-8 text-center md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-slate-200/50">
                    <div class="pt-4 md:pt-0 reveal delay-100">
                        <div class="text-4xl md:text-5xl font-black bg-gradient-to-r from-[#174413] to-emerald-600 bg-clip-text text-transparent">500+</div>
                        <div class="mt-2 text-sm font-semibold tracking-wide text-slate-500 uppercase">Mitra Toko</div>
                    </div>
                    <div class="pt-4 md:pt-0 reveal delay-200">
                        <div class="text-4xl md:text-5xl font-black bg-gradient-to-r from-[#174413] to-emerald-600 bg-clip-text text-transparent">50K+</div>
                        <div class="mt-2 text-sm font-semibold tracking-wide text-slate-500 uppercase">Pengguna Aktif</div>
                    </div>
                    <div class="pt-4 md:pt-0 reveal delay-300">
                        <div class="text-4xl md:text-5xl font-black bg-gradient-to-r from-[#174413] to-emerald-600 bg-clip-text text-transparent">100K+</div>
                        <div class="mt-2 text-sm font-semibold tracking-wide text-slate-500 uppercase">Makanan Terselamatkan</div>
                    </div>
                    <div class="pt-4 md:pt-0 reveal delay-400">
                        <div class="text-4xl md:text-5xl font-black bg-gradient-to-r from-[#174413] to-emerald-600 bg-clip-text text-transparent">200+</div>
                        <div class="mt-2 text-sm font-semibold tracking-wide text-slate-500 uppercase">Lembaga Sosial</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cara Kerja (Eksplorasi) Section -->
        <section id="eksplorasi" class="py-24 relative z-10">
            <div class="container-shell">
                <div class="mb-20 text-center max-w-2xl mx-auto reveal">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1 text-xs font-bold bg-emerald-100/90 text-emerald-800 border border-emerald-200/80 backdrop-blur-md mb-4 uppercase tracking-wide">Alur Sistem</span>
                    <h2 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">Bagaimana Cara Kerjanya?</h2>
                    <p class="mt-4 text-lg md:text-xl text-slate-650 font-medium">Platform tiga arah yang menghubungkan semua pihak</p>
                </div>
                <div class="grid gap-8 md:grid-cols-3">
                    @foreach ([
                        [
                            'title' => 'Untuk Mitra', 
                            'desc' => 'Pelaku Usaha Pangan', 
                            'items' => ['Kelola inventaris surplus makanan', 'Atur flash sale otomatis', 'Donasikan makanan ke lembaga sosial', 'Kurangi food waste & biaya TPA'], 
                            'route' => route('mitra.dashboard')
                        ], 
                        [
                            'title' => 'Untuk Konsumen', 
                            'desc' => 'Pembeli Cerdas', 
                            'items' => ['Beli makanan berkualitas dengan harga diskon', 'Cari toko terdekat dengan GPS', 'Notifikasi flash sale real-time', 'Berkontribusi kurangi food waste'], 
                            'route' => route('consumer.dashboard')
                        ], 
                        [
                            'title' => 'Untuk Lembaga Sosial', 
                            'desc' => 'Penerima Donasi', 
                            'items' => ['Terima donasi makanan layak konsumsi', 'Klaim donasi first-come first-served', 'Tracking logistik real-time', 'Riwayat penerimaan lengkap'], 
                            'route' => route('lembaga.dashboard')
                        ]
                    ] as $card)
                        <div class="glass-card glass-card-hover p-8 rounded-3xl flex flex-col justify-between h-full reveal delay-{{ $loop->iteration * 100 }}">
                            <div>
                                @if($card['title'] === 'Untuk Mitra')
                                    <!-- Store SVG Icon -->
                                    <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-tr from-[#174413] to-emerald-600 text-white shadow-lg shadow-emerald-950/10">
                                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                @elseif($card['title'] === 'Untuk Konsumen')
                                    <!-- Shopping Bag SVG Icon -->
                                    <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white shadow-lg shadow-emerald-950/10">
                                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                    </div>
                                @else
                                    <!-- Heart/Donation SVG Icon -->
                                    <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-tr from-[#174413] to-teal-500 text-white shadow-lg shadow-emerald-950/10">
                                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                    </div>
                                @endif
                                
                                <h3 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $card['title'] }}</h3>
                                <p class="mt-2 text-sm font-semibold tracking-wide text-emerald-800/80 uppercase">{{ $card['desc'] }}</p>
                                
                                <ul class="mt-6 space-y-4 text-slate-650">
                                    @foreach ($card['items'] as $item)
                                        <li class="flex items-start gap-3">
                                            <span class="flex h-5.5 w-5.5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 mt-0.5">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </span>
                                            <span class="text-sm font-medium leading-relaxed">{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <a href="{{ $card['route'] }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white/60 hover:bg-gradient-to-tr hover:from-[#174413] hover:to-[#25661e] hover:text-white hover:border-transparent transition-all duration-300 font-bold text-slate-700 py-3.5 mt-8 w-full shadow-sm text-sm">
                                Pelajari Lebih Lanjut
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Fitur Section -->
        <section id="fitur" class="py-24 relative z-10 bg-gradient-to-b from-transparent via-emerald-50/10 to-transparent">
            <div class="container-shell">
                <div class="mb-20 text-center max-w-2xl mx-auto reveal">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1 text-xs font-bold bg-emerald-100/90 text-emerald-800 border border-emerald-200/80 backdrop-blur-md mb-4 uppercase tracking-wide">Fungsionalitas</span>
                    <h2 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">Fitur Unggulan</h2>
                    <p class="mt-4 text-lg md:text-xl text-slate-650 font-medium">Teknologi canggih untuk distribusi pangan berkelanjutan</p>
                </div>
                <div class="grid gap-8 sm:grid-cols-2 md:grid-cols-3">
                    @foreach ([
                        ['Flash Sale Timer', 'Countdown otomatis untuk makanan near-expired dengan diskon bertahap'], 
                        ['Location-Based Search', 'Temukan toko terdekat dengan teknologi GPS real-time'], 
                        ['Rating & Review', 'Sistem rating transparan dengan upload foto bukti kualitas'], 
                        ['Verifikasi Admin', 'Semua mitra dan lembaga terverifikasi untuk keamanan maksimal'], 
                        ['Kurangi Food Waste', 'Distribusi otomatis ke Jual atau Donasi berdasarkan kelayakan'], 
                        ['Multi-Cabang', 'Kelola inventaris berbagai cabang dalam satu akun induk']
                    ] as $feature)
                        <div class="glass-card glass-card-hover p-8 text-center rounded-3xl flex flex-col justify-between reveal delay-{{ ($loop->index % 3 + 1) * 100 }}">
                            <div>
                                @if($feature[0] === 'Flash Sale Timer')
                                    <!-- Clock Icon -->
                                    <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-tr from-amber-400 to-orange-500 text-white shadow-md shadow-orange-500/10">
                                        <svg class="h-6 w-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                @elseif($feature[0] === 'Location-Based Search')
                                    <!-- Map Pin Icon -->
                                    <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white shadow-md shadow-emerald-500/10">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                @elseif($feature[0] === 'Rating & Review')
                                    <!-- Star Icon -->
                                    <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-tr from-yellow-400 to-amber-500 text-white shadow-md shadow-yellow-500/10">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.243.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.77-.57-.372-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                        </svg>
                                    </div>
                                @elseif($feature[0] === 'Verifikasi Admin')
                                    <!-- Shield Check Icon -->
                                    <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-tr from-blue-500 to-indigo-600 text-white shadow-md shadow-blue-500/10">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.957 11.957 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                @elseif($feature[0] === 'Kurangi Food Waste')
                                    <!-- Recycle/Leaf Icon -->
                                    <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-tr from-[#174413] to-emerald-500 text-white shadow-md shadow-emerald-500/10">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707-.707M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                @else
                                    <!-- Multi-Branch Icon -->
                                    <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-tr from-teal-500 to-emerald-600 text-white shadow-md shadow-teal-500/10">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                @endif
                                <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ $feature[0] }}</h3>
                                <p class="mt-3 text-sm text-slate-600 leading-relaxed font-medium">{{ $feature[1] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Dampak Positif Section -->
        <section class="py-24 relative z-10">
            <div class="container-shell grid items-center gap-16 md:grid-cols-2">
                <div class="relative order-last md:order-first reveal">
                    <div class="absolute inset-0 bg-gradient-to-tr from-emerald-500/10 to-transparent rounded-[2.5rem] blur-2xl transform -rotate-2"></div>
                    <div class="relative p-4 glass-card rounded-[2.5rem] shadow-xl hover:-rotate-1 hover:scale-[1.01] transition-all duration-500">
                        <div class="overflow-hidden rounded-[2rem]">
                            <img src="https://images.unsplash.com/photo-1593113702251-272b1bc414a9?auto=format&fit=crop&q=80&w=1200" alt="Food donation" class="h-[28rem] w-full object-cover">
                        </div>
                    </div>
                </div>
                <div class="relative z-20 reveal">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1 text-xs font-bold bg-emerald-100/90 text-emerald-800 border border-emerald-200/80 backdrop-blur-md mb-4 uppercase tracking-wide">Dampak Positif</span>
                    <h2 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">Bersama Membangun Masa Depan Berkelanjutan</h2>
                    <p class="mt-6 text-lg text-slate-650 font-medium leading-relaxed">
                        ShareMeal tidak hanya mengurangi food waste, tetapi juga membantu lembaga sosial mendapatkan akses pangan berkualitas dan memberikan peluang konsumen untuk berkontribusi pada lingkungan.
                    </p>
                    <div class="mt-8 space-y-6">
                        @foreach ([
                            ['Kurangi Limbah TPA', 'Mencegah ton makanan berakhir di tempat pemrosesan akhir'], 
                            ['Bantu Lembaga Sosial', 'Distribusi makanan layak konsumsi ke panti asuhan dan yayasan'], 
                            ['Hemat Biaya Operasional', 'Mitra menghemat biaya pembuangan dan dapatkan tambahan pendapatan']
                        ] as $impact)
                            <div class="flex items-start gap-4 reveal delay-{{ $loop->iteration * 100 }}">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 shadow-sm border border-emerald-200">
                                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 text-lg leading-snug">{{ $impact[0] }}</div>
                                    <div class="text-slate-650 mt-1 leading-relaxed text-sm font-medium">{{ $impact[1] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- Climax CTA (Bergabung) Section -->
        <section id="bergabung" class="py-16 relative z-10">
            <div class="container-shell">
                <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-[#12360f] to-[#1c5317] py-20 px-8 md:px-16 text-center shadow-2xl border border-white/10 reveal">
                    <!-- Internal Blobs -->
                    <div class="absolute top-[-20%] left-[-10%] w-[25rem] h-[25rem] bg-emerald-400/20 rounded-full blur-[80px]"></div>
                    <div class="absolute bottom-[-20%] right-[-10%] w-[30rem] h-[30rem] bg-lime-400/15 rounded-full blur-[90px]"></div>
                    
                    <div class="relative z-10 max-w-3xl mx-auto reveal delay-200">
                        <h2 class="text-4xl md:text-5xl font-black text-white tracking-tight leading-tight">Siap Bergabung dengan ShareMeal?</h2>
                        <p class="mt-6 text-lg md:text-xl text-emerald-100 font-medium max-w-xl mx-auto">Jadilah bagian dari gerakan mengurangi food waste di Indonesia</p>
                        <div class="mt-10 flex flex-wrap justify-center gap-5">
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-2xl bg-white text-[#174413] hover:bg-slate-50 shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 font-bold px-8 py-4 text-base">
                                Daftar Sekarang
                            </a>
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-2xl border border-white/40 text-white bg-white/10 backdrop-blur hover:bg-white/20 shadow-md hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 font-bold px-8 py-4 text-base">
                                Sudah Punya Akun
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer Section -->
        <footer class="bg-slate-900 pt-20 pb-12 text-slate-400 relative z-20 border-t border-slate-800">
            <div class="container-shell grid gap-12 md:grid-cols-4">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" class="h-8 w-8 object-cover rounded-full" alt="ShareMeal Logo">
                        <span class="font-bold text-white text-lg tracking-tight">ShareMeal</span>
                    </div>
                    <p class="text-sm leading-relaxed">Platform digital untuk mengoptimalkan pemanfaatan surplus pangan dan mengurangi food waste.</p>
                </div>
                <div>
                    <h3 class="font-bold text-white text-sm tracking-wider uppercase">Untuk Pengguna</h3>
                    <ul class="mt-5 space-y-3.5 text-sm">
                        <li><a href="{{ route('consumer.dashboard') }}" class="hover:text-emerald-400 hover:translate-x-1 transition-all duration-200 inline-block font-semibold">Konsumen</a></li>
                        <li><a href="{{ route('mitra.dashboard') }}" class="hover:text-emerald-400 hover:translate-x-1 transition-all duration-200 inline-block font-semibold">Mitra Toko</a></li>
                        <li><a href="{{ route('lembaga.dashboard') }}" class="hover:text-emerald-400 hover:translate-x-1 transition-all duration-200 inline-block font-semibold">Lembaga Sosial</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-bold text-white text-sm tracking-wider uppercase">Perusahaan</h3>
                    <ul class="mt-5 space-y-3.5 text-sm">
                        <li><a href="#" class="hover:text-emerald-400 hover:translate-x-1 transition-all duration-200 inline-block font-semibold">Tentang Kami</a></li>
                        <li><a href="#" class="hover:text-emerald-400 hover:translate-x-1 transition-all duration-200 inline-block font-semibold">Blog</a></li>
                        <li><a href="#" class="hover:text-emerald-400 hover:translate-x-1 transition-all duration-200 inline-block font-semibold">Kontak</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-bold text-white text-sm tracking-wider uppercase">Legal</h3>
                    <ul class="mt-5 space-y-3.5 text-sm">
                        <li><a href="#" class="hover:text-emerald-400 hover:translate-x-1 transition-all duration-200 inline-block font-semibold">Kebijakan Privasi</a></li>
                        <li><a href="#" class="hover:text-emerald-400 hover:translate-x-1 transition-all duration-200 inline-block font-semibold">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
            </div>
            <div class="container-shell mt-16 border-t border-slate-800/80 pt-8 text-center text-xs font-semibold tracking-wider text-slate-500">
                &copy; 2026 ShareMeal. All rights reserved.
            </div>
        </footer>
    </div>

    <!-- Scroll Interaction & Reveal Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Scroll-Reactive Header (dense glass state)
            const header = document.getElementById('main-header');
            const handleScroll = () => {
                if (window.scrollY > 20) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            };
            window.addEventListener('scroll', handleScroll);
            handleScroll(); // Trigger once on load to ensure state correctness

            // 2. Parallax Effect for Ambient Background Blobs
            const blob1 = document.getElementById('blob-1');
            const blob2 = document.getElementById('blob-2');
            const blob3 = document.getElementById('blob-3');
            const blob4 = document.getElementById('blob-4');

            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                if (blob1) blob1.style.transform = `translateY(${scrolled * 0.14}px)`;
                if (blob2) blob2.style.transform = `translateY(${scrolled * -0.08}px) rotate(${scrolled * 0.04}deg)`;
                if (blob3) blob3.style.transform = `translateY(${scrolled * 0.1}px)`;
                if (blob4) blob4.style.transform = `translateY(${scrolled * -0.06}px)`;
            });

            // 3. Staggered Reveal Animation using Intersection Observer
            const observerOptions = {
                root: null,
                rootMargin: '0px 0px -60px 0px',
                threshold: 0.08
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        // Unobserve after visual animation triggers to save CPU
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            const revealElements = document.querySelectorAll('.reveal');
            revealElements.forEach(el => observer.observe(el));
        });
    </script>
</x-layouts.app>
