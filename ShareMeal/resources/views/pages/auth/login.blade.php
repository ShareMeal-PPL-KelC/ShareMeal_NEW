<x-layouts.app title="Masuk - ShareMeal">
    <!-- Style & Google Fonts Import -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        
        .login-font {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        
        /* Glassmorphism Cards */
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
    </style>

    <div class="login-font grid min-h-screen lg:grid-cols-2 relative bg-[#f4f7f4] overflow-hidden">
        
        <!-- Animated Background Blobs -->
        <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-[10%] -left-[10%] w-[50%] h-[50%] rounded-full bg-emerald-200/40 blur-[120px] animate-float-1"></div>
            <div class="absolute -bottom-[10%] -right-[10%] w-[60%] h-[60%] rounded-full bg-teal-200/45 blur-[130px] animate-float-2"></div>
            <div class="absolute top-[40%] left-[30%] w-[35%] h-[35%] rounded-full bg-green-200/35 blur-[100px] animate-float-3"></div>
        </div>

        <!-- Left Column: Elegant Visual Hero Panel (Desktop) -->
        <div class="relative hidden overflow-hidden lg:flex flex-col justify-between p-16 z-10">
            <!-- Blur overlay and image -->
            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&q=80&w=1200" alt="ShareMeal Marketplace" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-tr from-[#0b240a]/95 via-[#0e350b]/80 to-[#1b5017]/35 mix-blend-multiply"></div>
            </div>

            <!-- Top Header Logo -->
            <div class="relative z-10">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                    <img src="{{ asset('images/logo.png') }}" class="h-12 w-12 object-cover rounded-full transition-transform group-hover:scale-105" alt="ShareMeal Logo">
                    <span class="text-3xl font-extrabold text-white tracking-tight">ShareMeal</span>
                </a>
                <p class="mt-4 max-w-sm text-sm text-emerald-100/80 leading-relaxed">Cultivating a zero-waste future through the art of surplus distribution.</p>
            </div>

            <!-- Floating Info Card -->
            <div class="relative z-10 glass-card-dark max-w-lg p-8 rounded-[2rem] text-white">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-400/20 text-emerald-300 border border-emerald-400/30">
                        <i data-lucide="leaf" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-300">The Living Pantry</span>
                </div>
                <h3 class="text-2xl font-bold tracking-tight leading-snug">Turn Surplus Food Into Shared Nourishment</h3>
                <p class="mt-3 text-sm leading-relaxed text-emerald-100/75">Join thousands of community members actively reducing waste and building a sustainable circular economy. Simple, delicious, and deeply impactful.</p>
                
                <div class="mt-6 pt-6 border-t border-white/10 flex items-center justify-between text-xs text-emerald-200/60 font-semibold tracking-wider uppercase">
                    <span>Active Food Hub</span>
                    <span class="flex items-center gap-2 text-emerald-400">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                        Live Community
                    </span>
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
