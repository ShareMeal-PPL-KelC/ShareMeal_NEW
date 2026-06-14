<x-layouts.app title="Masuk - ShareMeal">
    <!-- Style & Google Fonts Import -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        .login-font { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }

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

        /* Right panel blobs */
        @keyframes float-1 { 0%{transform:translate(0,0) scale(1)} 33%{transform:translate(40px,-60px) scale(1.1)} 66%{transform:translate(-30px,30px) scale(0.95)} 100%{transform:translate(0,0) scale(1)} }
        @keyframes float-2 { 0%{transform:translate(0,0) scale(1)} 33%{transform:translate(-50px,40px) scale(0.95)} 66%{transform:translate(40px,-40px) scale(1.15)} 100%{transform:translate(0,0) scale(1)} }
        @keyframes float-3 { 0%{transform:translate(0,0) scale(1)} 33%{transform:translate(40px,50px) scale(1.08)} 66%{transform:translate(-40px,-40px) scale(0.92)} 100%{transform:translate(0,0) scale(1)} }
        .animate-float-1 { animation: float-1 22s infinite alternate ease-in-out; }
        .animate-float-2 { animation: float-2 25s infinite alternate ease-in-out; }
        .animate-float-3 { animation: float-3 24s infinite alternate ease-in-out; }

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
            background: radial-gradient(circle, rgba(52,211,153,.55) 0%, rgba(16,185,129,.18) 50%, transparent 75%);
            filter: blur(60px);
            animation: aurora-1 18s infinite ease-in-out;
            pointer-events:none;
        }
        .aurora-orb-2 {
            position:absolute; width:360px; height:360px; border-radius:50%;
            background: radial-gradient(circle, rgba(52,211,153,.35) 0%, rgba(16,185,129,.1) 50%, transparent 75%);
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

        /* Dashboard Float Animations */
        @keyframes float-dashboard {
            0% { transform: translateY(0px) rotate(0.2deg); }
            50% { transform: translateY(-10px) rotate(-0.2deg); }
            100% { transform: translateY(0px) rotate(0.2deg); }
        }
        @keyframes float-badge-up {
            0% { transform: translateY(0px) translateX(0px); }
            50% { transform: translateY(-14px) translateX(4px); }
            100% { transform: translateY(0px) translateX(0px); }
        }
        .animate-float-dashboard {
            animation: float-dashboard 7s infinite ease-in-out;
        }
        .animate-float-badge-up {
            animation: float-badge-up 6s infinite ease-in-out;
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

        <!-- Left Column: Cinematic Hero Panel (Desktop) -->
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

            <!-- TOP: Logo & Slogan -->
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
                    <span>1,240 Ton Penyelamatan Pangan</span>
                </div>
            </div>

            <!-- MIDDLE: Live Dashboard Mockup Preview -->
            <div class="relative z-10 my-auto py-6 flex flex-col items-center">
                <!-- Text Intro -->
                <div class="w-full max-w-md mb-6 text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-emerald-600/20 badge-shimmer mb-3">
                        <span class="text-[9px] font-bold uppercase tracking-[0.15em] text-emerald-800">Dampak Nyata Komunitas</span>
                    </div>
                    <h2 class="text-[2.2rem] font-extrabold leading-[1.15] text-[#174413] tracking-tight">
                        Pantau &amp; Bagikan<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-700 via-teal-700 to-green-700">Surplus Makanan Anda</span>
                    </h2>
                </div>

                <!-- Main Floating Dashboard Card Mockup -->
                <div class="w-full max-w-md bg-white/90 border border-emerald-100/90 rounded-2xl shadow-2xl p-5 backdrop-blur-md relative animate-float-dashboard">
                    <!-- Browser Window Control Dots -->
                    <div class="flex items-center gap-1.5 mb-4">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-400/80"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400/80"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-400/80"></span>
                        <span class="text-[10px] text-[#174413]/40 font-bold ml-2 tracking-wider">dashboard_berbagi.html</span>
                    </div>

                    <!-- Profile and Impact Widget -->
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-emerald-100/50">
                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-600 flex items-center justify-center text-white font-extrabold text-sm shadow-md">
                                M
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-[#174413]">Mitra Toko Hijau</h4>
                                <p class="text-[9px] text-emerald-700 font-semibold uppercase tracking-wider">Level 3 Penyelamat</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-850">Aktif</span>
                    </div>

                    <!-- Stats Row -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-gradient-to-br from-emerald-500/5 to-emerald-500/10 border border-emerald-500/15 rounded-xl p-3">
                            <div class="flex items-center justify-between text-emerald-800">
                                <span class="text-[9px] font-bold uppercase tracking-wider text-emerald-800/60">Dampak CO₂</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
                            </div>
                            <div class="mt-1 text-lg font-black text-[#174413] tracking-tight">124.5 kg</div>
                            <div class="text-[9px] font-semibold text-emerald-700/80 mt-0.5">Setara 8 Pohon Tumbuh</div>
                        </div>
                        <div class="bg-gradient-to-br from-teal-500/5 to-teal-500/10 border border-teal-500/15 rounded-xl p-3">
                            <div class="flex items-center justify-between text-teal-800">
                                <span class="text-[9px] font-bold uppercase tracking-wider text-teal-800/60">Porsi Berbagi</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"/></svg>
                            </div>
                            <div class="mt-1 text-lg font-black text-[#174413] tracking-tight">87 Porsi</div>
                            <div class="text-[9px] font-semibold text-teal-700/80 mt-0.5">Diselamatkan Bulan Ini</div>
                        </div>
                    </div>

                    <!-- Live Feed / Recent Activity -->
                    <div>
                        <h5 class="text-[10px] font-extrabold text-[#174413] uppercase tracking-wider mb-2.5 flex items-center justify-between">
                            <span>Aktivitas Surplus Terbaru</span>
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500 animate-pulse"></span>
                        </h5>
                        <div class="space-y-2">
                            <!-- Item 1 -->
                            <div class="flex items-center justify-between p-2 rounded-lg bg-emerald-50/40 border border-emerald-100/30 text-xs">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <div>
                                        <span class="font-extrabold text-[#174413]">Roti Manis Cokelat</span>
                                        <span class="text-[9px] text-[#174413]/50 block">5 Porsi · Bakery Delight</span>
                                    </div>
                                </div>
                                <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800">Menunggu Kurir</span>
                            </div>
                            <!-- Item 2 -->
                            <div class="flex items-center justify-between p-2 rounded-lg bg-emerald-50/40 border border-emerald-100/30 text-xs">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <div>
                                        <span class="font-extrabold text-[#174413]">Surplus Buffet Makan Siang</span>
                                        <span class="text-[9px] text-[#174413]/50 block">12 Porsi · Hotel Nusantara</span>
                                    </div>
                                </div>
                                <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-800">Selesai Disalurkan</span>
                            </div>
                        </div>
                    </div>

                    <!-- Floating Reward Badge decoration -->
                    <div class="absolute -right-10 -bottom-6 bg-gradient-to-r from-emerald-600 to-teal-600 text-white border border-emerald-400/20 px-3.5 py-2 rounded-2xl shadow-xl flex items-center gap-2 text-xs font-black animate-float-badge-up backdrop-blur-md">
                        <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26 6.91.97-5 4.89 1.18 6.88L12 17.77l-6.18 3.25L7 14.12 2 9.23l6.91-.97L12 2z"/></svg>
                        <div>
                            <span class="block text-[8px] uppercase tracking-wider text-emerald-200">Bonus Keberlanjutan</span>
                            <span>+10 Poin Hijau</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOTTOM: Dynamic micro banner -->
            <div class="relative z-10 w-full max-w-md mx-auto">
                <div class="glass-card rounded-[1.5rem] p-4 text-center">
                    <p class="text-xs text-[#174413]/80 leading-relaxed font-semibold">
                        🌱 Bergabunglah untuk melihat dampak ekologi Anda secara langsung.
                    </p>
                </div>
            </div>

        </div>

        <!-- Right Column: Translucent Form Wrapper -->
        <div class="flex items-center justify-center px-6 py-12 lg:px-16 z-10 relative">
            <div class="w-full max-w-lg">
                <!-- Mobile Logo Header -->
                <div class="mb-8 lg:hidden">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                        <img src="{{ asset('images/logo.png') }}" class="h-9 w-9 object-cover rounded-full" alt="ShareMeal Logo">
                        <span class="text-2xl font-extrabold text-[#174413] tracking-tight">ShareMeal</span>
                    </a>
                </div>

                <!-- Form Card -->
                <div class="glass-card p-8 sm:p-10 rounded-[2.5rem]">
                    <div class="mb-8">
                        <h1 class="text-3xl font-extrabold text-[#174413] tracking-tight">Selamat Datang</h1>
                        <p class="mt-2 text-sm text-[#174413]/70">Silakan masuk untuk melanjutkan perjalanan keberlanjutan Anda.</p>
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

                    <form method="post" action="{{ route('login.submit') }}" class="space-y-5" x-data="{ showPassword: false }">
                        @csrf
                        
                        <!-- Role Selector -->
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#174413]/85">Tipe Pengguna</label>
                            <div class="relative">
                                <select name="user_type" class="w-full pl-4 pr-10 py-3.5 bg-white/80 border border-[#174413]/15 rounded-2xl text-sm font-semibold text-[#174413] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none appearance-none cursor-pointer">
                                    <option value="consumer" {{ old('user_type') == 'consumer' ? 'selected' : '' }}>Konsumen (Masyarakat)</option>
                                    <option value="mitra" {{ old('user_type') == 'mitra' ? 'selected' : '' }}>Mitra (Merchant/Toko)</option>
                                    <option value="lembaga" {{ old('user_type') == 'lembaga' ? 'selected' : '' }}>Lembaga Sosial (NGO)</option>
                                    <option value="admin" {{ old('user_type') == 'admin' ? 'selected' : '' }}>Administrator</option>
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-[#174413]/55">
                                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                </div>
                            </div>
                            @error('user_type') <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <!-- Email Input -->
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#174413]/85">Alamat Email</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#174413]/40">
                                    <i data-lucide="mail" class="w-4.5 h-4.5"></i>
                                </span>
                                <input class="w-full pl-11 pr-4 py-3.5 bg-white/80 border border-[#174413]/15 rounded-2xl text-sm font-medium text-[#174413] placeholder-[#174413]/35 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none @error('email') border-red-400 @enderror" 
                                       type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required>
                            </div>
                            @error('email') <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <!-- Password Input -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#174413]/85">Kata Sandi</label>
                                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 transition">Lupa sandi?</a>
                            </div>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#174413]/40">
                                    <i data-lucide="lock" class="w-4.5 h-4.5"></i>
                                </span>
                                <input class="w-full pl-11 pr-12 py-3.5 bg-white/80 border border-[#174413]/15 rounded-2xl text-sm font-medium text-[#174413] placeholder-emerald-800/25 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none @error('password') border-red-400 @enderror" 
                                       :type="showPassword ? 'text' : 'password'" name="password" placeholder="••••••••" required>
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3.5 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-xl hover:bg-emerald-50 text-emerald-800/40 hover:text-[#174413] transition" :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Lihat kata sandi'">
                                    <i data-lucide="eye" class="h-4.5 w-4.5" x-show="!showPassword"></i>
                                    <i data-lucide="eye-off" class="h-4.5 w-4.5" x-show="showPassword" x-cloak></i>
                                </button>
                            </div>
                            @error('password') <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center">
                            <label class="flex items-center gap-2.5 cursor-pointer select-none text-sm text-[#174413]/75 font-semibold">
                                <input type="checkbox" name="remember" class="h-4.5 w-4.5 rounded-lg border-emerald-800/20 bg-white text-emerald-600 focus:ring-emerald-500/20 focus:ring-offset-0">
                                <span>Ingat Saya</span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm uppercase tracking-widest rounded-2xl transition-all duration-300 hover:scale-[1.02] active:scale-95 shadow-md hover:shadow-lg shadow-emerald-600/10">
                            Masuk
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="my-6 flex items-center gap-3">
                        <div class="h-px flex-1 bg-emerald-800/10"></div>
                        <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#174413]/40">Atau Masuk Dengan</div>
                        <div class="h-px flex-1 bg-emerald-800/10"></div>
                    </div>

                    <!-- Social Buttons -->
                    <div class="grid grid-cols-2 gap-3.5">
                        <button type="button" class="flex items-center justify-center gap-2 px-4 py-3 bg-white/70 hover:bg-white border border-[#174413]/10 hover:border-emerald-600/25 rounded-2xl text-xs font-bold text-[#174413] transition-all duration-300 hover:scale-[1.01]">
                            <svg class="w-4 h-4" viewBox="0 0 24 24">
                                <path fill="#EA4335" d="M12 5.04c1.66 0 3.2.57 4.38 1.69l3.27-3.27C17.67 1.58 14.98 1 12 1 7.35 1 3.37 3.65 1.48 7.5l3.85 2.99c.9-2.7 3.4-4.45 6.67-4.45z"/>
                                <path fill="#4285F4" d="M23.49 12.27c0-.8-.07-1.56-.2-2.27H12v4.51h6.45c-.28 1.46-1.11 2.69-2.35 3.52l3.65 2.83c2.14-1.97 3.39-4.88 3.39-8.59z"/>
                                <path fill="#FBBC05" d="M5.33 10.49c-.24-.72-.38-1.49-.38-2.29s.14-1.57.38-2.29L1.48 2.92C.54 4.81 0 6.94 0 9.2s.54 4.39 1.48 6.28l3.85-2.99z"/>
                                <path fill="#34A853" d="M12 23c3.24 0 5.97-1.07 7.96-2.92l-3.65-2.83c-1.01.67-2.3 1.07-4.31 1.07-3.27 0-5.77-1.75-6.67-4.45L1.48 16.86C3.37 20.71 7.35 23 12 23z"/>
                            </svg>
                            <span>Google</span>
                        </button>
                        <button type="button" class="flex items-center justify-center gap-2 px-4 py-3 bg-white/70 hover:bg-white border border-[#174413]/10 hover:border-emerald-600/25 rounded-2xl text-xs font-bold text-[#174413] transition-all duration-300 hover:scale-[1.01]">
                            <svg class="w-4 h-4 fill-[#174413]" viewBox="0 0 24 24">
                                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 4.17c.66-.81 1.11-1.93.99-3.06-1 .04-2.21.67-2.93 1.49-.62.69-1.16 1.84-1.01 2.96 1.12.09 2.27-.57 2.95-1.39z"/>
                            </svg>
                            <span>Apple</span>
                        </button>
                    </div>

                    <p class="mt-8 text-center text-sm text-[#174413]/70 font-semibold">
                        Belum punya akun? <a href="{{ route('register') }}" class="text-emerald-700 hover:text-emerald-800 hover:underline transition">Daftar sekarang</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
