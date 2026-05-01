@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">{{ $shell['title'] }}</h1>
            <p class="text-gray-500 mt-1">{{ $shell['subtitle'] }}</p>
        </div>
    </div>

    <!-- Filters & Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div class="lg:col-span-3 bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row gap-4 justify-between items-center">
            <div class="flex p-1 bg-gray-50 rounded-xl w-full sm:w-auto overflow-x-auto">
                <a href="?type=all" class="px-4 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap {{ $type === 'all' ? 'bg-white text-[#174413] shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">Semua</a>
                <a href="?type=consumer" class="px-4 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap {{ $type === 'consumer' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">Konsumen</a>
                <a href="?type=mitra" class="px-4 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap {{ $type === 'mitra' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">Mitra</a>
                <a href="?type=lembaga" class="px-4 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap {{ $type === 'lembaga' ? 'bg-white text-orange-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">Lembaga</a>
            </div>
            <div class="relative w-full sm:w-72">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <form action="{{ route('admin.users') }}" method="GET">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau email..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                </form>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-center">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900">{{ count($allUsers) }}</div>
                <div class="text-xs text-gray-500 font-medium">Total Terdaftar</div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50/50 text-gray-500 font-bold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Tipe Akun</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Transaksi</th>
                        <th class="px-6 py-4">Warning</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50/50 transition group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#174413] text-white flex items-center justify-center font-bold text-sm">
                                    {{ substr($user['name'], 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900 group-hover:text-green-700 transition">{{ $user['name'] }}</span>
                                    <span class="text-xs text-gray-500">{{ $user['email'] }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($user['type'] === 'mitra')
                                <span class="px-2 py-1 rounded bg-green-50 text-green-600 text-[10px] font-bold uppercase tracking-wider">Mitra</span>
                            @elseif($user['type'] === 'lembaga')
                                <span class="px-2 py-1 rounded bg-orange-50 text-orange-600 text-[10px] font-bold uppercase tracking-wider">Lembaga</span>
                            @elseif($user['type'] === 'admin')
                                <span class="px-2 py-1 rounded bg-purple-50 text-purple-600 text-[10px] font-bold uppercase tracking-wider">Admin</span>
                            @else
                                <span class="px-2 py-1 rounded bg-blue-50 text-blue-600 text-[10px] font-bold uppercase tracking-wider">Konsumen</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($user['status'] === 'active')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-50 text-green-700 border border-green-100">
                                    Aktif
                                </span>
                            @elseif($user['status'] === 'warned')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-orange-50 text-orange-700 border border-orange-100">
                                    Peringatan
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-100">
                                    Terblokir
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-700">
                            {{ $user['transactions'] }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="{{ $user['warnings'] > 0 ? 'text-red-600 font-bold' : 'text-gray-400' }}">
                                {{ $user['warnings'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($user['type'] !== 'admin')
                                <div class="flex justify-end gap-2">
                                    <form action="{{ route('admin.users.warn', $user['id']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="p-2 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition cursor-pointer" title="Beri Peringatan">
                                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    @if($user['status'] === 'blocked')
                                        <form action="{{ route('admin.users.unblock', $user['id']) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition cursor-pointer" title="Buka Blokir">
                                                <i data-lucide="unlock" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button @click="if(confirm('Blokir user {{ $user['name'] }}?')) { 
                                            const reason = prompt('Alasan pemblokiran:');
                                            if(reason) {
                                                const form = document.createElement('form');
                                                form.method = 'POST';
                                                form.action = '{{ url('admin/users') }}/{{ $user['id'] }}/block';
                                                form.innerHTML = `<input type='hidden' name='_token' value='{{ csrf_token() }}'><input type='hidden' name='reason' value='${reason}'>`;
                                                document.body.appendChild(form);
                                                form.submit();
                                            }
                                        }" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Blokir User">
                                            <i data-lucide="user-x" class="w-4 h-4"></i>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            Tidak ada user ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
