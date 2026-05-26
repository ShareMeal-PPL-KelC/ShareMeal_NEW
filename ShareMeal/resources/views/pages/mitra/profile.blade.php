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

<div class="space-y-6" x-data="{ businessContactOtpModalOpen: @js($showBusinessContactOtpModal) }">
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
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ session('error') }}
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
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="business_type" class="block text-sm font-semibold text-gray-700 mb-2">Kategori Usaha</label>
                        <input id="business_type" name="business_type" type="text" value="{{ old('business_type', $businessType) }}" required maxlength="100" placeholder="Restoran, Bakery, Katering" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#174413] focus:ring-2 focus:ring-green-100 outline-none transition">
                        @error('business_type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label for="business_contact" class="block text-sm font-semibold text-gray-700 mb-2">Kontak Usaha</label>
                        <input id="business_contact" name="business_contact" type="tel" inputmode="numeric" value="{{ old('business_contact', $businessContact) }}" required maxlength="15" pattern="^(08|62)[0-9]{8,13}$" placeholder="081234567890" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#174413] focus:ring-2 focus:ring-green-100 outline-none transition {{ $businessContactLocked ? 'bg-gray-100 text-gray-500' : '' }}" {{ $businessContactLocked ? 'readonly' : '' }}>
                        <p class="mt-2 text-xs text-gray-500">Diawali 08 atau 62, panjang 10-15 digit.</p>
                        @if($businessContactLocked)
                            <p class="mt-2 text-xs font-semibold text-orange-600">Kontak usaha baru bisa diganti lagi pada {{ $businessContactLockedUntil->format('H:i:s') }}.</p>
                        @endif
                        @error('business_contact')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="opening_start" class="block text-sm font-semibold text-gray-700 mb-2">Jam Buka</label>
                        <input id="opening_start" name="opening_start" type="time" value="{{ old('opening_start', $openingStart) }}" required class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#174413] focus:ring-2 focus:ring-green-100 outline-none transition">
                        @error('opening_start')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="opening_end" class="block text-sm font-semibold text-gray-700 mb-2">Jam Tutup</label>
                        <input id="opening_end" name="opening_end" type="time" value="{{ old('opening_end', $openingEnd) }}" required class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#174413] focus:ring-2 focus:ring-green-100 outline-none transition">
                        @error('opening_end')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="business_address" class="block text-sm font-semibold text-gray-700 mb-2">Alamat Usaha</label>
                    <textarea id="business_address" name="business_address" rows="3" maxlength="1000" required placeholder="Masukkan alamat lengkap usaha" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#174413] focus:ring-2 focus:ring-green-100 outline-none transition">{{ old('business_address', $businessAddress) }}</textarea>
                    @error('business_address')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="business_description" class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi Usaha</label>
                    <textarea id="business_description" name="business_description" rows="5" maxlength="1000" required placeholder="Ceritakan jenis makanan, konsep usaha, atau layanan utama" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#174413] focus:ring-2 focus:ring-green-100 outline-none transition">{{ old('business_description', $businessDescription) }}</textarea>
                    @error('business_description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="store_image" class="block text-sm font-semibold text-gray-700 mb-2">Gambar Toko</label>
                    <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-5">
                        <input id="store_image" name="store_image" type="file" accept="image/jpeg,image/png" class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-[#174413] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-green-900">
                        <p class="mt-3 text-xs text-gray-500">Format JPG, JPEG, atau PNG. Maksimal 2 MB.</p>
                    </div>
                    @error('store_image')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
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

    @if($profile?->business_pending_contact || $businessContactOtp || $errors->has('otp'))
        <div x-show="businessContactOtpModalOpen"
             x-transition.opacity
             class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4"
             x-cloak>
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl" @click.away="businessContactOtpModalOpen = false">
                <div class="border-b border-gray-100 px-6 py-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Verifikasi Kontak Usaha</h3>
                            <p class="mt-1 text-sm text-gray-600">Masukkan kode OTP untuk mengaktifkan kontak {{ $profile?->business_pending_contact ?? old('business_contact') }}.</p>
                        </div>
                        <button type="button" @click="businessContactOtpModalOpen = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ route('mitra.profile.contact.verify') }}" class="px-6 py-5 space-y-4">
                    @csrf

                    @if($businessContactOtp)
                        <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                            <span class="font-semibold">Kode OTP demo:</span> {{ $businessContactOtp }}
                        </div>
                    @endif

                    <div>
                        <label for="business_contact_otp" class="block text-sm font-semibold text-gray-700 mb-2">Kode OTP</label>
                        <input id="business_contact_otp" name="otp" type="text" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" placeholder="Masukkan 6 digit kode" autofocus class="w-full rounded-xl border border-gray-200 px-4 py-3 text-center text-lg font-bold tracking-[0.35em] focus:border-[#174413] focus:ring-2 focus:ring-green-100 outline-none transition">
                        @error('otp')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
                        <button type="button" @click="businessContactOtpModalOpen = false" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                            Nanti Saja
                        </button>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 transition">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            Verifikasi OTP
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
