<x-layouts.app title="Daftar - ShareMeal">
    <!-- Style & Google Fonts Import -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        
        .register-font {
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

    <div class="register-font grid min-h-screen lg:grid-cols-2 relative bg-[#f4f7f4] overflow-hidden">
        
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
                <img src="https://images.unsplash.com/photo-1593113702251-272b1bc414a9?auto=format&fit=crop&q=80&w=1200" alt="Sustainable Future" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-tr from-[#0b240a]/95 via-[#0e350b]/85 to-[#1b5017]/35 mix-blend-multiply"></div>
            </div>

            <!-- Top Header Logo -->
            <div class="relative z-10">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-xl font-black text-emerald-300 transition-transform group-hover:scale-105">S</div>
                    <span class="text-3xl font-extrabold text-white tracking-tight">ShareMeal</span>
                </a>
            </div>

            <!-- Main Heading -->
            <div class="relative z-10 my-auto py-12">
                <h2 class="text-5xl font-extrabold leading-tight text-white tracking-tight">Bersama Kurangi Limbah, Berbagi Berkah.</h2>
                <p class="mt-6 max-w-xl text-base text-emerald-100/85 leading-relaxed">Bergabunglah dengan ekosistem pangan berkelanjutan kami. Berikan dampak nyata bagi bumi dan sesama melalui langkah sederhana menyelamatkan surplus makanan.</p>
            </div>

            <!-- Bottom Floating Stats Grid -->
            <div class="relative z-10 grid grid-cols-2 gap-8 pt-8 border-t border-white/10">
                <div class="glass-card-dark p-6 rounded-2xl text-white">
                    <div class="text-3xl font-extrabold text-emerald-300">15k+</div>
                    <div class="mt-1 text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-200/60">Makanan Terselamatkan</div>
                </div>
                <div class="glass-card-dark p-6 rounded-2xl text-white">
                    <div class="text-3xl font-extrabold text-emerald-300">200+</div>
                    <div class="mt-1 text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-200/60">Mitra Lokal Aktif</div>
                </div>
            </div>
        </div>

        <!-- Right Column: Translucent Form Wrapper -->
        <div class="flex items-center justify-center px-6 py-12 lg:px-16 z-10 relative overflow-y-auto">
            <div class="w-full max-w-lg">
                <!-- Mobile Logo Header -->
                <div class="mb-8 lg:hidden">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-base font-black text-white">S</div>
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
