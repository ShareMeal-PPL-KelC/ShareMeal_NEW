<x-layouts.app title="Masuk - ShareMeal">
    <div class="grid min-h-screen lg:grid-cols-2">
        <!-- Kolom Kiri: Branding (Hanya Desktop) -->
        <div class="relative hidden overflow-hidden bg-[#174413] lg:flex">
            <img src="https://images.unsplash.com/photo-1563485571829-7032f428f3ce?auto=format&fit=crop&q=80&w=1200" alt="ShareMeal" class="absolute inset-0 h-full w-full object-cover opacity-30 mix-blend-screen">
            <div class="relative z-10 flex h-full w-full flex-col justify-between p-16 text-white">
                <div>
                    <a href="{{ route('home') }}" class="text-5xl font-extrabold text-[#bcf0ae] hover:opacity-80 transition">ShareMeal</a>
                    <p class="mt-4 max-w-md text-lg text-white/90">Cultivating a zero-waste future through the art of surplus distribution.</p>
                </div>
                <div class="card-soft max-w-xl p-8 text-slate-800">
                    <div class="text-2xl font-bold text-[#174413]">The Living Pantry</div>
                    <p class="mt-4 text-sm leading-7 text-slate-600">Join thousands of community members turning food surplus into shared nourishment. Simple, sustainable, and purely impactful.</p>
                    <div class="mt-6 text-xs font-semibold uppercase tracking-[0.3em] text-[#174413]">Bergabung Bersama Kami</div>
                </div>
            </div>
        </div>
        
        <!-- Kolom Kanan: Form -->
        <div class="flex items-center justify-center bg-[#f9f9f8] px-6 py-10 lg:px-16">
            <div class="w-full max-w-xl">
                <!-- Branding Mobile -->
                <div class="mb-10 lg:hidden">
                    <a href="{{ route('home') }}" class="text-3xl font-extrabold text-[#174413]">ShareMeal</a>
                </div>

                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-slate-900">Selamat Datang</h1>
                    <p class="mt-2 text-slate-600">Silakan masuk untuk melanjutkan perjalanan keberlanjutan Anda.</p>
                </div>

                <!-- Menampilkan Error Global Jika Ada -->
                @if ($errors->any())
                    <div class="mb-6 rounded-xl bg-red-50 p-4 border border-red-200">
                        <ul class="list-inside list-disc text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="post" action="{{ route('login.submit') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-800">Tipe Pengguna</label>
                        <select name="user_type" class="select @error('user_type') border-red-500 @enderror">
                            <option value="consumer" {{ old('user_type') == 'consumer' ? 'selected' : '' }}>Konsumen</option>
                            <option value="mitra" {{ old('user_type') == 'mitra' ? 'selected' : '' }}>Mitra</option>
                            <option value="lembaga" {{ old('user_type') == 'lembaga' ? 'selected' : '' }}>Lembaga Sosial</option>
                            <option value="admin" {{ old('user_type') == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('user_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-800">Alamat Email</label>
                        <input class="input @error('email') border-red-500 @enderror" type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required>
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-800">Kata Sandi</label>
                        <input class="input @error('password') border-red-500 @enderror" type="password" name="password" placeholder="••••••••" required>
                        @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-3 text-sm text-slate-600">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-[#174413] focus:ring-[#174413]"> Ingat Saya
                        </label>
                        <a href="#" class="text-sm font-semibold text-[#174413] hover:underline">Lupa sandi?</a>
                    </div>
                    <button type="submit" class="btn-primary w-full py-4 text-base">Masuk</button>
                </form>

                <div class="my-6 flex items-center gap-4">
                    <div class="h-px flex-1 bg-slate-200"></div>
                    <div class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Atau Masuk Dengan</div>
                    <div class="h-px flex-1 bg-slate-200"></div>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" class="btn-secondary">Google</button>
                    <button type="button" class="btn-secondary">Apple</button>
                </div>
                
                <p class="mt-8 text-center text-sm text-slate-600">
                    Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-[#174413] hover:underline">Daftar sekarang</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>