@extends('layouts.dashboard')

@section('content')
@php
    $currentPhone = $profile?->phone ?? $user->phone;
    $phoneLockedUntil = $profile?->phone_change_available_at;
    $phoneChangeLocked = $phoneLockedUntil && $phoneLockedUntil->isFuture();
    $demoOtp = session('profile_phone_otp.' . $user->id);
    $showOtpModal = (bool) ($demoOtp || $profile?->pending_phone || $errors->has('otp'));
@endphp
<div class="space-y-6" x-data="{ otpModalOpen: @js($showOtpModal) }">
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
                        <p class="text-sm text-gray-500">Perbarui nama dan foto profil Anda.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="name" class="text-sm font-semibold text-gray-700">Nama Lengkap</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border-gray-200 bg-gray-50/50 p-3 text-sm focus:border-[#174413] focus:ring-[#174413] @error('name') border-red-500 @enderror">
                            @error('name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="avatar" class="text-sm font-semibold text-gray-700">Foto Profil</label>
                            <input type="file" name="avatar" id="avatar" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-[#174413] hover:file:bg-green-100">
                            @error('avatar') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="address" class="text-sm font-semibold text-gray-700">Alamat</label>
                        <textarea name="address" id="address" rows="3" class="w-full rounded-xl border-gray-200 bg-gray-50/50 p-3 text-sm focus:border-[#174413] focus:ring-[#174413] @error('address') border-red-500 @enderror">{{ old('address', $profile?->address) }}</textarea>
                        @error('address') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Keamanan & Kontak</h3>
                        <p class="text-sm text-gray-500">Kelola email dan nomor telepon terverifikasi.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Alamat Email</label>
                            <input type="email" value="{{ $user->email }}" disabled class="w-full rounded-xl border-gray-200 bg-gray-100 p-3 text-sm text-gray-500 cursor-not-allowed">
                            <p class="text-[10px] text-gray-400 italic">Email tidak dapat diubah untuk keamanan akun.</p>
                        </div>

                        <div class="space-y-2">
                            <label for="phone" class="text-sm font-semibold text-gray-700">Nomor Telepon</label>
                            <div class="relative">
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $currentPhone) }}" {{ $phoneChangeLocked ? 'disabled' : '' }} class="w-full rounded-xl border-gray-200 bg-gray-50/50 p-3 text-sm focus:border-[#174413] focus:ring-[#174413] @error('phone') border-red-500 @enderror {{ $phoneChangeLocked ? 'cursor-not-allowed' : '' }}">
                                @if($user->phone_verified_at)
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1 text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-full border border-blue-100">
                                        <i data-lucide="shield-check" class="w-3 h-3"></i>
                                        VERIFIED
                                    </div>
                                @endif
                            </div>
                            @error('phone') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            @if($phoneChangeLocked)
                                <p class="text-[10px] text-orange-600 italic">Nomor baru saja diganti. Dapat diubah kembali pada: {{ $phoneLockedUntil->format('d M Y, H:i') }}</p>
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

    @if($showOtpModal)
        <div x-show="otpModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="bg-white w-full max-w-md rounded-3xl p-8 shadow-2xl space-y-6" @click.away="otpModalOpen = false">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="phone-forward" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-2xl font-black text-gray-900">Verifikasi Nomor</h3>
                    <p class="text-gray-500 text-sm mt-2">Kami telah mengirimkan kode OTP ke nomor <span class="font-bold text-gray-900">{{ $profile?->pending_phone }}</span></p>
                    @if($demoOtp)
                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-100 rounded-xl text-xs text-yellow-700">
                            <p class="font-bold">MODE DEMO:</p>
                            <p>Gunakan kode OTP ini: <span class="text-lg font-black tracking-widest">{{ $demoOtp }}</span></p>
                        </div>
                    @endif
                </div>

                <form action="{{ route('profile.phone.verify') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block text-center">Kode OTP 6 Digit</label>
                        <input type="text" name="otp" maxlength="6" required placeholder="000000" class="w-full text-center text-3xl font-black tracking-[1em] rounded-2xl border-gray-200 bg-gray-50 p-4 focus:border-[#174413] focus:ring-[#174413] @error('otp') border-red-500 @enderror">
                        @error('otp') <p class="text-xs text-red-600 text-center">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="otpModalOpen = false" class="flex-1 py-4 rounded-xl font-bold text-gray-400 hover:bg-gray-50 transition">Nanti Saja</button>
                        <button type="submit" class="flex-1 bg-[#174413] text-white py-4 rounded-xl font-black shadow-xl shadow-green-100 hover:bg-[#256020] transition flex items-center justify-center gap-2">
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
