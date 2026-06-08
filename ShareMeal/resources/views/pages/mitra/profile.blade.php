@extends('layouts.dashboard')

@section('content')
@php
    $businessName = $profile?->business_name ?? $user->organization_name ?? $user->name;
    $businessAddress = $profile?->business_address;
    $businessContact = $profile?->business_contact;
    $businessContactLockedUntil = $profile?->business_contact_change_available_at;
    $businessContactLocked = $businessContactLockedUntil && $businessContactLockedUntil->isFuture();
    $businessContactOtp = session('business_contact_otp.' . $user->id);
    $showBusinessContactOtpModal = (bool) ($businessContactOtp || $profile?->business_pending_contact || $errors->has('otp'));
    $businessType = $profile?->business_type ?? 'Restoran';
    $businessDescription = $profile?->business_description ?? $profile?->description;
    $openingHours = $profile?->business_opening_hours ?? $profile?->opening_hours;
    [$openingStart, $openingEnd] = str_contains((string) $openingHours, ' - ')
        ? explode(' - ', $openingHours, 2)
        : ['08:00', '20:00'];
@endphp

<!-- Demo OTP session store for AJAX parsing -->
<div id="demo_otp_store" class="hidden" data-demo-otp="{{ session('business_contact_otp.' . $user->id) }}"></div>

