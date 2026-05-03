@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen pb-20">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-green-700 font-bold text-xs uppercase tracking-[0.2em]">
                <span class="w-8 h-[2px] bg-green-700"></span>
                Transaction Monitor
            </div>
            <h1 class="text-4xl font-black text-gray-900 tracking-tight leading-none">
                Pemantauan <span class="text-green-700">Transaksi</span>
            </h1>
            <p class="text-gray-500 font-medium">Monitoring seluruh aktivitas transaksi dan donasi (PBI 25)</p>
        </div>
        <div class="flex gap-3">
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" placeholder="Cari ID transaksi..." class="pl-12 pr-6 py-4 bg-white border border-gray-100 rounded-2xl text-sm focus:ring-2 focus:ring-green-500/20 shadow-sm w-64">
            </div>
            <button class="bg-white text-gray-700 px-6 py-4 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all flex items-center gap-2 font-bold text-sm">
                <i data-lucide="filter" class="w-4 h-4"></i>
                Filter
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <div class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-green-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Transaksi</p>
                <h3 class="text-3xl font-black text-gray-900 mt-1">{{ $stats['total_transaksi'] }}</h3>
                <p class="text-[10px] text-green-500 font-bold mt-2">+5.4% dari kemarin</p>
            </div>
        </div>
        <div class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Selesai</p>
            <h3 class="text-3xl font-black text-gray-900 mt-1">{{ $stats['total_selesai'] }}</h3>
        </div>
        <div class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Pending</p>
            <h3 class="text-3xl font-black text-orange-500 mt-1">{{ $stats['total_pending'] }}</h3>
        </div>
        <div class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm bg-gradient-to-br from-green-50 to-white">
            <p class="text-xs font-bold text-green-700 uppercase tracking-widest">Gross Merchandise Value</p>
            <h3 class="text-3xl font-black text-green-800 mt-1">{{ $stats['gmv'] }}</h3>
        </div>
    </div>

    <!-- Transaction Table -->
    <div class="bg-white rounded-[40px] border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">ID Transaksi</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pembeli</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Mitra</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Waktu</th>
                        <th class="px-8 py-5 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($transactions as $trx)
                    <tr class="hover:bg-gray-50/30 transition-colors group">
                        <td class="px-8 py-6 font-black text-gray-400">#{{ $trx->id }}</td>
                        <td class="px-8 py-6">
                            <div class="font-bold text-gray-900">{{ $trx->customer->name }}</div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="font-bold text-gray-700">{{ $trx->mitra->name }}</div>
                        </td>
                        <td class="px-8 py-6 font-black text-gray-900">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                        <td class="px-8 py-6">
                            <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $trx->status === 'completed' ? 'bg-green-500 text-white' : 'bg-orange-500 text-white' }}">
                                {{ $trx->status }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-sm font-bold text-gray-500">{{ $trx->created_at->diffForHumans() }}</td>
                        <td class="px-8 py-6">
                            <div class="flex justify-center">
                                <button class="p-2 text-gray-400 hover:text-green-700 hover:bg-green-50 rounded-xl transition-all">
                                    <i data-lucide="eye" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="p-8 border-t border-gray-50 flex justify-between items-center bg-gray-50/30">
            <p class="text-sm font-bold text-gray-400">Menampilkan 1-10 dari 5420 transaksi</p>
            <div class="flex gap-2">
                <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-gray-900 transition-colors">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </button>
                <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#1a4414] text-white font-bold">1</button>
                <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-700 font-bold hover:bg-gray-50">2</button>
                <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-700 font-bold hover:bg-gray-50">3</button>
                <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-gray-900 transition-colors">
                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
