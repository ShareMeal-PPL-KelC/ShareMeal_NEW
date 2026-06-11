<x-layouts.app title="Verifikasi OTP Lupa Sandi - ShareMeal">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=300;400;500;600;700;800&display=swap');
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

        /* Float Animations */
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

        /* Mockup Animations */
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
        .animate-float-dashboard { animation: float-dashboard 7s infinite ease-in-out; }
        .animate-float-badge-up { animation: float-badge-up 6s infinite ease-in-out; }
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
                <img src="/images/logo2.png" alt="ShareMeal" class="absolute inset-0 h-full w-full object-cover object-center opacity-80">
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
            </div>

            <!-- MIDDLE: OTP Verification Mockup -->
            <div class="relative z-10 my-auto py-6 flex flex-col items-center">
                <!-- Text Intro -->
                <div class="w-full max-w-md mb-6 text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-emerald-600/20 badge-shimmer mb-3">
                        <span class="text-[9px] font-bold uppercase tracking-[0.15em] text-emerald-800">Verifikasi Keamanan</span>
                    </div>
                    <h2 class="text-[2.2rem] font-extrabold leading-[1.15] text-[#174413] tracking-tight">
                        Satu Langkah Lagi<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-700 via-teal-700 to-green-700">Validasi Akses Anda</span>
                    </h2>
                </div>

                <!-- Main Floating Mockup Card -->
                <div class="w-full max-w-md bg-white/90 border border-emerald-100/90 rounded-2xl shadow-2xl p-5 backdrop-blur-md relative animate-float-dashboard">
                    <!-- Browser Window Control Dots -->
                    <div class="flex items-center gap-1.5 mb-4">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-400/80"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400/80"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-400/80"></span>
                        <span class="text-[10px] text-[#174413]/40 font-bold ml-2 tracking-wider">otp_authenticator.html</span>
                    </div>

                    <div class="space-y-4">
                        <!-- Code digit slots simulator -->
                        <div class="flex justify-center gap-2.5 my-3">
                            <div class="w-10 h-12 rounded-xl border-2 border-emerald-600 bg-emerald-50/20 flex items-center justify-center text-lg font-black text-[#174413]">9</div>
                            <div class="w-10 h-12 rounded-xl border-2 border-emerald-600 bg-emerald-50/20 flex items-center justify-center text-lg font-black text-[#174413]">5</div>
                            <div class="w-10 h-12 rounded-xl border-2 border-emerald-600 bg-emerald-50/20 flex items-center justify-center text-lg font-black text-[#174413]">4</div>
                            <div class="w-10 h-12 rounded-xl border-2 border-emerald-600 bg-emerald-50/20 flex items-center justify-center text-lg font-black text-[#174413]">2</div>
                            <div class="w-10 h-12 rounded-xl border-2 border-emerald-600 bg-emerald-50/20 flex items-center justify-center text-lg font-black text-[#174413]">0</div>
                            <div class="w-10 h-12 rounded-xl border-2 border-emerald-600 bg-emerald-50/20 flex items-center justify-center text-lg font-black text-[#174413]">1</div>
                        </div>

                        <!-- Pulsing Success Banner -->
                        <div class="flex items-center gap-3 p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl animate-pulse">
                            <div class="h-6 w-6 rounded-full bg-emerald-600 flex items-center justify-center text-white text-xs font-bold">
                                ✓
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-[#174413]">Verifikasi Sukses</h4>
                                <p class="text-[9px] text-emerald-800 font-semibold">Identitas berhasil dikonfirmasi.</p>
                            </div>
                        </div>

                        <!-- Time countdown inside simulator -->
                        <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl text-center">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Waktu Sisa Kode</span>
                            <span class="text-sm font-black text-slate-700">04:59</span>
                        </div>
                    </div>

                    <!-- Floating Badge: Akses Terbuka -->
                    <div class="absolute -right-10 -bottom-6 bg-gradient-to-r from-emerald-600 to-teal-600 text-white border border-emerald-400/20 px-3.5 py-2 rounded-2xl shadow-xl flex items-center gap-2.5 text-xs font-black animate-float-badge-up backdrop-blur-md">
                        <div class="h-7 w-7 rounded-lg bg-white/20 flex items-center justify-center text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h16.5a1.5 1.5 0 001.5-1.5V13.5a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 13.5v6.75a1.5 1.5 0 001.5 1.5z"/></svg>
                        </div>
                        <div>
                            <span class="block text-[8px] uppercase tracking-wider text-emerald-200">Status Akses</span>
                            <span>Akses Terbuka</span>
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
                <div class="glass-card p-8 sm:p-10 rounded-[2.5rem]">
                    <div class="mb-8">
                        <h1 class="text-3xl font-extrabold text-[#174413] tracking-tight">Verifikasi OTP</h1>
                        <p class="mt-2 text-sm text-[#174413]/70">Masukkan kode verifikasi OTP yang telah dikirimkan ke alamat email Anda.</p>
                    </div>

                    <!-- Demo OTP display for local testing/grading -->
                    @if (session('demo_reset_otp'))
                        <div class="mb-6 rounded-2xl bg-amber-50/90 border border-amber-200/80 p-4 text-xs text-amber-900 font-bold">
                            ⚠️ [DEMO MODE] Kode OTP Anda: <span class="bg-amber-100 px-2 py-0.5 rounded text-sm tracking-widest font-mono text-amber-950">{{ session('demo_reset_otp') }}</span>
                        </div>
                    @endif



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

                    <form method="post" action="{{ route('password.verify_otp') }}" class="space-y-5">
                        @csrf
                        
                        <!-- Hidden Context Inputs -->
                        <input type="hidden" name="email" value="{{ $email }}">
                        <input type="hidden" name="user_type" value="{{ $user_type }}">

                        <!-- Context info display -->
                        <div class="p-4 mb-3 rounded-2xl bg-slate-100/60 border border-slate-200/40 text-xs font-semibold text-[#174413]/80 space-y-1">
                            <div>Email: <span class="font-bold text-slate-800">{{ $email }}</span></div>
                            <div>Tipe: <span class="font-bold text-slate-800 capitalize">{{ $user_type }}</span></div>
                        </div>

                        <!-- OTP Input -->
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#174413]/85">Kode OTP 6-Digit</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#174413]/40">
                                    <i data-lucide="key" class="w-4.5 h-4.5"></i>
                                </span>
                                <input class="w-full pl-11 pr-4 py-3.5 bg-white/80 border border-[#174413]/15 rounded-2xl text-sm font-black tracking-[0.2em] font-mono text-center text-[#174413] placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none" 
                                       type="text" name="otp" maxlength="6" placeholder="000000" required autocomplete="off">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm uppercase tracking-widest rounded-2xl transition-all duration-300 hover:scale-[1.02] active:scale-95 shadow-md hover:shadow-lg shadow-emerald-600/10">
                            Verifikasi OTP
                        </button>
                    </form>

                    <p class="mt-8 text-center text-sm text-[#174413]/70 font-semibold">
                        Kembali ke halaman <a href="{{ route('password.request') }}" class="text-emerald-700 hover:text-emerald-800 hover:underline transition">Lupa Sandi</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
