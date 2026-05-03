@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen pb-20">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-green-700 font-bold text-xs uppercase tracking-[0.2em]">
                <span class="w-8 h-[2px] bg-green-700"></span>
                System Moderation
            </div>
            <h1 class="text-4xl font-black text-gray-900 tracking-tight leading-none">
                Kelola <span class="text-green-700">Pengguna</span>
            </h1>
            <p class="text-gray-500 font-medium">Manajemen akun mitra, konsumen, dan lembaga (PBI 24)</p>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <div class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total User</p>
            <h3 class="text-3xl font-black text-gray-900 mt-1">{{ count($users) }}</h3>
        </div>
        <div class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Mitra Aktif</p>
            <h3 class="text-3xl font-black text-green-600 mt-1">{{ $users->where('type', 'mitra')->count() }}</h3>
        </div>
        <div class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Lembaga Terverifikasi</p>
            <h3 class="text-3xl font-black text-blue-600 mt-1">{{ $users->where('type', 'lembaga')->count() }}</h3>
        </div>
        <div class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">User Terblokir</p>
            <h3 class="text-3xl font-black text-red-600 mt-1">{{ $users->where('status', 'blocked')->count() }}</h3>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-[40px] border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex gap-2">
                <button class="px-6 py-2 bg-gray-900 text-white rounded-xl text-sm font-bold">Semua</button>
                <button class="px-6 py-2 bg-gray-50 text-gray-500 rounded-xl text-sm font-bold hover:bg-gray-100">Mitra</button>
                <button class="px-6 py-2 bg-gray-50 text-gray-500 rounded-xl text-sm font-bold hover:bg-gray-100">Konsumen</button>
            </div>
            <div class="relative w-full md:w-80">
                <i data-lucide="search" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" placeholder="Cari nama atau email..." class="w-full pl-12 pr-6 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-green-500/20">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/50 text-left">
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Informasi User</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Role</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Bergabung</th>
                        <th class="px-8 py-5 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($users as $user)
                    <tr class="hover:bg-gray-50/30 transition-colors">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-bold">
                                    {{ substr($user['name'], 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900">{{ $user['name'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $user['email'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="px-3 py-1 bg-gray-100 rounded-full text-[10px] font-black uppercase tracking-widest text-gray-600">
                                {{ $user['type'] }}
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full {{ $user['status'] === 'active' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                <span class="text-sm font-bold text-gray-700 capitalize">{{ $user['status'] }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-sm font-bold text-gray-500">{{ $user['joined_at'] }}</td>
                        <td class="px-8 py-6">
                            <div class="flex justify-center gap-2">
                                <button class="p-2 text-orange-600 hover:bg-orange-50 rounded-lg transition-colors" title="Beri Peringatan">
                                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                </button>
                                @if($user['status'] === 'active')
                                <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Blokir Akun">
                                    <i data-lucide="ban" class="w-4 h-4"></i>
                                </button>
                                @else
                                <button class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Buka Blokir">
                                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
