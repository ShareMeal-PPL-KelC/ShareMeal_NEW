@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Lembaga</h1>
            <p class="text-gray-600 mt-1">Kelola penyaluran donasi makanan untuk masyarakat</p>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500 bg-white px-4 py-2 rounded-lg border border-gray-100 shadow-sm">
            <i data-lucide="user" class="w-4 h-4"></i>
            <span>{{ $shell['userName'] }}</span>
        </div>
    </div>


    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition text-center">
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="heart" class="w-6 h-6 text-blue-600"></i>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $stats->totalDonations }}</div>
            <p class="text-xs font-bold text-gray-400 uppercase mt-1">Total Donasi</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition text-center">
            <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="package" class="w-6 h-6 text-orange-600"></i>
            </div>
            <div class="text-3xl font-bold text-orange-600">{{ $stats->activeDonations }}</div>
            <p class="text-xs font-bold text-gray-400 uppercase mt-1">Aktif</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition text-center">
            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="users" class="w-6 h-6 text-green-600"></i>
            </div>
            <div class="text-3xl font-bold text-green-600">{{ $stats->beneficiaries }}</div>
            <p class="text-xs font-bold text-gray-400 uppercase mt-1">Penerima Manfaat</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition text-center">
            <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="calendar" class="w-6 h-6 text-purple-600"></i>
            </div>
            <div class="text-3xl font-bold text-purple-600">{{ $stats->thisMonth }}</div>
            <p class="text-xs font-bold text-gray-400 uppercase mt-1">Bulan Ini</p>
        </div>
    </div>

    <!-- Recent Donations -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="history" class="w-5 h-5 text-gray-600"></i>
                <h2 class="text-xl font-bold text-gray-900 font-manrope">Donasi Terbaru</h2>
            </div>
            <a href="{{ route('lembaga.donations') }}" class="text-sm font-bold text-blue-600 hover:underline flex items-center gap-1">
                Lihat Semua
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>
        </div>
        <div class="p-6">
            @forelse($recentDonations as $donation)
                 <div class="flex items-center justify-between p-4 border border-gray-50 rounded-xl mb-3 hover:border-blue-200 transition">
                      <div class="flex items-center gap-4">
                           <div class="w-10 h-10 bg-gray-50 rounded-lg flex items-center justify-center text-gray-400">
                                <i data-lucide="package" class="w-5 h-5"></i>
                           </div>
                           <div>
                                <div class="font-bold text-gray-900">{{ $donation['store']['name'] }}</div>
                                <div class="text-xs text-gray-500">{{ count($donation['items']) }} Item makanan</div>
                           </div>
                      </div>
                      <div class="flex items-center gap-4">
                           <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">
                                {{ $donation['status'] }}
                           </span>
                           <a href="{{ route('lembaga.donations') }}" class="text-gray-400 hover:text-blue-600">
                                <i data-lucide="chevron-right" class="w-5 h-5"></i>
                           </a>
                      </div>
                 </div>
            @empty
            <div class="text-center py-12">
                 <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                      <i data-lucide="heart" class="w-8 h-8 text-gray-200"></i>
                 </div>
                 <p class="text-gray-500 font-medium italic">Belum ada donasi yang tersedia saat ini.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
