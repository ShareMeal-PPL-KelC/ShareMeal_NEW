@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">{{ $shell['title'] }}</h1>
            <p class="text-gray-500 mt-1">{{ $shell['subtitle'] }}</p>
        </div>
        <div class="flex gap-2">
            <button onclick="alert('File CSV berhasil diunduh (Simulasi)')" class="bg-white text-gray-700 px-4 py-2 border border-gray-200 rounded-xl shadow-sm hover:bg-gray-50 transition flex items-center gap-2 font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-green-500">
                <i data-lucide="download" class="w-4 h-4"></i>
                Export CSV
            </button>
        </div>
    </div>

    <!-- Stats Grid (Figma Style) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center hover:shadow-md transition">
            <div class="text-3xl font-bold text-blue-600 mb-2">{{ $stats['total_transaksi'] }}</div>
            <div class="text-xs text-gray-500">Total Transaksi</div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center hover:shadow-md transition">
            <div class="text-3xl font-bold text-green-600 mb-2">{{ $stats['total_selesai'] }}</div>
            <div class="text-xs text-gray-500">Transaksi Selesai</div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center hover:shadow-md transition">
            <div class="text-3xl font-bold text-orange-600 mb-2">{{ $stats['total_pending'] }}</div>
            <div class="text-xs text-gray-500">Transaksi Pending</div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center hover:shadow-md transition">
            <div class="text-3xl font-bold text-[#e85d04] mb-2">{{ $stats['gmv'] }}</div>
            <div class="text-xs text-gray-500">Total GMV Platform</div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="text-lg font-bold text-gray-900">Riwayat Transaksi</h2>
            <div class="relative w-full sm:w-72">
                <i data-lucide="search" class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" placeholder="Cari ID, Konsumen, atau Mitra..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50/50 text-gray-500 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs">ID Transaksi</th>
                        <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs">Konsumen & Mitra</th>
                        <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs">Total Harga</th>
                        <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs">Status</th>
                        <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs">Waktu</th>
                        <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $trx)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4">
                            <span class="font-bold text-gray-900">TRX-{{ str_pad($trx->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-semibold text-gray-900">{{ $trx->customer->name ?? 'User Tidak Diketahui' }}</span>
                                <span class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                    <i data-lucide="store" class="w-3 h-3"></i> {{ $trx->mitra->name ?? 'Mitra Tidak Diketahui' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-900">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($trx->status === 'completed')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Selesai
                                </span>
                            @elseif($trx->status === 'pending')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-50 text-orange-700 border border-orange-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Menunggu
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Dibatalkan
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-900">{{ $trx->created_at->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $trx->created_at->format('H:i') }} WIB</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="alert('Menampilkan detail transaksi TRX-{{ str_pad($trx->id, 5, '0', STR_PAD_LEFT) }} (Simulasi)')" class="p-2 text-gray-400 hover:text-blue-600 bg-white hover:bg-blue-50 border border-transparent hover:border-blue-100 rounded-lg transition cursor-pointer" title="Lihat Detail">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="bg-gray-50 w-16 h-16 rounded-full flex items-center justify-center mb-4">
                                    <i data-lucide="inbox" class="w-8 h-8 text-gray-400"></i>
                                </div>
                                <p class="font-medium text-gray-900">Belum ada transaksi</p>
                                <p class="text-sm mt-1">Data transaksi akan muncul di sini saat ada pesanan masuk.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->count() > 0)
        <div class="p-4 border-t border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-500">Menampilkan halaman <span class="font-bold text-gray-900">{{ $page }}</span> dari 2 halaman.</p>
            <div class="flex gap-1">
                @if($page == 1)
                    <span class="px-3 py-1 border border-gray-200 rounded text-sm text-gray-500 opacity-50 cursor-not-allowed bg-gray-50">Prev</span>
                @else
                    <a href="?page={{ $page - 1 }}" class="px-3 py-1 border border-gray-200 rounded text-sm text-gray-700 hover:bg-gray-50 transition cursor-pointer">Prev</a>
                @endif

                <a href="?page=1" class="px-3 py-1 border border-gray-200 rounded text-sm transition cursor-pointer {{ $page == 1 ? 'bg-[#174413] text-white border-[#174413]' : 'text-gray-700 hover:bg-gray-50' }}">1</a>
                <a href="?page=2" class="px-3 py-1 border border-gray-200 rounded text-sm transition cursor-pointer {{ $page == 2 ? 'bg-[#174413] text-white border-[#174413]' : 'text-gray-700 hover:bg-gray-50' }}">2</a>

                @if($page == 2)
                    <span class="px-3 py-1 border border-gray-200 rounded text-sm text-gray-500 opacity-50 cursor-not-allowed bg-gray-50">Next</span>
                @else
                    <a href="?page={{ $page + 1 }}" class="px-3 py-1 border border-gray-200 rounded text-sm text-gray-700 hover:bg-gray-50 transition cursor-pointer">Next</a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
