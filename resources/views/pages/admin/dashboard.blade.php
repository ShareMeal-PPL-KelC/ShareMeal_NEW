@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Dashboard Admin</h1>
        <p class="text-gray-500 mt-1">Kelola sistem, verifikasi akun, dan moderasi platform</p>
    </div>

    <!-- Stats Grid 6 Items -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center">
            <div class="text-2xl font-bold text-blue-600 mb-1">{{ $stats['total_user'] }}</div>
            <div class="text-xs text-gray-500">Total User</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center">
            <div class="text-2xl font-bold text-orange-600 mb-1">{{ $stats['pending'] }}</div>
            <div class="text-xs text-gray-500">Pending</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center">
            <div class="text-2xl font-bold text-green-600 mb-1">{{ $stats['mitra_aktif'] }}</div>
            <div class="text-xs text-gray-500">Mitra Aktif</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center">
            <div class="text-2xl font-bold text-purple-600 mb-1">{{ $stats['lembaga_aktif'] }}</div>
            <div class="text-xs text-gray-500">Lembaga Aktif</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center">
            <div class="text-2xl font-bold text-blue-600 mb-1">{{ $stats['transaksi'] }}</div>
            <div class="text-xs text-gray-500">Transaksi</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center">
            <div class="text-2xl font-bold text-green-600 mb-1">{{ $stats['makanan_saved'] }}</div>
            <div class="text-xs text-gray-500">Makanan Saved</div>
        </div>
    </div>

    <!-- Alert Verification -->
    <div class="bg-orange-50 border border-orange-100 rounded-xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="text-orange-600 mt-0.5">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="font-bold text-orange-800 text-sm">15 Pendaftaran Menunggu Verifikasi (FR-18)</h3>
                <p class="text-xs text-orange-600 mt-1">Terdapat 15 mitra dan lembaga sosial baru yang perlu diverifikasi.</p>
            </div>
        </div>
        <a href="{{ route('admin.verification') }}" class="whitespace-nowrap bg-white text-gray-700 text-sm font-bold border border-gray-200 px-4 py-2 rounded-lg hover:bg-gray-50 transition">
            Verifikasi Sekarang
        </a>
    </div>

    <!-- Two Columns: Pending & Activities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Pending Verification List -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm flex flex-col">
            <div class="p-5 flex items-center justify-between border-b border-gray-50">
                <div class="flex items-center gap-2">
                    <i data-lucide="shield" class="w-5 h-5 text-orange-500"></i>
                    <h2 class="font-bold text-gray-900">Pending Verifikasi (FR-18)</h2>
                </div>
                <a href="{{ route('admin.verification') }}" class="text-xs font-bold text-gray-600 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50">Lihat Semua</a>
            </div>
            <div class="p-5 space-y-4 flex-1">
                <!-- Item 1 -->
                <div class="border border-gray-100 rounded-xl p-4 flex justify-between items-center bg-white hover:border-green-200 transition">
                    <div>
                        <h4 class="font-bold text-gray-900 text-sm mb-1">Toko Roti Sejahtera</h4>
                        <p class="text-xs text-gray-500">Mitra • 3 dokumen</p>
                        <p class="text-[10px] text-gray-400 mt-1">Diajukan: 2026-03-31 09:00</p>
                    </div>
                    <button class="bg-[#0f172a] text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-slate-800 transition">Review</button>
                </div>
                <!-- Item 2 -->
                <div class="border border-gray-100 rounded-xl p-4 flex justify-between items-center bg-white hover:border-green-200 transition">
                    <div>
                        <h4 class="font-bold text-gray-900 text-sm mb-1">Yayasan Harapan Bangsa</h4>
                        <p class="text-xs text-gray-500">Lembaga Sosial • 4 dokumen</p>
                        <p class="text-[10px] text-gray-400 mt-1">Diajukan: 2026-03-31 08:30</p>
                    </div>
                    <button class="bg-[#0f172a] text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-slate-800 transition">Review</button>
                </div>
                <!-- Item 3 -->
                <div class="border border-gray-100 rounded-xl p-4 flex justify-between items-center bg-white hover:border-green-200 transition">
                    <div>
                        <h4 class="font-bold text-gray-900 text-sm mb-1">Healthy Cafe</h4>
                        <p class="text-xs text-gray-500">Mitra • 3 dokumen</p>
                        <p class="text-[10px] text-gray-400 mt-1">Diajukan: 2026-03-30 16:45</p>
                    </div>
                    <button class="bg-[#0f172a] text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-slate-800 transition">Review</button>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm flex flex-col">
            <div class="p-5 flex items-center gap-2 border-b border-gray-50">
                <i data-lucide="trending-up" class="w-5 h-5 text-blue-500"></i>
                <h2 class="font-bold text-gray-900">Aktivitas Terbaru</h2>
            </div>
            <div class="p-5 space-y-4 flex-1">
                @foreach($activities as $activity)
                    @php
                        $iconColor = match($activity['type']) {
                            'success' => 'text-green-500 bg-green-50',
                            'warning' => 'text-orange-500 bg-orange-50',
                            'danger' => 'text-red-500 bg-red-50',
                            default => 'text-blue-500 bg-blue-50',
                        };
                    @endphp
                    <div class="flex items-start gap-3 bg-gray-50 rounded-xl p-4">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $iconColor }}">
                            <i data-lucide="{{ $activity['icon'] }}" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 text-sm">{{ $activity['title'] }}</h4>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $activity['description'] }}</p>
                            <p class="text-[10px] text-gray-400 mt-1.5">{{ $activity['time'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Dampak Platform -->
    <div class="bg-[#f4fbf9] rounded-xl border border-[#e8f5f1] p-6 shadow-sm">
        <h2 class="font-bold text-gray-900 mb-6">Dampak Platform</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 text-center divide-x divide-gray-200/60">
            <div class="px-4">
                <div class="text-3xl font-bold text-green-600 mb-2">{{ $stats['makanan_saved'] }}</div>
                <div class="text-xs text-gray-500">Makanan Diselamatkan (kg)</div>
            </div>
            <div class="px-4">
                <div class="text-3xl font-bold text-blue-600 mb-2">{{ $stats['co2_dikurangi'] }}</div>
                <div class="text-xs text-gray-500">CO₂ Dikurangi (kg)</div>
            </div>
            <div class="px-4">
                <div class="text-3xl font-bold text-purple-600 mb-2">{{ $stats['transaksi'] }}</div>
                <div class="text-xs text-gray-500">Total Transaksi</div>
            </div>
            <div class="px-4">
                <div class="text-3xl font-bold text-orange-600 mb-2">{{ $stats['gmv_platform'] }}</div>
                <div class="text-xs text-gray-500">GMV Platform</div>
            </div>
        </div>
    </div>

    <!-- Aksi Cepat -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h2 class="font-bold text-gray-900 mb-4">Aksi Cepat</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <a href="{{ route('admin.verification') }}" class="flex items-center justify-center gap-2 border border-gray-200 rounded-lg py-3 px-4 hover:bg-gray-50 hover:border-gray-300 transition group text-sm">
                <i data-lucide="shield" class="w-4 h-4 text-gray-500 group-hover:text-gray-900"></i>
                <span class="font-medium text-gray-700 group-hover:text-gray-900">Verifikasi Akun</span>
            </a>
            <a href="{{ route('admin.users') }}" class="flex items-center justify-center gap-2 border border-gray-200 rounded-lg py-3 px-4 hover:bg-gray-50 hover:border-gray-300 transition group text-sm">
                <i data-lucide="users" class="w-4 h-4 text-gray-500 group-hover:text-gray-900"></i>
                <span class="font-medium text-gray-700 group-hover:text-gray-900">Kelola User</span>
            </a>
            <a href="{{ route('admin.reports') }}" class="flex items-center justify-center gap-2 border border-gray-200 rounded-lg py-3 px-4 hover:bg-gray-50 hover:border-gray-300 transition group text-sm">
                <i data-lucide="line-chart" class="w-4 h-4 text-gray-500 group-hover:text-gray-900"></i>
                <span class="font-medium text-gray-700 group-hover:text-gray-900">Lihat Laporan</span>
            </a>
            <button class="flex items-center justify-center gap-2 border border-gray-200 rounded-lg py-3 px-4 hover:bg-gray-50 hover:border-gray-300 transition group text-sm">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-gray-500 group-hover:text-gray-900"></i>
                <span class="font-medium text-gray-700 group-hover:text-gray-900">Moderasi Konten</span>
            </button>
        </div>
    </div>
</div>
@endsection
