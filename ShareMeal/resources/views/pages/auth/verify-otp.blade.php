<x-layouts.app title="Verifikasi OTP Lupa Sandi - ShareMeal">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=300;400;500;600;700;800&display=swap');
        
        .login-font {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 20px 50px -15px rgba(23, 68, 19, 0.05);
        }

        .glass-card-dark {
            background: rgba(16, 44, 13, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
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

        <!-- Left Column: Elegant Visual Hero Panel -->
        <div class="relative hidden overflow-hidden lg:flex flex-col justify-between p-16 z-10">
            <div class="absolute inset-0 z-0">
                <img src="/images/logo2.png" alt="ShareMeal Marketplace" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-tr from-[#0b240a]/95 via-[#0e350b]/80 to-[#1b5017]/35 mix-blend-multiply"></div>
            </div>

            <div class="relative z-10">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                    <img src="{{ asset('images/logo.png') }}" class="h-12 w-12 object-cover rounded-full transition-transform group-hover:scale-105" alt="ShareMeal Logo">
                    <span class="text-3xl font-extrabold text-white tracking-tight">ShareMeal</span>
                </a>
            </div>

            <div class="relative z-10 glass-card-dark max-w-lg p-8 rounded-[2rem] text-white">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-400/20 text-emerald-300 border border-emerald-400/30">
                        <i data-lucide="key-round" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-300">Verifikasi Akun</span>
                </div>
                <h3 class="text-2xl font-bold tracking-tight leading-snug">Satu Langkah Lagi</h3>
                <p class="mt-3 text-sm leading-relaxed text-emerald-100/75">Masukkan kode OTP 6-digit untuk memvalidasi kepemilihan akun Anda sebelum melanjutkan proses penyetelan ulang kata sandi.</p>
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
