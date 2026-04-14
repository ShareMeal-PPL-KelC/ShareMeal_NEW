<x-layouts.app title="Daftar - ShareMeal">
    <div class="grid min-h-screen lg:grid-cols-2">
        <!-- Kolom Kiri: Branding (Hanya Desktop) -->
        <div class="relative hidden overflow-hidden bg-[#174413] lg:flex">
            <img src="https://images.unsplash.com/photo-1593113702251-272b1bc414a9?auto=format&fit=crop&q=80&w=1200" alt="Impact" class="absolute inset-0 h-full w-full object-cover opacity-40">
            <div class="relative z-10 flex h-full w-full flex-col justify-between bg-gradient-to-tr from-[#174413] via-[#174413]/85 to-transparent p-16 text-white">
                <a href="{{ route('home') }}" class="flex items-center gap-3 hover:opacity-80 transition inline-block">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-lg font-black text-[#174413]">S</div>
                    <span class="text-3xl font-extrabold">ShareMeal</span>
                </a>
                <div>
                    <h2 class="text-5xl font-extrabold leading-tight">Bersama Kurangi Limbah, Berbagi Berkah.</h2>
                    <p class="mt-6 max-w-xl text-lg text-white/90">Bergabunglah dengan ekosistem pangan berkelanjutan kami. Berikan dampak nyata bagi bumi dan sesama melalui langkah sederhana menyelamatkan surplus makanan.</p>
                </div>
                <div class="grid grid-cols-2 gap-10 text-white">
                    <div>
                        <div class="text-4xl font-extrabold">15k+</div>
                        <div class="mt-2 text-xs uppercase tracking-[0.3em] text-white/70">Makanan Terselamatkan</div>
                    </div>
                    <div>
                        <div class="text-4xl font-extrabold">200+</div>
                        <div class="mt-2 text-xs uppercase tracking-[0.3em] text-white/70">Mitra Lokal</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Kolom Kanan: Form -->
        <div class="flex items-center justify-center bg-white px-6 py-10 lg:px-16">
            <div class="w-full max-w-xl">
                <!-- Branding Mobile -->
                <div class="mb-10 flex items-center gap-3 lg:hidden">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#174413] text-lg font-black text-white">S</div>
                    <a href="{{ route('home') }}" class="text-3xl font-extrabold text-[#174413]">ShareMeal</a>
                </div>

                <div class="mb-8">
                    <h1 class="text-4xl font-extrabold text-[#174413]">Buat Akun Baru</h1>
                    <p class="mt-2 text-slate-600">Langkah awal Anda menuju masa depan tanpa limbah.</p>
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

                <form method="post" action="{{ route('register.submit') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label class="mb-3 block text-sm font-semibold uppercase tracking-[0.2em] text-[#174413]">Pilih Peran Anda</label>
                        <div class="grid gap-3 md:grid-cols-3">
                            @foreach ([['mitra', 'Mitra', 'Toko atau Restoran'], ['consumer', 'Konsumen', 'Pahlawan Makanan'], ['lembaga', 'Lembaga', 'Organisasi Sosial']] as $role)
                                <label class="card cursor-pointer p-4 hover:border-[#174413] transition-colors relative has-[:checked]:border-[#174413] has-[:checked]:ring-1 has-[:checked]:ring-[#174413] has-[:checked]:bg-green-50/50">
                                    <input type="radio" name="user_type" value="{{ $role[0] }}" class="mb-3 text-[#174413] focus:ring-[#174413]" {{ old('user_type', $loop->first ? $role[0] : '') == $role[0] ? 'checked' : '' }}>
                                    <div class="text-base font-semibold text-[#174413]">{{ $role[1] }}</div>
                                    <div class="text-xs text-slate-500">{{ $role[2] }}</div>
                                </label>
                            @endforeach
                        </div>
                        @error('user_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="grid gap-5">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Nama Lengkap</label>
                            <input class="input @error('name') border-red-500 @enderror" type="text" name="name" value="{{ old('name') }}" placeholder="John Doe" required>
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                            <input class="input @error('email') border-red-500 @enderror" type="email" name="email" value="{{ old('email') }}" placeholder="email@contoh.com" required>
                            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Kata Sandi</label>
                            <input class="input @error('password') border-red-500 @enderror" type="password" name="password" placeholder="••••••••" required>
                            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Konfirmasi Kata Sandi</label>
                            <input class="input" type="password" name="password_confirmation" placeholder="••••••••" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="flex items-start gap-3 text-sm text-slate-600">
                            <input type="checkbox" name="terms" value="1" class="mt-1 h-4 w-4 rounded border-slate-300 text-[#174413] focus:ring-[#174413]" required>
                            <span>Saya menyetujui <span class="font-semibold text-[#174413]">Syarat & Ketentuan</span> serta <span class="font-semibold text-[#174413]">Kebijakan Privasi</span> yang berlaku di ShareMeal.</span>
                        </label>
                        @error('terms') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    <button type="submit" class="btn-primary w-full rounded-full py-4 text-base">Daftar Sekarang</button>
                </form>
                
                <p class="mt-8 text-center text-sm text-slate-600">
                    Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-[#174413] hover:underline">Masuk ke sini</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>