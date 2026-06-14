<x-layouts.app title="Lupa Kata Sandi - ShareMeal">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=300;400;500;600;700;800&display=swap');
        
        .login-font {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        
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
        
        .animate-float-1 { animation: float-1 22s infinite alternate ease-in-out; }
        .animate-float-2 { animation: float-2 25s infinite alternate ease-in-out; }
        .animate-float-3 { animation: float-3 24s infinite alternate ease-in-out; }

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

    <div class="login-font grid min-h-screen lg:grid-cols-2 relative bg-[#f4f7f4] overflow-hidden">
        <!-- Animated Background Blobs -->
        <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-[10%] -left-[10%] w-[50%] h-[50%] rounded-full bg-emerald-200/40 blur-[120px] animate-float-1"></div>
            <div class="absolute -bottom-[10%] -right-[10%] w-[60%] h-[60%] rounded-full bg-teal-200/45 blur-[130px] animate-float-2"></div>
            <div class="absolute top-[40%] left-[30%] w-[35%] h-[35%] rounded-full bg-green-200/35 blur-[100px] animate-float-3"></div>
        </div>

        {{-- Falling Leaves Effect --}}
        <x-falling-leaves />

        <!-- Left Column: Elegant Visual Hero Panel (Desktop) -->
        <div class="relative hidden overflow-hidden lg:flex flex-col justify-between p-12 z-10">
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
            </div>

            <!-- MIDDLE: Security Center Mockup -->
            <div class="relative z-10 my-auto py-6 flex flex-col items-center">
                <!-- Text Intro -->
                <div class="w-full max-w-md mb-6 text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-emerald-600/20 badge-shimmer mb-3">
                        <span class="text-[9px] font-bold uppercase tracking-[0.15em] text-emerald-800">Proteksi Keamanan Akun</span>
                    </div>
                    <h2 class="text-[2.2rem] font-extrabold leading-[1.15] text-[#174413] tracking-tight">
                        Pemulihan Akses<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-700 via-teal-700 to-green-700">Secara Terenkripsi</span>
                    </h2>
                </div>

                <!-- Main Floating Card Mockup -->
                <div class="w-full max-w-md bg-white/90 border border-emerald-100/90 rounded-2xl shadow-2xl p-5 backdrop-blur-md relative animate-float-dashboard">
                    <!-- Browser Window Control Dots -->
                    <div class="flex items-center gap-1.5 mb-4">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-400/80"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400/80"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-400/80"></span>
                        <span class="text-[10px] text-[#174413]/40 font-bold ml-2 tracking-wider">pusat_keamanan.html</span>
                    </div>

                    <!-- Shield & Checklist Container -->
                    <div class="space-y-4">
                        <!-- Shield Header with pulsing effect -->
                        <div class="flex items-center gap-4 p-3 bg-emerald-50/50 border border-emerald-100/50 rounded-xl">
                            <div class="h-10 w-10 rounded-full bg-emerald-600 flex items-center justify-center text-white shadow-md relative">
                                <span class="absolute inset-0 rounded-full bg-emerald-500 animate-ping opacity-25"></span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-[#174413]">Sistem Keamanan Akun</h4>
                                <p class="text-[9px] text-emerald-800 font-semibold">Data Terlindungi &amp; OTP Aktif</p>
                            </div>
                        </div>

                        <!-- Verification Checklist -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between p-2 rounded-lg bg-emerald-50/30 border border-emerald-100/30 text-xs">
                                <div class="flex items-center gap-2.5 text-[#174413]">
                                    <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    <span class="font-extrabold text-[#174413]/85">Protokol SSL/TLS Terlindungi</span>
                                </div>
                                <span class="text-[8px] font-black px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">AKTIF</span>
                            </div>
                            <div class="flex items-center justify-between p-2 rounded-lg bg-emerald-50/30 border border-emerald-100/30 text-xs">
                                <div class="flex items-center gap-2.5 text-[#174413]">
                                    <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    <span class="font-extrabold text-[#174413]/85">Autentikasi OTP Dinamis</span>
                                </div>
                                <span class="text-[8px] font-black px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">SIAGA</span>
                            </div>
                        </div>
                    </div>

                    <!-- Floating Badge: SMS/Push Notification OTP simulator -->
                    <div class="absolute -right-10 -bottom-6 bg-white border border-emerald-100 rounded-2xl p-3 shadow-xl flex items-start gap-3 w-[220px] animate-float-badge-up backdrop-blur-md">
                        <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </div>
                        <div>
                            <span class="block text-[8px] font-extrabold uppercase tracking-wider text-emerald-800/50">SMS Kode OTP</span>
                            <p class="text-[10px] text-[#174413]/70 font-semibold mt-0.5 leading-snug">Kode OTP ShareMeal Anda: <span class="font-extrabold text-[#174413]">954201</span>.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOTTOM: Security tip card -->
            <div class="relative z-10 w-full max-w-md mx-auto">
                <div class="glass-card rounded-[1.5rem] p-4 text-center">
                    <p class="text-xs text-[#174413]/80 leading-relaxed font-semibold">
                        🔒 Pemulihan akun Anda diproteksi dengan verifikasi berlapis.
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Column: Form -->
        <div class="flex items-center justify-center px-6 py-12 lg:px-16 z-10 relative">
            <div class="w-full max-w-lg">
                <div class="mb-8 lg:hidden">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                        <img src="{{ asset('images/logo.png') }}" class="h-9 w-9 object-cover rounded-full" alt="ShareMeal Logo">
                        <span class="text-2xl font-extrabold text-[#174413] tracking-tight">ShareMeal</span>
                    </a>
                </div>

                <div class="glass-card p-8 sm:p-10 rounded-[2.5rem]">
                    <div class="mb-8">
                        <h1 class="text-3xl font-extrabold text-[#174413] tracking-tight">Lupa Sandi?</h1>
                        <p class="mt-2 text-sm text-[#174413]/70">Jangan khawatir. Masukkan email Anda di bawah untuk menerima kode verifikasi OTP.</p>
                    </div>



                    @if (session('error'))
                        <div class="mb-6 rounded-2xl bg-red-50/70 backdrop-blur-md p-4 border border-red-200/50">
                            <p class="text-xs text-red-700 font-semibold">{{ session('error') }}</p>
                        </div>
                    @endif

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

                    <form method="post" action="{{ route('password.email') }}" class="space-y-5">
                        @csrf
                        
                        <!-- Role Selector -->
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#174413]/85">Tipe Pengguna</label>
                            <div class="relative">
                                <select name="user_type" class="w-full pl-4 pr-10 py-3.5 bg-white/80 border border-[#174413]/15 rounded-2xl text-sm font-semibold text-[#174413] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none appearance-none cursor-pointer">
                                    <option value="consumer" {{ old('user_type') == 'consumer' ? 'selected' : '' }}>Konsumen (Masyarakat)</option>
                                    <option value="mitra" {{ old('user_type') == 'mitra' ? 'selected' : '' }}>Mitra (Merchant/Toko)</option>
                                    <option value="lembaga" {{ old('user_type') == 'lembaga' ? 'selected' : '' }}>Lembaga Sosial (NGO)</option>
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-[#174413]/55">
                                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Email Input -->
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#174413]/85">Alamat Email Terdaftar</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#174413]/40">
                                    <i data-lucide="mail" class="w-4.5 h-4.5"></i>
                                </span>
                                <input class="w-full pl-11 pr-4 py-3.5 bg-white/80 border border-[#174413]/15 rounded-2xl text-sm font-medium text-[#174413] placeholder="nama@email.com" required 
                                       type="email" name="email" value="{{ old('email') }}">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm uppercase tracking-widest rounded-2xl transition-all duration-300 hover:scale-[1.02] active:scale-95 shadow-md hover:shadow-lg shadow-emerald-600/10">
                            Kirim OTP Pemulihan
                        </button>
                    </form>

                    <p class="mt-8 text-center text-sm text-[#174413]/70 font-semibold">
                        Kembali ke halaman <a href="{{ route('login') }}" class="text-emerald-700 hover:text-emerald-800 hover:underline transition">Masuk</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
