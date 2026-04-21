@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Admin</h1>
            <p class="text-gray-600 mt-1">Pantau performa sistem dan kelola verifikasi mitra</p>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500 bg-white px-4 py-2 rounded-lg border border-gray-100 shadow-sm">
            <i data-lucide="shield-check" class="w-4 h-4 text-green-600"></i>
            <span>Sistem Terverifikasi</span>
        </div>
    </div>

    <!-- Admin Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ count($users) }}</div>
            <p class="text-sm text-gray-500 mt-1">Total Pengguna Terdaftar</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-orange-50 rounded-lg text-orange-600">
                    <i data-lucide="file-warning" class="w-5 h-5"></i>
                </div>
                <span class="text-[10px] font-bold text-orange-600 uppercase bg-orange-100 px-2 py-0.5 rounded-full">Urgent</span>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ count($applications) }}</div>
            <p class="text-sm text-gray-500 mt-1">Antrean Verifikasi Dokumen</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-green-50 rounded-lg text-green-600">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-900">1.2k+</div>
            <p class="text-sm text-gray-500 mt-1">Produk Makanan Aktif</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                    <i data-lucide="leaf" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-900">15.4 Ton</div>
            <p class="text-sm text-gray-500 mt-1">Food Waste Terkurangi</p>
        </div>
    </div>

    <!-- Verification Quick Access -->
    @if(count($applications) > 0)
    <div class="bg-gradient-to-r from-[#174413] to-[#256a1f] rounded-3xl p-8 text-white">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex-1 text-center md:text-left">
                <h2 class="text-2xl font-bold mb-2">Pemberitahuan Sistem</h2>
                <p class="text-green-100 max-w-xl">Ada <strong>{{ count($applications) }} aplikasi baru</strong> dari Mitra dan Lembaga Sosial yang membutuhkan validasi dokumen legalitas Anda.</p>
            </div>
            <a href="{{ route('admin.verification') }}" class="bg-white text-[#174413] px-6 py-3 rounded-xl font-bold text-sm hover:bg-green-50 transition active:scale-95 shadow-xl flex items-center gap-2">
                Buka Panel Verifikasi
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Newest Users -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="p-6 border-b flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">Pengguna Baru</h2>
                <a href="{{ route('admin.users') }}" class="text-sm font-bold text-green-600 hover:underline">Lihat Semua</a>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach(array_slice($users, 0, 5) as $user)
                    <div class="p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold">
                                {{ substr($user['name'], 0, 1) }}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">{{ $user['name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $user['email'] }}</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full capitalize {{ $user['type'] === 'admin' ? 'bg-purple-100 text-purple-700' : ($user['type'] === 'mitra' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700') }}">
                            {{ $user['type'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Activity Feed -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6 font-manrope">Log Aktivitas Sistem</h2>
            <div class="space-y-6">
                @foreach([
                    ['icon' => 'user-plus', 'color' => 'bg-green-100 text-green-600', 'title' => 'Pendaftaran Mitra Baru', 'desc' => 'Toko Roti Barokah mendaftarkan akun.', 'time' => 'Baru saja'],
                    ['icon' => 'check-circle', 'color' => 'bg-blue-100 text-blue-600', 'title' => 'Verifikasi Selesai', 'desc' => 'Lembaga Yatim Piatu berhasil diverifikasi.', 'time' => '10 menit yang lalu'],
                    ['icon' => 'shopping-bag', 'color' => 'bg-orange-100 text-orange-600', 'title' => 'Lonjakan Pesanan', 'desc' => 'Frekuensi booking naik 15% pagi ini.', 'time' => '1 jam yang lalu'],
                ] as $log)
                <div class="flex gap-4">
                    <div class="h-10 w-10 rounded-full {{ $log['color'] }} flex items-center justify-center flex-shrink-0">
                        <i data-lucide="{{ $log['icon'] }}" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-gray-900">{{ $log['title'] }}</div>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $log['desc'] }}</p>
                        <div class="text-[10px] text-gray-400 mt-2">{{ $log['time'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
