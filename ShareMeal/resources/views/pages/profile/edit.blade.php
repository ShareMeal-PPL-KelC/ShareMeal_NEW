@extends('layouts.dashboard')

@section('content')
@php
    $currentPhone = $profile?->phone ?? $user->phone;
    $phoneLockedUntil = $profile?->phone_change_available_at;
    $phoneChangeLocked = $phoneLockedUntil && $phoneLockedUntil->isFuture();
    $demoOtp = session('profile_phone_otp.' . $user->id);
    $showOtpModal = (bool) ($demoOtp || $profile?->pending_phone || $errors->has('otp'));
@endphp

<!-- Demo OTP session store for AJAX parsing -->
<div id="demo_otp_store" class="hidden" data-demo-otp="{{ session('profile_phone_otp.' . $user->id) }}"></div>

<div class="space-y-6" x-data="profileHandler()">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Profil Saya</h1>
            <p class="text-gray-600 mt-1">Kelola identitas akun yang digunakan di ShareMeal.</p>
        </div>
        <a href="{{ route(Auth::user()->role . '.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-[#174413] transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700 animate-pulse">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="session-error-alert rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-[320px_1fr] gap-6">
        <aside class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 h-fit">
            <div class="flex flex-col items-center text-center">
                <img src="{{ $user->image }}" alt="Foto profil {{ $user->name }}" class="h-32 w-32 rounded-full object-cover ring-4 ring-green-50 border border-green-100">
                <h2 class="mt-4 text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                <p class="mt-1 text-sm capitalize text-gray-500">{{ $user->role }}</p>
                <div class="mt-6 w-full space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500">Status Akun</span>
                        <span class="font-bold text-green-600">Aktif</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500">Terverifikasi</span>
                        <span class="font-bold text-blue-600">{{ $user->is_verified ? 'Ya' : 'Tidak' }}</span>
                    </div>
                </div>
            </div>
        </aside>

        <main class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="divide-y divide-gray-100">
                @csrf
                <div class="p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Informasi Pribadi</h3>
                        <p class="text-sm text-gray-500">Perbarui nama dan foto profil Anda tanpa memicu verifikasi kontak.</p>
                    </div>

                    @if($user->role === 'lembaga')
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Nama Lembaga</label>
                            <input type="text" value="{{ $user->organization_name }}" readonly class="w-full rounded-xl border-gray-200 bg-gray-100 p-3 text-sm text-gray-500 cursor-not-allowed">
                            <p class="text-[10px] text-gray-400 italic">Nama lembaga tidak dapat diubah untuk menjaga validitas dokumen hukum.</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="name" class="text-sm font-semibold text-gray-700">{{ $user->role === 'lembaga' ? 'Nama Pemilik/Pengurus' : 'Nama Lengkap' }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border-gray-200 bg-gray-50/50 p-3 text-sm focus:border-[#174413] focus:ring-[#174413] @error('name') border-red-500 @enderror">
                            @error('name') <p class="text-xs text-red-600 validation-error-msg">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="avatar" class="text-sm font-semibold text-gray-700">Foto Profil</label>
                            <input type="file" name="avatar" id="avatar" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-[#174413] hover:file:bg-green-100" @change="checkAvatarSize($event)">
                            <p x-show="avatarError" class="text-xs text-red-600 font-bold" x-text="avatarError" x-cloak></p>
                            @error('avatar') <p class="text-xs text-red-600 validation-error-msg">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="address" class="text-sm font-semibold text-gray-700">Alamat</label>
                        <textarea name="address" id="address" rows="3" class="w-full rounded-xl border-gray-200 bg-gray-50/50 p-3 text-sm focus:border-[#174413] focus:ring-[#174413] @error('address') border-red-500 @enderror">{{ old('address', $profile?->address) }}</textarea>
                        @error('address') <p class="text-xs text-red-600 validation-error-msg">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Keamanan & Kontak</h3>
                        <p class="text-sm text-gray-500">Kelola email dan nomor telepon terverifikasi secara aman.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Alamat Email</label>
                            <input type="email" value="{{ $user->email }}" disabled class="w-full rounded-xl border-gray-200 bg-gray-100 p-3 text-sm text-gray-500 cursor-not-allowed">
                            <p class="text-[10px] text-gray-400 italic">Email tidak dapat diubah untuk keamanan akun.</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Nomor Telepon</label>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" name="phone" id="phone" value="{{ old('phone', $currentPhone) }}" readonly class="w-full rounded-xl border border-gray-200 bg-gray-100 p-3 pr-28 text-sm text-gray-500 cursor-not-allowed">
                                    @if($user->phone_verified_at)
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1 text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-full border border-blue-100">
                                            <i data-lucide="shield-check" class="w-3 h-3"></i>
                                            VERIFIED
                                        </div>
                                    @endif
                                </div>
                                <button type="button" @click="changePhoneModalOpen = true; phoneStep = 1; errorMsgPhone = ''; newPhone = '';"
                                        {{ $phoneChangeLocked ? 'disabled' : '' }}
                                        class="px-4 py-3 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl text-xs font-bold transition-all whitespace-nowrap">
                                    Ubah Nomor
                                </button>
                            </div>
                            @error('phone') <p class="text-xs text-red-600 mt-1 validation-error-msg">{{ $message }}</p> @enderror
                            @if($phoneChangeLocked)
                                <p class="text-[10px] text-orange-600 italic mt-1">Nomor baru saja diganti. Dapat diubah kembali pada: {{ $phoneLockedUntil->format('d M Y, H:i') }}</p>
                            @endif
                            

                        </div>
                    </div>
                </div>

                <div class="bg-gray-50/50 p-6 flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#174413] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-green-100 transition-all hover:bg-[#256020]">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Simpan Profil
                    </button>
                </div>
            </form>
        </main>
    </div>

    <!-- DEDICATED CHANGE PHONE & OTP MODAL -->
    <div x-show="changePhoneModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak x-transition>
        <div class="bg-white w-full max-w-md rounded-3xl p-8 shadow-2xl space-y-6" @click.away="if (!loading && !success && !loadingOtp) { changePhoneModalOpen = false; }">
            
            <!-- STEP 1: INPUT NEW PHONE NUMBER -->
            <div x-show="phoneStep === 1" x-transition>
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-emerald-100">
                        <i data-lucide="phone" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-2xl font-black text-gray-900">Ubah Nomor HP</h3>
                    <p class="text-gray-500 text-sm mt-2">Masukkan nomor telepon baru Anda. Kode OTP verifikasi akan dikirimkan ke nomor ini.</p>
                </div>

                <!-- Error alert banner -->
                <div x-show="errorMsgPhone" x-cloak class="mb-4 p-3.5 bg-red-50 border border-red-200/50 rounded-2xl flex items-start gap-2.5 text-xs font-semibold text-red-700">
                    <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span x-text="errorMsgPhone"></span>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Nomor HP Baru</label>
                        <input type="text" x-model="newPhone" placeholder="08xxxxxxxxxx" class="w-full rounded-2xl border-gray-200 bg-gray-50 p-4 text-sm font-semibold focus:border-emerald-600 focus:ring-emerald-600 outline-none transition-all">
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="changePhoneModalOpen = false" class="flex-1 py-4 rounded-xl font-bold text-gray-400 hover:bg-gray-50 transition" :disabled="loadingOtp">Batal</button>
                        <button type="button" @click="sendOtp()" class="flex-1 bg-[#174413] text-white py-4 rounded-xl font-black shadow-xl shadow-green-100 hover:bg-[#256020] transition flex items-center justify-center gap-2" :disabled="loadingOtp">
                            <span x-show="!loadingOtp">Kirim OTP</span>
                            <span x-show="loadingOtp" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Mengirim...
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 2: VERIFY OTP -->
            <div x-show="phoneStep === 2" x-transition>
                
                <!-- LOADING SCREEN -->
                <div x-show="loading" x-cloak>
                    <div class="flex flex-col items-center justify-center py-10 space-y-4">
                        <div class="relative w-16 h-16">
                            <div class="w-16 h-16 rounded-full border-4 border-emerald-100 animate-pulse"></div>
                            <div class="absolute inset-0 w-16 h-16 rounded-full border-4 border-t-emerald-600 border-r-transparent border-b-transparent border-l-transparent animate-spin"></div>
                        </div>
                        <p class="text-sm font-bold text-emerald-800 animate-pulse">Memverifikasi kode OTP...</p>
                    </div>
                </div>

                <!-- SUCCESS SCREEN -->
                <div x-show="success" x-cloak>
                    <div class="flex flex-col items-center justify-center py-8 space-y-4 text-center">
                        <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center shadow-lg shadow-emerald-200 animate-bounce">
                            <svg class="w-10 h-10 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-emerald-700">Verifikasi Berhasil!</h3>
                        <p class="text-xs font-bold text-emerald-600/70 uppercase tracking-widest animate-pulse">Mengalihkan profil Anda...</p>
                    </div>
                </div>

                <!-- INPUT FORM -->
                <div x-show="!loading && !success">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-green-50 border border-green-100 text-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="phone-forward" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-2xl font-black text-gray-900">Verifikasi Nomor</h3>
                        <p class="text-gray-500 text-sm mt-2">Kami telah mengirimkan kode OTP ke nomor <span class="font-bold text-gray-900" x-text="newPhone"></span></p>
                        
                        <!-- Demo OTP display -->
                        <div x-show="demoOtpVal" class="mt-4 p-3 bg-yellow-50 border border-yellow-100 rounded-xl text-xs text-yellow-700">
                            <p class="font-bold">MODE DEMO:</p>
                            <p>Gunakan kode OTP ini: <span class="text-lg font-black tracking-widest" x-text="demoOtpVal"></span></p>
                        </div>
                    </div>

                    <!-- AJAX Error alert banner -->
                    <div x-show="errorMsg" x-cloak class="mb-4 p-3.5 bg-red-50 border border-red-200/50 rounded-2xl flex items-start gap-2.5 text-xs font-semibold text-red-700">
                        <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span x-text="errorMsg"></span>
                    </div>

                    <form action="{{ route('profile.phone.verify') }}" method="POST" class="space-y-4" @submit.prevent="verifyOtp($event)">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest block text-center">Kode OTP 6 Digit</label>
                            <input type="text" name="otp" maxlength="6" required placeholder="000000" class="w-full text-center text-3xl font-black tracking-[1em] rounded-2xl border-gray-200 bg-gray-50 p-4 focus:border-[#174413] focus:ring-[#174413] @error('otp') border-red-500 @enderror">
                            @error('otp') <p class="text-xs text-red-600 mt-1 text-center validation-error-msg">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button type="button" @click="phoneStep = 1; errorMsgPhone = '';" class="flex-1 py-4 rounded-xl font-bold text-gray-400 hover:bg-gray-50 transition">Kembali</button>
                            <button type="submit" class="flex-1 bg-[#174413] text-white py-4 rounded-xl font-black shadow-xl shadow-green-100 hover:bg-[#256020] transition flex items-center justify-center gap-2">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                                Verifikasi OTP
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('profileHandler', () => ({
            changePhoneModalOpen: {{ $showOtpModal ? 'true' : 'false' }},
            phoneStep: {{ ($demoOtp || $profile?->pending_phone || $errors->has('otp')) ? '2' : '1' }},
            newPhone: '{{ $profile?->pending_phone ?? '' }}',
            demoOtpVal: '{{ $demoOtp }}',
            loadingOtp: false,
            errorMsgPhone: '',
            loading: false,
            success: false,
            errorMsg: '',
            avatarError: '',

            checkAvatarSize(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 2 * 1024 * 1024) {
                        this.avatarError = 'Ukuran foto profil maksimal 2 MB. File yang Anda pilih berukuran ' + (file.size / (1024 * 1024)).toFixed(2) + ' MB.';
                        e.target.value = ''; // Reset file input
                    } else {
                        this.avatarError = '';
                    }
                }
            },

            async sendOtp() {
                this.loadingOtp = true;
                this.errorMsgPhone = '';
                
                const newPhoneVal = this.newPhone ? this.newPhone.toString().trim() : '';
                const currentPhoneVal = '{{ $currentPhone }}'.trim();
                
                const normalizedNewPhone = newPhoneVal.replace(/\D+/g, '');
                const normalizedCurrentPhone = currentPhoneVal.replace(/\D+/g, '');

                if (normalizedNewPhone === normalizedCurrentPhone) {
                    this.errorMsgPhone = 'Nomor telepon baru tidak boleh sama dengan nomor telepon saat ini.';
                    this.loadingOtp = false;
                    return;
                }

                const phoneRegex = /^(08|62)\d{8,13}$/;
                if (!phoneRegex.test(newPhoneVal)) {
                    this.errorMsgPhone = 'Nomor telepon harus berupa angka valid dengan awalan 08 atau 62 dan panjang 10-15 digit.';
                    this.loadingOtp = false;
                    return;
                }
                
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                
                const nameEl = document.getElementById('name');
                const addressEl = document.getElementById('address');
                formData.append('name', nameEl ? nameEl.value : '');
                formData.append('phone', newPhoneVal);
                formData.append('address', addressEl ? addressEl.value : '');
                
                let fetchCompleted = false;
                
                const safetyTimeout = setTimeout(() => {
                    if (!fetchCompleted) {
                        this.loadingOtp = false;
                        this.phoneStep = 2;
                    }
                }, 1200);
                
                try {
                    const response = await fetch('{{ route('profile.update') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    fetchCompleted = true;
                    clearTimeout(safetyTimeout);
                    
                    if (!response.ok) {
                        if (response.status === 422) {
                            try {
                                const json = await response.json();
                                let errorMsg = json.message || 'Validasi gagal.';
                                if (json.errors) {
                                    errorMsg = Object.values(json.errors).flat().join(' ');
                                }
                                this.errorMsgPhone = errorMsg;
                            } catch (e) {
                                this.errorMsgPhone = 'Validasi gagal di server.';
                            }
                        } else {
                            this.errorMsgPhone = 'Gagal mengirim OTP. Terjadi kesalahan pada server (Status ' + response.status + ').';
                        }
                        this.loadingOtp = false;
                        this.phoneStep = 1;
                        return;
                    }

                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const hasError = doc.querySelector('.validation-error-msg') || doc.querySelector('.session-error-alert');
                    
                    if (hasError) {
                        this.errorMsgPhone = hasError.textContent.trim();
                        this.loadingOtp = false;
                        this.phoneStep = 1;
                    } else {
                        const demoOtpStore = doc.querySelector('#demo_otp_store');
                        if (demoOtpStore) {
                            this.demoOtpVal = demoOtpStore.getAttribute('data-demo-otp');
                        }
                        this.loadingOtp = false;
                        this.phoneStep = 2;
                    }
                } catch (err) {
                    fetchCompleted = true;
                    clearTimeout(safetyTimeout);
                    this.loadingOtp = false;
                    this.phoneStep = 2;
                }
            },

            async verifyOtp(e) {
                this.loading = true;
                this.errorMsg = '';
                const formData = new FormData(e.target);
                
                try {
                    const response = await fetch(e.target.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (!response.ok) {
                        this.errorMsg = 'Verifikasi gagal. Terjadi kesalahan pada server (Status ' + response.status + ').';
                        this.loading = false;
                        return;
                    }

                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const hasError = doc.querySelector('.validation-error-msg') || doc.querySelector('.session-error-alert');
                    
                    if (hasError) {
                        this.errorMsg = hasError.textContent.trim();
                        this.loading = false;
                    } else {
                        setTimeout(() => {
                            this.loading = false;
                            this.success = true;
                            setTimeout(() => {
                                window.location.reload();
                            }, 1800);
                        }, 1000);
                    }
                } catch (err) {
                    this.errorMsg = 'Koneksi internet bermasalah. Silakan coba lagi.';
                    this.loading = false;
                }
            }
        }));
    });
</script>
@endsection
