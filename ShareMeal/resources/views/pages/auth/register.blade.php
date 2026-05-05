<x-layouts.app title="Daftar - ShareMeal">
    <div class="grid min-h-screen lg:grid-cols-2">
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

        <div class="flex items-center justify-center bg-white px-6 py-10 lg:px-16">
            <div class="w-full max-w-xl">
                <div class="mb-10 flex items-center gap-3 lg:hidden">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#174413] text-lg font-black text-white">S</div>
                    <a href="{{ route('home') }}" class="text-3xl font-extrabold text-[#174413]">ShareMeal</a>
                </div>

                <div class="mb-8">
                    <h1 class="text-4xl font-extrabold text-[#174413]">Buat Akun Baru</h1>
                    <p class="mt-2 text-slate-600">Langkah awal Anda menuju masa depan tanpa limbah.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-xl bg-red-50 p-4 border border-red-200">
                        <ul class="list-inside list-disc text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="post" action="{{ route('register.submit') }}" enctype="multipart/form-data" class="space-y-6" x-data="{ userType: '{{ old('user_type', 'mitra') }}', showPassword: false, showPasswordConfirmation: false }">
                    @csrf
                    <div>
                        <label class="mb-3 block text-sm font-semibold uppercase tracking-[0.2em] text-[#174413]">Pilih Peran Anda</label>
                        <div class="grid gap-3 md:grid-cols-3">
                            @foreach ([['mitra', 'Mitra', 'Toko atau Restoran'], ['consumer', 'Konsumen', 'Pahlawan Makanan'], ['lembaga', 'Lembaga', 'Organisasi Sosial']] as $role)
                                <label class="cursor-pointer rounded-xl border-2 p-4 transition-all relative block"
                                       :class="userType === '{{ $role[0] }}' ? 'border-[#174413] bg-green-50 shadow-sm' : 'border-slate-100 hover:border-slate-200 bg-white'">
                                    <input type="radio" name="user_type" value="{{ $role[0] }}" x-model="userType" class="mb-3 h-4 w-4 text-[#174413] focus:ring-[#174413]" {{ old('user_type', 'mitra') == $role[0] ? 'checked' : '' }}>
                                    <div class="text-base font-bold text-[#174413]">{{ $role[1] }}</div>
                                    <div class="text-[10px] leading-tight text-slate-500 mt-0.5">{{ $role[2] }}</div>
                                </label>
                            @endforeach
                        </div>
                        @error('user_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="userType === 'mitra'" x-cloak class="space-y-6 border-y border-slate-100 py-6">
                        <h3 class="font-bold text-[#174413] flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                            Dokumen Legalitas Usaha
                        </h3>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">Foto KTP Pemilik <span class="text-red-500">*</span></label>
                                <input type="file" name="document_ktp_mitra" :required="userType === 'mitra'" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-[#174413] file:text-white">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">SIUP / TDP <span class="text-red-500">*</span></label>
                                <input type="file" name="document_siup_mitra" :required="userType === 'mitra'" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-[#174413] file:text-white">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">Nomor Induk Berusaha (NIB) <span class="text-red-500">*</span></label>
                                <input type="file" name="document_nib_mitra" :required="userType === 'mitra'" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-[#174413] file:text-white">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">Sertifikat Halal <span class="text-xs font-normal text-slate-400">(Opsional)</span></label>
                                <input type="file" name="document_halal_mitra" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-[#174413] file:text-white">
                            </div>
                        </div>
                    </div>

                    <div x-show="userType === 'lembaga'" x-cloak class="space-y-6 border-y border-slate-100 py-6">
                        <h3 class="font-bold text-[#174413] flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                            Dokumen Legalitas Lembaga
                        </h3>
                        <div class="grid gap-5">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">Dokumen Legalitas Dasar <span class="text-red-500">*</span></label>
                                <p class="text-[10px] text-slate-500 -mt-1">(Akta Pendirian, SK Menkumham, dll)</p>
                                <input type="file" name="document_legalitas_lembaga" :required="userType === 'lembaga'" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-[#174413] file:text-white">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">Dokumen Izin Operasional & Registrasi Sosial <span class="text-red-500">*</span></label>
                                <p class="text-[10px] text-slate-500 -mt-1">(Izin LKS, Tanda Daftar Yayasan, dll)</p>
                                <input type="file" name="document_izin_lembaga" :required="userType === 'lembaga'" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-[#174413] file:text-white">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">Dokumen Identitas & Lokasi <span class="text-red-500">*</span></label>
                                <p class="text-[10px] text-slate-500 -mt-1">(KTP Pengurus, Domisili, Foto Lokasi)</p>
                                <input type="file" name="document_identitas_lembaga" :required="userType === 'lembaga'" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-[#174413] file:text-white">
                            </div>
                        </div>
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
                            <div class="relative">
                                <input class="input pr-11 @error('password') border-red-500 @enderror" :type="showPassword ? 'text' : 'password'" name="password" placeholder="........" required>
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 z-10 flex h-5 w-5 -translate-y-1/2 items-center justify-center text-slate-400 transition hover:text-slate-600" :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Lihat kata sandi'">
                                    <i data-lucide="eye" class="h-5 w-5" x-show="!showPassword"></i>
                                    <i data-lucide="eye-off" class="h-5 w-5" x-show="showPassword" x-cloak></i>
                                </button>
                            </div>
                            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Konfirmasi Kata Sandi</label>
                            <div class="relative">
                                <input class="input pr-11" :type="showPasswordConfirmation ? 'text' : 'password'" name="password_confirmation" placeholder="........" required>
                                <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation" class="absolute right-3 top-1/2 z-10 flex h-5 w-5 -translate-y-1/2 items-center justify-center text-slate-400 transition hover:text-slate-600" :aria-label="showPasswordConfirmation ? 'Sembunyikan konfirmasi kata sandi' : 'Lihat konfirmasi kata sandi'">
                                    <i data-lucide="eye" class="h-5 w-5" x-show="!showPasswordConfirmation"></i>
                                    <i data-lucide="eye-off" class="h-5 w-5" x-show="showPasswordConfirmation" x-cloak></i>
                                </button>
                            </div>
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
