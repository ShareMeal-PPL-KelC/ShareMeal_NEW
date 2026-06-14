<x-layouts.app title="Daftar - ShareMeal">
    <!-- Style & Google Fonts Import -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=300;400;500;600;700;800&display=swap');
        
        .register-font {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        
        /* Glassmorphism Cards */
        .glass-card {
            background: rgba(255,255,255,0.50);
            backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.6);
            box-shadow: 0 20px 50px -15px rgba(23,68,19,0.08);
        }

        .glass-card-dark {
            background: rgba(10,30,8,0.5);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.12);
        }

        /* Floating background blobs */
        @keyframes float-1 {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(40px, -60px) scale(1.1); }
            66% { transform: translate(-30px, 30px) scale(0.95); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        @keyframes float-2 {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(-50px, 40px) scale(0.95); }
            66% { transform: translate(40px, -40px) scale(1.15); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        @keyframes float-3 {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(40px, 50px) scale(1.08); }
            66% { transform: translate(-40px, -40px) scale(0.92); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        
        .animate-float-1 {
            animation: float-1 22s infinite alternate ease-in-out;
        }
        .animate-float-2 {
            animation: float-2 25s infinite alternate ease-in-out;
        }
        .animate-float-3 {
            animation: float-3 24s infinite alternate ease-in-out;
        }

        /* Dashboard Float Animations */
        @keyframes float-dashboard {
            0% { transform: translateY(0px) rotate(-0.2deg); }
            50% { transform: translateY(-10px) rotate(0.2deg); }
            100% { transform: translateY(0px) rotate(-0.2deg); }
        }
        @keyframes float-badge-up {
            0% { transform: translateY(0px) translateX(0px); }
            50% { transform: translateY(-14px) translateX(-4px); }
            100% { transform: translateY(0px) translateX(0px); }
        }
        .animate-float-dashboard {
            animation: float-dashboard 7s infinite ease-in-out;
        }
        .animate-float-badge-up {
            animation: float-badge-up 6s infinite ease-in-out;
        }

        /* Radar and Pin Animations */
        @keyframes radar-pulse {
            0% { transform: scale(0.2); opacity: 0.8; }
            80% { transform: scale(1.2); opacity: 0; }
            100% { transform: scale(1.4); opacity: 0; }
        }
        .animate-radar-pulse {
            animation: radar-pulse 3s infinite ease-out;
        }
        @keyframes pin-glow {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16,185,129,0.7); }
            70% { transform: scale(1.15); box-shadow: 0 0 0 8px rgba(16,185,129,0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16,185,129,0); }
        }
        .animate-pin-glow {
            animation: pin-glow 2s infinite ease-in-out;
        }

        /* Aurora Orbs */
        @keyframes aurora-1 {
            0%   { transform: translate(0,0)       scale(1);    opacity:.55; }
            35%  { transform: translate(60px,-80px) scale(1.2); opacity:.72; }
            70%  { transform: translate(-40px,40px) scale(0.9); opacity:.48; }
            100% { transform: translate(0,0)       scale(1);    opacity:.55; }
        }
        @keyframes aurora-2 {
            0%   { transform: translate(0,0)        scale(1);    opacity:.4; }
            40%  { transform: translate(-70px,60px) scale(1.3);  opacity:.6; }
            75%  { transform: translate(50px,-50px) scale(0.85); opacity:.35; }
            100% { transform: translate(0,0)        scale(1);    opacity:.4; }
        }
        @keyframes aurora-3 {
            0%   { transform: translate(0,0)         scale(1);    opacity:.3; }
            50%  { transform: translate(40px,70px)   scale(1.15); opacity:.5; }
            85%  { transform: translate(-60px,-30px) scale(0.9);  opacity:.25; }
            100% { transform: translate(0,0)         scale(1);    opacity:.3; }
        }
        .aurora-orb-1 {
            position:absolute; width:420px; height:420px; border-radius:50%;
            background: radial-gradient(circle, rgba(52,211,153,.35) 0%, rgba(16,185,129,.1) 50%, transparent 75%);
            filter: blur(60px);
            animation: aurora-1 18s infinite ease-in-out;
            pointer-events:none;
        }
        .aurora-orb-2 {
            position:absolute; width:360px; height:360px; border-radius:50%;
            background: radial-gradient(circle, rgba(52,211,153,.3) 0%, rgba(16,185,129,.08) 50%, transparent 75%);
            filter: blur(70px);
            animation: aurora-2 22s infinite ease-in-out;
            pointer-events:none;
        }
        .aurora-orb-3 {
            position:absolute; width:280px; height:280px; border-radius:50%;
            background: radial-gradient(circle, rgba(110,231,183,.3) 0%, rgba(52,211,153,.08) 55%, transparent 75%);
            filter: blur(50px);
            animation: aurora-3 26s infinite ease-in-out;
            pointer-events:none;
        }

        /* Shimmer badge */
        @keyframes shimmer-badge {
            0%   { background-position: -200% center; }
            100% { background-position:  200% center; }
        }
        .badge-shimmer {
            background: linear-gradient(90deg, rgba(52,211,153,.15) 0%, rgba(52,211,153,.35) 40%, rgba(52,211,153,.45) 55%, rgba(52,211,153,.35) 70%, rgba(52,211,153,.15) 100%);
            background-size: 200% auto;
            animation: shimmer-badge 4s linear infinite;
        }
    </style>

    <div class="register-font flex h-screen overflow-hidden lg:grid lg:grid-cols-2 relative bg-[#f4f7f4]">
        
        <!-- Animated Background Blobs -->
        <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-[10%] -left-[10%] w-[50%] h-[50%] rounded-full bg-emerald-200/40 blur-[120px] animate-float-1"></div>
            <div class="absolute -bottom-[10%] -right-[10%] w-[60%] h-[60%] rounded-full bg-teal-200/45 blur-[130px] animate-float-2"></div>
            <div class="absolute top-[40%] left-[30%] w-[35%] h-[35%] rounded-full bg-green-200/35 blur-[100px] animate-float-3"></div>
        </div>

        {{-- Falling Leaves Effect --}}
        <x-falling-leaves />

        <!-- Left Column: Elegant Visual Hero Panel (Desktop) -->
        <div class="relative hidden overflow-hidden lg:flex flex-col justify-between p-12 z-10 h-screen flex-shrink-0">
            <!-- Base image + light green transparent overlay -->
            <div class="absolute inset-0 z-0">
                <img src="/images/logo2.jpg" alt="ShareMeal" class="absolute inset-0 h-full w-full object-cover object-center opacity-80">
                <div class="absolute inset-0 bg-gradient-to-b from-[#f4f7f4]/70 via-[#eef4ee]/55 to-[#e4eee4]/85"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-[#f4f7f4]/45 via-transparent to-transparent"></div>
            </div>

            <!-- Aurora Orbs -->
            <div class="aurora-orb-1" style="top:-80px;left:-60px;"></div>
            <div class="aurora-orb-2" style="bottom:70px;right:-90px;"></div>
            <div class="aurora-orb-3" style="top:42%;left:28%;"></div>

            <!-- TOP: Logo -->
            <div class="relative z-10 flex items-center justify-between">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                    <div class="relative">
                        <div class="absolute inset-0 rounded-full bg-emerald-600/15 blur-md group-hover:blur-lg transition-all"></div>
                        <img src="{{ asset('images/logo.png') }}" class="relative h-11 w-11 object-cover rounded-full ring-2 ring-emerald-700/10 transition-transform group-hover:scale-105" alt="ShareMeal Logo">
                    </div>
                    <span class="text-2xl font-extrabold text-[#174413] tracking-tight">ShareMeal</span>
                </a>
                
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-emerald-600/15 bg-white/40 text-[10px] font-bold text-emerald-800 backdrop-blur-sm">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-ping inline-block"></span>
                    <span>200+ Mitra Aktif Terhubung</span>
                </div>
            </div>

            <!-- MIDDLE: Real-time Rescue Map Radar Mockup -->
            <div class="relative z-10 my-auto py-6 flex flex-col items-center">
                <!-- Text Intro -->
                <div class="w-full max-w-md mb-6 text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-emerald-600/20 badge-shimmer mb-3">
                        <span class="text-[9px] font-bold uppercase tracking-[0.15em] text-emerald-800">Radar Pangan Surplus</span>
                    </div>
                    <h2 class="text-[2.2rem] font-extrabold leading-[1.15] text-[#174413] tracking-tight">
                        Cari Makanan Murah<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-700 via-teal-700 to-green-700">Di Sekitar Anda!</span>
                    </h2>
                </div>

                <!-- Main Floating Card Mockup -->
                <div class="w-full max-w-md bg-white/90 border border-emerald-100/90 rounded-2xl shadow-2xl p-5 backdrop-blur-md relative animate-float-dashboard">
                    <!-- Browser Window Control Dots -->
                    <div class="flex items-center gap-1.5 mb-4">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-400/80"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400/80"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-400/80"></span>
                        <span class="text-[10px] text-[#174413]/40 font-bold ml-2 tracking-wider">radar_penyelamatan.html</span>
                    </div>

                    <!-- Abstract Map Radar Container -->
                    <div class="bg-emerald-50/40 rounded-2xl relative w-full h-[230px] overflow-hidden border border-emerald-100/30 shadow-inner z-0">
                        <!-- Dotted Map Grid Lines -->
                        <div class="absolute w-px h-full bg-emerald-700/5 left-[33%]"></div>
                        <div class="absolute w-px h-full bg-emerald-700/5 left-[66%]"></div>
                        <div class="absolute h-px w-full bg-emerald-700/5 top-[33%]"></div>
                        <div class="absolute h-px w-full bg-emerald-700/5 top-[66%]"></div>

                        <!-- Center User Location & Radar Pulse -->
                        <div class="absolute top-[50%] left-[50%] -translate-x-1/2 -translate-y-1/2 flex items-center justify-center z-10">
                            <!-- Radar expanding rings -->
                            <div class="absolute w-12 h-12 rounded-full border border-emerald-400/40 animate-radar-pulse"></div>
                            <div class="absolute w-24 h-24 rounded-full border border-emerald-400/20 animate-radar-pulse" style="animation-delay: 1s;"></div>
                            <div class="absolute w-36 h-36 rounded-full border border-emerald-400/10 animate-radar-pulse" style="animation-delay: 2s;"></div>
                            
                            <!-- User Beacon Dot -->
                            <div class="h-4.5 w-4.5 rounded-full bg-blue-500 border-2 border-white flex items-center justify-center shadow-lg relative z-20">
                                <span class="h-2 w-2 rounded-full bg-white animate-ping"></span>
                            </div>
                        </div>

                        <!-- Merchant Pins with Pulse Glow -->
                        <!-- Pin 1 (Top Left) -->
                        <div class="absolute top-8 left-16 z-10">
                            <div class="h-3 w-3 rounded-full bg-emerald-500 border border-white animate-pin-glow"></div>
                        </div>

                        <!-- Pin 2 (Bottom Right) -->
                        <div class="absolute bottom-12 right-20 z-10">
                            <div class="h-3 w-3 rounded-full bg-emerald-500 border border-white animate-pin-glow"></div>
                        </div>

                        <!-- Pin 3 (Top Right - Active target) -->
                        <div class="absolute top-14 right-24 z-10 flex items-center justify-center">
                            <div class="h-3.5 w-3.5 rounded-full bg-emerald-600 border-2 border-white animate-pin-glow shadow-md"></div>
                            
                            <!-- Connecting light path to active pin -->
                            <div class="absolute top-[50%] left-[50%] w-[120px] h-px bg-gradient-to-r from-blue-400/10 to-emerald-500/80 -rotate-12 transform origin-left z-0 pointer-events-none"></div>
                        </div>

                        <!-- Active Pin Map Tooltip Popup -->
                        <div class="absolute top-5 right-4 bg-white/95 border border-emerald-100 shadow-xl rounded-xl p-2.5 w-[145px] text-left z-20 backdrop-blur-sm pointer-events-none transition-all duration-300">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-[10px] font-black text-[#174413] truncate">Bakery Lestari</span>
                                <span class="text-[8px] font-extrabold px-1 py-0.25 rounded bg-emerald-100 text-emerald-800">-60%</span>
                            </div>
                            <p class="text-[9px] text-[#174413]/70 font-semibold mt-0.5">3 Porsi Roti Manis</p>
                            <div class="flex items-center justify-between mt-2 pt-1 border-t border-emerald-50/50">
                                <span class="text-[8px] text-emerald-700 font-bold">250m terdekat</span>
                                <span class="text-[9px] font-extrabold text-emerald-800">Rp 12.000</span>
                            </div>
                        </div>

                        <!-- Live searching banner at bottom -->
                        <div class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-white/85 border border-emerald-100/50 px-3 py-1 rounded-full text-[9px] font-extrabold text-emerald-800 flex items-center gap-1.5 backdrop-blur-sm shadow-sm">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Mencari surplus di sekitar Anda...</span>
                        </div>
                    </div>

                    <!-- Floating Badge: Nearby Stores Count -->
                    <div class="absolute -right-10 -bottom-6 bg-white border border-emerald-100 rounded-2xl p-3 shadow-xl flex items-center gap-3 animate-float-badge-up backdrop-blur-md">
                        <!-- Radar / Compass Icon -->
                        <div class="h-9 w-9 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-md flex-shrink-0">
                            <svg class="w-5 h-5 animate-spin" style="animation-duration: 20s" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3L16.5 16.5 9 9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 9l-4.5 4.5m4.5-4.5V3"/></svg>
                        </div>
                        <div>
                            <span class="block text-[8px] font-extrabold uppercase tracking-wider text-emerald-800/50">Radius Penyelamatan</span>
                            <span class="block text-xs font-black text-[#174413] tracking-tight">12 Mitra Aktif</span>
                            <span class="text-[8px] font-bold text-emerald-700">Ditemukan</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOTTOM: Info Card -->
            <div class="relative z-10 w-full max-w-md mx-auto">
                <div class="glass-card rounded-[1.5rem] p-4 text-center">
                    <p class="text-xs text-[#174413]/80 leading-relaxed font-semibold">
                        🤝 Jadilah bagian dari jaringan kebaikan kami hari ini.
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Column: Scrollable Form Wrapper -->
        <div class="flex-1 overflow-y-auto h-screen flex items-start justify-center px-6 py-12 lg:px-16 z-10 relative">
            <div class="w-full max-w-lg">
                <!-- Mobile Logo Header -->
                <div class="mb-8 lg:hidden">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                        <img src="{{ asset('images/logo.png') }}" class="h-9 w-9 object-cover rounded-full" alt="ShareMeal Logo">
                        <span class="text-2xl font-extrabold text-[#174413] tracking-tight">ShareMeal</span>
                    </a>
                </div>

                <!-- Form Card -->
                <div class="glass-card p-8 sm:p-10 rounded-[2.5rem]" 
                     x-data="{ 
                        userType: '{{ old('user_type', 'mitra') }}', 
                        showPassword: false, 
                        showPasswordConfirmation: false,
                        ktpError: '',
                        siupError: '',
                        nibError: '',
                        halalError: '',
                        legalitasError: '',
                        izinError: '',
                        identitasError: '',
                        emailError: '',
                        passwordError: '',
                        termsError: '',
                        validateFile(e, errorVar) {
                            const file = e.target.files[0];
                            this[errorVar] = '';
                            if (!file) return;

                            // Size check (2MB = 2048 * 1024 bytes)
                            const maxSize = 2 * 1024 * 1024;
                            if (file.size > maxSize) {
                                this[errorVar] = 'Ukuran berkas melebihi batas 2 MB. Silakan pilih berkas yang lebih kecil.';
                                e.target.value = '';
                                return;
                            }

                            // Extension check
                            const allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                            const extension = file.name.split('.').pop().toLowerCase();
                            if (!allowedExtensions.includes(extension)) {
                                this[errorVar] = 'Format tidak valid. Hanya JPG, PNG, atau PDF yang diperbolehkan.';
                                e.target.value = '';
                                return;
                            }
                        }
                     }">
                    <div class="mb-8">
                        <h1 class="text-3xl font-extrabold text-[#174413] tracking-tight">Buat Akun Baru</h1>
                        <p class="mt-2 text-sm text-[#174413]/70">Langkah awal Anda menuju masa depan tanpa limbah.</p>
                    </div>

                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="mb-6 rounded-2xl bg-red-50/70 backdrop-blur-md p-4 border border-red-200/50">
                            <ul class="list-inside list-disc text-xs text-red-700 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="post" action="{{ route('register.submit') }}" enctype="multipart/form-data" class="space-y-6"
                          @submit="if (!document.getElementById('terms_checkbox').checked) { $event.preventDefault(); termsError = 'Anda harus menyetujui Syarat & Ketentuan serta Kebijakan Privasi untuk melanjutkan.'; }">
                        @csrf
                        
                        <!-- Role Picker Switchers -->
                        <div>
                            <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-[#174413]/85">Pilih Peran Anda</label>
                            <div class="grid gap-3 grid-cols-1 md:grid-cols-3">
                                @foreach ([
                                    ['mitra', 'Mitra', 'Toko/Restoran', 'store'],
                                    ['consumer', 'Konsumen', 'Penyelamat', 'user'],
                                    ['lembaga', 'Lembaga', 'Organisasi', 'heart']
                                ] as $role)
                                    <label class="cursor-pointer rounded-2xl border-2 p-4 transition-all duration-300 relative block group hover:scale-[1.02] active:scale-95 text-center md:text-left"
                                           :class="userType === '{{ $role[0] }}' ? 'border-emerald-600 bg-white shadow-md shadow-emerald-600/5' : 'border-[#174413]/10 bg-white/50 hover:bg-white/95'">
                                        <input type="radio" name="user_type" value="{{ $role[0] }}" x-model="userType" class="sr-only" {{ old('user_type', 'mitra') == $role[0] ? 'checked' : '' }}>
                                        
                                        <div class="flex flex-col items-center md:items-start">
                                            <!-- Role Icon Badge -->
                                            <div class="w-8 h-8 rounded-xl flex items-center justify-center mb-3 transition-colors"
                                                 :class="userType === '{{ $role[0] }}' ? 'bg-emerald-100 text-emerald-700' : 'bg-emerald-50 text-[#174413]/55 group-hover:bg-emerald-100/50'">
                                                <i data-lucide="{{ $role[3] }}" class="w-4.5 h-4.5"></i>
                                            </div>
                                            <div class="text-sm font-extrabold text-[#174413]">{{ $role[1] }}</div>
                                            <div class="text-[9px] leading-tight text-[#174413]/50 font-bold uppercase tracking-wider mt-1">{{ $role[2] }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('user_type') <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <!-- Mitra Verification Document Uploads -->
                        <div x-show="userType === 'mitra'" x-cloak class="space-y-5 border-t border-b border-[#174413]/10 py-5 my-4" x-transition>
                            <h3 class="font-extrabold text-[#174413] flex items-center gap-2 text-sm">
                                <i data-lucide="shield-check" class="w-5 h-5 text-emerald-600"></i>
                                Dokumen Legalitas Usaha (Maks. 2 MB | JPG, PNG, PDF)
                            </h3>

                            <div class="grid gap-4 md:grid-cols-2">
                                <!-- KTP -->
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-[#174413]/80">Foto KTP Pemilik <span class="text-red-500">*</span></label>
                                    <input type="file" name="document_ktp_mitra" :required="userType === 'mitra'" accept=".jpg,.jpeg,.png,.pdf"
                                           @change="validateFile($event, 'ktpError')"
                                           class="w-full bg-white/75 border border-[#174413]/15 rounded-xl p-2 text-xs file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[9px] file:font-black file:uppercase file:tracking-wider file:bg-emerald-600 file:text-white file:hover:bg-emerald-700 transition">
                                    <p x-show="ktpError" x-cloak class="text-xs font-semibold text-red-600 mt-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <span x-text="ktpError"></span>
                                    </p>
                                </div>
                                <!-- SIUP -->
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-[#174413]/80">SIUP / TDP <span class="text-red-500">*</span></label>
                                    <input type="file" name="document_siup_mitra" :required="userType === 'mitra'" accept=".jpg,.jpeg,.png,.pdf"
                                           @change="validateFile($event, 'siupError')"
                                           class="w-full bg-white/75 border border-[#174413]/15 rounded-xl p-2 text-xs file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[9px] file:font-black file:uppercase file:tracking-wider file:bg-emerald-600 file:text-white file:hover:bg-emerald-700 transition">
                                    <p x-show="siupError" x-cloak class="text-xs font-semibold text-red-600 mt-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <span x-text="siupError"></span>
                                    </p>
                                </div>
                                <!-- NIB -->
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-[#174413]/80">Nomor Induk Berusaha (NIB) <span class="text-red-500">*</span></label>
                                    <input type="file" name="document_nib_mitra" :required="userType === 'mitra'" accept=".jpg,.jpeg,.png,.pdf"
                                           @change="validateFile($event, 'nibError')"
                                           class="w-full bg-white/75 border border-[#174413]/15 rounded-xl p-2 text-xs file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[9px] file:font-black file:uppercase file:tracking-wider file:bg-emerald-600 file:text-white file:hover:bg-emerald-700 transition">
                                    <p x-show="nibError" x-cloak class="text-xs font-semibold text-red-600 mt-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <span x-text="nibError"></span>
                                    </p>
                                </div>
                                <!-- Sertifikat Halal -->
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-[#174413]/80">Sertifikat Halal <span class="text-[10px] font-normal text-emerald-800/40">(Opsional)</span></label>
                                    <input type="file" name="document_halal_mitra" accept=".jpg,.jpeg,.png,.pdf"
                                           @change="validateFile($event, 'halalError')"
                                           class="w-full bg-white/75 border border-[#174413]/15 rounded-xl p-2 text-xs file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[9px] file:font-black file:uppercase file:tracking-wider file:bg-emerald-600 file:text-white file:hover:bg-emerald-700 transition">
                                    <p x-show="halalError" x-cloak class="text-xs font-semibold text-red-600 mt-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <span x-text="halalError"></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Lembaga (NGO) Verification Document Uploads -->
                        <div x-show="userType === 'lembaga'" x-cloak class="space-y-5 border-t border-b border-[#174413]/10 py-5 my-4" x-transition>
                            <h3 class="font-extrabold text-[#174413] flex items-center gap-2 text-sm">
                                <i data-lucide="shield-check" class="w-5 h-5 text-emerald-600"></i>
                                Dokumen Legalitas Lembaga (Maks. 2 MB | JPG, PNG, PDF)
                            </h3>
                            <div class="grid gap-4">
                                <!-- Legalitas Dasar -->
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-[#174413]/80">Dokumen Legalitas Dasar <span class="text-red-500">*</span></label>
                                    <p class="text-[9px] text-[#174413]/55 -mt-1">(Akta Pendirian, SK Menkumham, dll)</p>
                                    <input type="file" name="document_legalitas_lembaga" :required="userType === 'lembaga'" accept=".jpg,.jpeg,.png,.pdf"
                                           @change="validateFile($event, 'legalitasError')"
                                           class="w-full bg-white/75 border border-[#174413]/15 rounded-xl p-2 text-xs file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[9px] file:font-black file:uppercase file:tracking-wider file:bg-emerald-600 file:text-white file:hover:bg-emerald-700 transition">
                                    <p x-show="legalitasError" x-cloak class="text-xs font-semibold text-red-600 mt-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <span x-text="legalitasError"></span>
                                    </p>
                                </div>
                                <!-- Izin Operasional -->
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-[#174413]/80">Izin Operasional & Registrasi Sosial <span class="text-red-500">*</span></label>
                                    <p class="text-[9px] text-[#174413]/55 -mt-1">(Izin LKS, Tanda Daftar Yayasan, dll)</p>
                                    <input type="file" name="document_izin_lembaga" :required="userType === 'lembaga'" accept=".jpg,.jpeg,.png,.pdf"
                                           @change="validateFile($event, 'izinError')"
                                           class="w-full bg-white/75 border border-[#174413]/15 rounded-xl p-2 text-xs file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[9px] file:font-black file:uppercase file:tracking-wider file:bg-emerald-600 file:text-white file:hover:bg-emerald-700 transition">
                                    <p x-show="izinError" x-cloak class="text-xs font-semibold text-red-600 mt-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <span x-text="izinError"></span>
                                    </p>
                                </div>
                                <!-- Identitas & Lokasi -->
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-[#174413]/80">Dokumen Identitas & Lokasi <span class="text-red-500">*</span></label>
                                    <p class="text-[9px] text-[#174413]/55 -mt-1">(KTP Pengurus, Domisili, Foto Lokasi)</p>
                                    <input type="file" name="document_identitas_lembaga" :required="userType === 'lembaga'" accept=".jpg,.jpeg,.png,.pdf"
                                           @change="validateFile($event, 'identitasError')"
                                           class="w-full bg-white/75 border border-[#174413]/15 rounded-xl p-2 text-xs file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[9px] file:font-black file:uppercase file:tracking-wider file:bg-emerald-600 file:text-white file:hover:bg-emerald-700 transition">
                                    <p x-show="identitasError" x-cloak class="text-xs font-semibold text-red-600 mt-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <span x-text="identitasError"></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Main Core Text Fields -->
                        <div class="grid gap-5">
                            <!-- Organization Name Input -->
                            <div x-show="userType === 'mitra' || userType === 'lembaga'" x-transition x-cloak>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#174413]/85"
                                       x-text="userType === 'mitra' ? 'Nama Mitra' : 'Nama Lembaga'">
                                    Nama Organisasi
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#174413]/40">
                                        <!-- Show store icon for mitra, building icon for lembaga -->
                                        <i data-lucide="store" class="w-4.5 h-4.5" x-show="userType === 'mitra'"></i>
                                        <i data-lucide="building" class="w-4.5 h-4.5" x-show="userType === 'lembaga'" x-cloak></i>
                                    </span>
                                    <input class="w-full pl-11 pr-4 py-3.5 bg-white/80 border border-[#174413]/15 rounded-2xl text-sm font-medium text-[#174413] focus:border-[#174413] focus:ring-[#174413] outline-none" 
                                           :placeholder="userType === 'mitra' ? 'Masukkan nama mitra (contoh: Dapoer Roti)' : 'Masukkan nama lembaga (contoh: Panti Asuhan)'" 
                                           type="text" 
                                           name="organization_name" 
                                           value="{{ old('organization_name') }}" 
                                           :required="userType === 'mitra' || userType === 'lembaga'"
                                           pattern="^[a-zA-Z0-9\s]+$"
                                           oninvalid="this.setCustomValidity('Nama hanya boleh berisi huruf, angka, dan spasi')"
                                           oninput="this.setCustomValidity('')">
                                </div>
                                @error('organization_name') <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p> @enderror
                            </div>

                            <!-- Name Input -->
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#174413]/85"
                                       x-text="userType === 'mitra' ? 'Nama Pemilik' : (userType === 'lembaga' ? 'Nama Pemilik/Pengurus' : 'Nama Lengkap')">
                                    Nama Lengkap
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#174413]/40">
                                        <i data-lucide="user" class="w-4.5 h-4.5"></i>
                                    </span>
                                    <input class="w-full pl-11 pr-4 py-3.5 bg-white/80 border border-[#174413]/15 rounded-2xl text-sm font-medium text-[#174413] focus:border-[#174413] focus:ring-[#174413] outline-none" 
                                           :placeholder="userType === 'mitra' ? 'Masukkan nama pemilik' : (userType === 'lembaga' ? 'Masukkan nama pemilik/pengurus' : 'Masukkan nama lengkap')" 
                                           type="text" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           required
                                           pattern="[a-zA-Z\s]+"
                                           oninvalid="this.setCustomValidity('Nama hanya boleh berisi huruf dan spasi')"
                                           oninput="this.setCustomValidity(''); this.value = this.value.replace(/[0-9]/g, '')">
                                </div>
                                @error('name') <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p> @enderror
                            </div>

                            <!-- Email Input -->
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#174413]/85">Email</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#174413]/40">
                                        <i data-lucide="mail" class="w-4.5 h-4.5"></i>
                                    </span>
                                    <input class="w-full pl-11 pr-4 py-3.5 bg-white/80 border border-[#174413]/15 rounded-2xl text-sm font-medium text-[#174413] focus:border-[#174413] focus:ring-[#174413] outline-none" 
                                           placeholder="Masukkan email aktif" 
                                           type="email" name="email" value="{{ old('email') }}" required
                                           @input="emailError = $el.validity.typeMismatch ? 'Format email tidak valid (harus mengandung @)' : ''">
                                </div>
                                <p x-show="emailError" x-cloak class="text-xs font-semibold text-red-600 mt-1.5 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span x-text="emailError"></span>
                                </p>
                                @error('email') <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p> @enderror
                            </div>

                            <!-- Password Input -->
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#174413]/85">Kata Sandi</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#174413]/40">
                                        <i data-lucide="lock" class="w-4.5 h-4.5"></i>
                                    </span>
                                    <input class="w-full pl-11 pr-12 py-3.5 bg-white/80 border border-[#174413]/15 rounded-2xl text-sm font-medium text-[#174413] focus:border-[#174413] focus:ring-[#174413] outline-none" 
                                           placeholder="Buat kata sandi minimal 8 karakter" 
                                           :type="showPassword ? 'text' : 'password'" name="password" required minlength="8"
                                           @input="passwordError = $el.value.length < 8 ? 'Kata sandi minimal harus 8 karakter' : ''">
                                    
                                    <button type="button" @click="showPassword = !showPassword" class="absolute right-3.5 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-xl hover:bg-emerald-50 text-emerald-800/40 hover:text-[#174413] transition" :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Lihat kata sandi'">
                                        <i data-lucide="eye" class="h-4.5 w-4.5" x-show="!showPassword"></i>
                                        <i data-lucide="eye-off" class="h-4.5 w-4.5" x-show="showPassword" x-cloak></i>
                                    </button>
                                </div>
                                <p x-show="passwordError" x-cloak class="text-xs font-semibold text-red-600 mt-1.5 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span x-text="passwordError"></span>
                                </p>
                                @error('password') <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p> @enderror
                            </div>

                            <!-- Confirm Password Input -->
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#174413]/85">Konfirmasi Kata Sandi</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#174413]/40">
                                        <i data-lucide="lock-keyhole" class="w-4.5 h-4.5"></i>
                                    </span>
                                    <input class="w-full pl-11 pr-12 py-3.5 bg-white/80 border border-[#174413]/15 rounded-2xl text-sm font-medium text-[#174413] focus:border-[#174413] focus:ring-[#174413] outline-none" 
                                           placeholder="Ketik ulang kata sandi Anda" 
                                           :type="showPasswordConfirmation ? 'text' : 'password'" name="password_confirmation" required>
                                    
                                    <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation" class="absolute right-3.5 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-xl hover:bg-emerald-50 text-emerald-800/40 hover:text-[#174413] transition" :aria-label="showPasswordConfirmation ? 'Sembunyikan konfirmasi kata sandi' : 'Lihat konfirmasi kata sandi'">
                                        <i data-lucide="eye" class="h-4.5 w-4.5" x-show="!showPasswordConfirmation"></i>
                                        <i data-lucide="eye-off" class="h-4.5 w-4.5" x-show="showPasswordConfirmation" x-cloak></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Terms & Conditions Checkbox -->
                        <div>
                            <label class="flex items-start gap-2.5 cursor-pointer select-none text-sm text-[#174413]/75 font-semibold">
                                <input type="checkbox" id="terms_checkbox" name="terms" value="1" 
                                       @change="termsError = $el.checked ? '' : 'Anda harus menyetujui Syarat & Ketentuan serta Kebijakan Privasi untuk melanjutkan.'"
                                       class="mt-1 h-4.5 w-4.5 rounded-lg border-emerald-800/20 bg-white text-emerald-600 focus:ring-emerald-500/20 focus:ring-offset-0">
                                <span class="leading-normal">Saya menyetujui <span class="font-bold text-emerald-700 hover:underline">Syarat & Ketentuan</span> serta <span class="font-bold text-emerald-700 hover:underline">Kebijakan Privasi</span> yang berlaku di ShareMeal.</span>
                            </label>
                            
                            <!-- Custom colored validation alert banner -->
                            <div x-show="termsError" x-cloak 
                                 class="mt-3 p-3.5 bg-red-50/80 backdrop-blur-md border border-red-200/50 rounded-2xl flex items-start gap-2.5 text-xs font-semibold text-red-700 animate-pulse">
                                <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span x-text="termsError"></span>
                            </div>
                            
                            @error('terms') <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full py-4 bg-[#174413] hover:bg-[#1a5017] text-white font-extrabold text-sm uppercase tracking-widest rounded-2xl transition-all duration-300 hover:scale-[1.02] active:scale-95 shadow-md hover:shadow-lg shadow-[#174413]/10">
                            Daftar Sekarang
                        </button>
                    </form>

                    <p class="mt-8 text-center text-sm text-[#174413]/70 font-semibold">
                        Sudah punya akun? <a href="{{ route('login') }}" class="text-emerald-700 hover:text-emerald-800 hover:underline transition">Masuk ke sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