<div class="space-y-6" x-data="mitraProfileHandler()">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Profil Usaha</h1>
            <p class="text-gray-600 mt-1">Lengkapi informasi usaha agar konsumen mengenal mitra dengan jelas.</p>
        </div>
        <a href="{{ route('mitra.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-[#174413] transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="session-error-alert rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if($user->status === 'warned' || $user->warnings_count > 0)
        <div class="rounded-2xl border border-orange-100 bg-orange-50/50 p-6 flex items-start gap-4">
            <div class="w-12 h-12 bg-orange-100 text-orange-650 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/>
                </svg>
            </div>
            <div class="space-y-1.5 flex-1">
                <h3 class="text-xs font-black text-orange-850 uppercase tracking-widest leading-none">Riwayat Peringatan Admin</h3>
                <p class="text-xs text-orange-700 leading-relaxed font-medium">Akun Anda sedang dalam status peringatan resmi oleh Admin.</p>
                <div class="text-xs text-orange-600 mt-2 bg-white/70 p-3 rounded-lg border border-orange-100 italic">
                    <strong>Alasan Terakhir:</strong> {{ $user->warning_reason ?: 'Pelanggaran kebijakan platform.' }}
                </div>
                <p class="text-[10px] text-orange-500 font-bold uppercase tracking-widest mt-2 block">Total Peringatan Akun: {{ $user->warnings_count }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-[320px_1fr] gap-6">
        <aside class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 h-fit">
            <div class="flex flex-col items-center text-center">
                <img src="{{ $user->image }}" alt="Foto usaha {{ $businessName }}" class="h-32 w-32 rounded-2xl object-cover ring-4 ring-green-50 border border-green-100">
                <h2 class="mt-4 text-xl font-bold text-gray-900">{{ $businessName }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $businessType }}</p>
                <div class="mt-5 w-full rounded-xl bg-gray-50 p-4 text-left space-y-3">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Kontak</div>
                        <div class="mt-1 text-sm font-medium text-gray-700">{{ $businessContact ?? '-' }}</div>
                        @if($profile?->business_pending_contact)
                            <div class="mt-1 text-xs font-medium text-orange-600">Menunggu OTP: {{ $profile->business_pending_contact }}</div>
                        @elseif($profile?->business_contact_verified_at)
                            <div class="mt-1 text-xs font-medium text-green-600">Terverifikasi</div>
                        @endif
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Jam Operasional</div>
                        <div class="mt-1 text-sm font-medium text-gray-700">{{ $openingHours ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Alamat</div>
                        <div class="mt-1 text-sm font-medium text-gray-700">{{ $businessAddress ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </aside>

        <section class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-5">
                <h2 class="text-xl font-bold text-gray-900">Informasi Usaha</h2>
                <p class="mt-1 text-sm text-gray-500">Informasi ini digunakan di halaman pencarian, checkout, dan detail transaksi konsumen.</p>
            </div>

            <form method="POST" action="{{ route('mitra.profile.update') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="business_name" class="block text-sm font-semibold text-gray-700 mb-2">Nama Usaha</label>
                        <input id="business_name" name="business_name" type="text" value="{{ old('business_name', $businessName) }}" required maxlength="255" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#174413] focus:ring-2 focus:ring-green-100 outline-none transition">
                        @error('business_name')
                            <p class="mt-2 text-sm text-red-600 validation-error-msg">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="business_type" class="block text-sm font-semibold text-gray-700 mb-2">Kategori Usaha</label>
                        <input id="business_type" name="business_type" type="text" value="{{ old('business_type', $businessType) }}" required maxlength="100" placeholder="Restoran, Bakery, Katering" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#174413] focus:ring-2 focus:ring-green-100 outline-none transition">
                        @error('business_type')
                            <p class="mt-2 text-sm text-red-600 validation-error-msg">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-5">
                    <div class="flex-1 md:min-w-[300px]">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kontak Usaha</label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" name="business_contact" id="business_contact" value="{{ old('business_contact', $businessContact) }}" readonly class="w-full rounded-xl border border-gray-200 bg-gray-100 p-3 pr-28 text-sm text-gray-500 cursor-not-allowed">
                                @if($profile?->business_contact_verified_at)
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1 text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-full border border-blue-100">
                                        <i data-lucide="shield-check" class="w-3 h-3"></i>
                                        VERIFIED
                                    </div>
                                @endif
                            </div>
                            <button type="button" @click="businessContactOtpModalOpen = true; phoneStep = 1; errorMsgPhone = ''; newPhone = '';"
                                    {{ $businessContactLocked ? 'disabled' : '' }}
                                    class="px-4 py-3 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl text-xs font-bold transition-all whitespace-nowrap">
                                Ganti Nomor
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Diawali 08 atau 62, panjang 10-15 digit.</p>
                        @if($businessContactLocked)
                            <p class="mt-2 text-xs font-semibold text-orange-600">Kontak usaha baru bisa diganti lagi pada {{ $businessContactLockedUntil->format('H:i:s') }}.</p>
                        @endif
                        @error('business_contact')
                            <p class="mt-2 text-sm text-red-600 validation-error-msg">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="w-full md:w-40">
                        <label for="opening_start" class="block text-sm font-semibold text-gray-700 mb-2">Jam Buka</label>
                        <input id="opening_start" name="opening_start" type="time" value="{{ old('opening_start', $openingStart) }}" required class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#174413] focus:ring-2 focus:ring-green-100 outline-none transition text-center">
                        @error('opening_start')
                            <p class="mt-2 text-sm text-red-600 validation-error-msg">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="w-full md:w-40">
                        <label for="opening_end" class="block text-sm font-semibold text-gray-700 mb-2">Jam Tutup</label>
                        <input id="opening_end" name="opening_end" type="time" value="{{ old('opening_end', $openingEnd) }}" required class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#174413] focus:ring-2 focus:ring-green-100 outline-none transition text-center">
                        @error('opening_end')
                            <p class="mt-2 text-sm text-red-600 validation-error-msg">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="business_address" class="block text-sm font-semibold text-gray-700 mb-2">Alamat Usaha</label>
                    <textarea id="business_address" name="business_address" rows="3" maxlength="1000" required placeholder="Masukkan alamat lengkap usaha" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#174413] focus:ring-2 focus:ring-green-100 outline-none transition">{{ old('business_address', $businessAddress) }}</textarea>
                    @error('business_address')
                        <p class="mt-2 text-sm text-red-600 validation-error-msg">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="business_description" class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi Usaha</label>
                    <textarea id="business_description" name="business_description" rows="5" maxlength="1000" required placeholder="Ceritakan jenis makanan, konsep usaha, atau layanan utama" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#174413] focus:ring-2 focus:ring-green-100 outline-none transition">{{ old('business_description', $businessDescription) }}</textarea>
                    @error('business_description')
                        <p class="mt-2 text-sm text-red-600 validation-error-msg">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-2xl border border-gray-100 bg-gray-50/50 p-6 space-y-6" x-data="{ canDelivery: @js($profile?->can_delivery ?? false) }">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Jasa Pengiriman</h3>
                            <p class="text-xs text-gray-500 mt-1">Aktifkan jika Anda menyediakan layanan kirim makanan ke alamat konsumen.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="can_delivery" value="0">
                            <input type="checkbox" name="can_delivery" value="1" x-model="canDelivery" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#174413]"></div>
                        </label>
                    </div>

                    <div x-show="canDelivery" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="pt-4 border-t border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="delivery_fee" class="block text-sm font-semibold text-gray-700 mb-2">Biaya Ongkir (Flat)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">Rp</span>
                                    </div>
                                    <input id="delivery_fee" name="delivery_fee" type="number" value="{{ old('delivery_fee', $profile?->delivery_fee ?? 0) }}" min="0" class="w-full rounded-xl border border-gray-200 pl-11 pr-4 py-3 text-sm focus:border-[#174413] focus:ring-2 focus:ring-green-100 outline-none transition" placeholder="0">
                                </div>
                                @error('delivery_fee')
                                    <p class="mt-2 text-sm text-red-600 validation-error-msg">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="delivery_slot_limit" class="block text-sm font-semibold text-gray-700 mb-2">Limit Pesanan per Slot</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i data-lucide="users" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input id="delivery_slot_limit" name="delivery_slot_limit" type="number" value="{{ old('delivery_slot_limit', $profile?->delivery_slot_limit ?? 10) }}" min="1" class="w-full rounded-xl border border-gray-200 pl-11 pr-4 py-3 text-sm focus:border-[#174413] focus:ring-2 focus:ring-green-100 outline-none transition" placeholder="10">
                                </div>
                                <p class="mt-2 text-xs text-gray-500">Jumlah maksimal pesanan (delivery/pickup) dalam satu jendela 1 jam.</p>
                                @error('delivery_slot_limit')
                                    <p class="mt-2 text-sm text-red-600 validation-error-msg">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="store_image" class="block text-sm font-semibold text-gray-700 mb-2">Gambar Toko</label>
                    <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-5">
                        <input id="store_image" name="store_image" type="file" accept="image/jpeg,image/png" class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-[#174413] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-green-900" @change="checkImageSize($event)">
                        <p class="mt-3 text-xs text-gray-500">Format JPG, JPEG, atau PNG. Maksimal 2 MB.</p>
                        <p x-show="imageError" class="text-xs text-red-650 font-bold mt-2" x-text="imageError" x-cloak></p>
                    </div>
                    @error('store_image')
                        <p class="mt-2 text-sm text-red-600 validation-error-msg">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('mitra.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#174413] px-5 py-3 text-sm font-semibold text-white hover:bg-green-900 transition">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Simpan Profil Usaha
                    </button>
                </div>
            </form>
        </section>
    </div>

    <!-- DEDICATED CHANGE BUSINESS CONTACT & OTP MODAL -->
    <div x-show="businessContactOtpModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak x-transition>
        <div class="bg-white w-full max-w-md rounded-3xl p-8 shadow-2xl space-y-6" @click.away="if (!loading && !success && !loadingOtp) { businessContactOtpModalOpen = false; }">
            
            <!-- STEP 1: INPUT NEW PHONE NUMBER -->
            <div x-show="phoneStep === 1" x-transition>
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-emerald-100">
                        <i data-lucide="phone" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-2xl font-black text-gray-900">Ubah Kontak Usaha</h3>
                    <p class="text-gray-500 text-sm mt-2">Masukkan nomor telepon baru untuk usaha Anda. Kode OTP verifikasi akan dikirimkan ke nomor ini.</p>
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
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Nomor Telepon Usaha Baru</label>
                        <input type="text" x-model="newPhone" placeholder="08xxxxxxxxxx" class="w-full rounded-2xl border-gray-200 bg-gray-50 p-4 text-sm font-semibold focus:border-emerald-600 focus:ring-emerald-600 outline-none transition-all">
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="businessContactOtpModalOpen = false" class="flex-1 py-4 rounded-xl font-bold text-gray-400 hover:bg-gray-50 transition" :disabled="loadingOtp">Batal</button>
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

                    <form action="{{ route('mitra.profile.contact.verify') }}" method="POST" class="space-y-4" @submit.prevent="verifyOtp($event)">
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
        Alpine.data('mitraProfileHandler', () => ({
            businessContactOtpModalOpen: {{ $showBusinessContactOtpModal ? 'true' : 'false' }},
            phoneStep: {{ ($businessContactOtp || $profile?->business_pending_contact || $errors->has('otp')) ? '2' : '1' }},
            newPhone: '{{ $profile?->business_pending_contact ?? '' }}',
            demoOtpVal: '{{ $businessContactOtp }}',
            loadingOtp: false,
            errorMsgPhone: '',
            loading: false,
            success: false,
            errorMsg: '',
            imageError: '',

            checkImageSize(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 2 * 1024 * 1024) {
                        this.imageError = 'Ukuran gambar toko maksimal 2 MB. File yang Anda pilih berukuran ' + (file.size / (1024 * 1024)).toFixed(2) + ' MB.';
                        e.target.value = ''; // Reset file input
                    } else {
                        this.imageError = '';
                    }
                }
            },

            async sendOtp() {
                this.loadingOtp = true;
                this.errorMsgPhone = '';
                
                const newPhoneVal = this.newPhone ? this.newPhone.toString().trim() : '';
                const currentPhoneVal = '{{ $businessContact }}'.trim();
                
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
                
                // Get other fields to pass validation
                formData.append('business_name', document.getElementById('business_name')?.value || '');
                formData.append('business_type', document.getElementById('business_type')?.value || '');
                formData.append('business_address', document.getElementById('business_address')?.value || '');
                formData.append('business_contact', newPhoneVal);
                formData.append('opening_start', document.getElementById('opening_start')?.value || '');
                formData.append('opening_end', document.getElementById('opening_end')?.value || '');
                formData.append('business_description', document.getElementById('business_description')?.value || '');
                
                // Jasa pengiriman
                const canDeliveryCheckbox = document.querySelector('input[name="can_delivery"][type="checkbox"]');
                const canDelivery = canDeliveryCheckbox ? (canDeliveryCheckbox.checked ? '1' : '0') : '0';
                formData.append('can_delivery', canDelivery);
                if (canDelivery === '1') {
                    formData.append('delivery_fee', document.getElementById('delivery_fee')?.value || '0');
                    formData.append('delivery_slot_limit', document.getElementById('delivery_slot_limit')?.value || '10');
                }

                let fetchCompleted = false;
                const safetyTimeout = setTimeout(() => {
                    if (!fetchCompleted) {
                        this.loadingOtp = false;
                        this.phoneStep = 2;
                    }
                }, 1200);
                
                try {
                    const response = await fetch('{{ route('mitra.profile.update') }}', {
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
