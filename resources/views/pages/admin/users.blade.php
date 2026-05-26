@extends('layouts.dashboard')

@section('content')
@php
$totalUsers = count($allUsers);
$totalKonsumen = count(array_filter($allUsers, fn($u) => $u['type'] === 'consumer'));
$totalMitra = count(array_filter($allUsers, fn($u) => $u['type'] === 'mitra'));
$totalLembaga = count(array_filter($allUsers, fn($u) => $u['type'] === 'lembaga'));
$totalAktif = count(array_filter($allUsers, fn($u) => $u['status'] === 'active'));
$totalWarning = count(array_filter($allUsers, fn($u) => $u['status'] === 'warned' || $u['warnings'] > 0));
$totalBlocked = count(array_filter($allUsers, fn($u) => $u['status'] === 'blocked'));
@endphp
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Data User</h1>
            <p class="text-gray-600 mt-1">Kelola akun & moderasi pelanggaran</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center">
            <div class="text-2xl font-bold text-gray-900">{{ $totalUsers }}</div>
            <p class="text-xs text-gray-500 mt-1">Total User</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-blue-100 shadow-sm text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $totalKonsumen }}</div>
            <p class="text-xs text-blue-500 mt-1">Konsumen</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-green-100 shadow-sm text-center">
            <div class="text-2xl font-bold text-green-600">{{ $totalMitra }}</div>
            <p class="text-xs text-green-500 mt-1">Mitra</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-purple-100 shadow-sm text-center">
            <div class="text-2xl font-bold text-purple-600">{{ $totalLembaga }}</div>
            <p class="text-xs text-purple-500 mt-1">Lembaga</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-emerald-100 shadow-sm text-center">
            <div class="text-2xl font-bold text-emerald-600">{{ $totalAktif }}</div>
            <p class="text-xs text-emerald-500 mt-1">Aktif</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-orange-100 shadow-sm text-center">
            <div class="text-2xl font-bold text-orange-600">{{ $totalWarning }}</div>
            <p class="text-xs text-orange-500 mt-1">Warning</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-red-100 shadow-sm text-center">
            <div class="text-2xl font-bold text-red-600">{{ $totalBlocked }}</div>
            <p class="text-xs text-red-500 mt-1">Blocked</p>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row gap-4">
        <form action="{{ route('admin.users') }}" method="GET" class="flex-1 flex flex-col sm:flex-row gap-4">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none bg-gray-50/50">
            </div>
            <select name="type" class="px-4 py-2 border border-gray-200 rounded-xl bg-gray-50/50 focus:ring-2 focus:ring-green-500 outline-none" onchange="this.form.submit()">
                <option value="all" {{ request('type') === 'all' ? 'selected' : '' }}>Semua Tipe</option>
                <option value="consumer" {{ request('type') === 'consumer' ? 'selected' : '' }}>Konsumen</option>
                <option value="mitra" {{ request('type') === 'mitra' ? 'selected' : '' }}>Mitra</option>
                <option value="lembaga" {{ request('type') === 'lembaga' ? 'selected' : '' }}>Lembaga</option>
            </select>
            <select name="status" class="px-4 py-2 border border-gray-200 rounded-xl bg-gray-50/50 focus:ring-2 focus:ring-green-500 outline-none" onchange="this.form.submit()">
                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="warned" {{ request('status') === 'warned' ? 'selected' : '' }}>Warning</option>
                <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Diblokir</option>
            </select>
        </form>
    </div>

    <!-- Users List -->
    <div class="space-y-4">
        @forelse($users as $user)
            @php
                $roleBadgeClass = match($user['type']) {
                    'consumer' => 'bg-blue-100 text-blue-700',
                    'mitra' => 'bg-green-100 text-green-700',
                    'lembaga' => 'bg-purple-100 text-purple-700',
                    default => 'bg-gray-100 text-gray-700'
                };
                $statusBadgeClass = match($user['status']) {
                    'active' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    'warned' => 'bg-orange-100 text-orange-700 border-orange-200',
                    'blocked' => 'bg-red-100 text-red-700 border-red-200',
                    default => 'bg-gray-100 text-gray-700 border-gray-200'
                };
            @endphp
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                    <!-- User Info -->
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-bold text-gray-900">{{ $user['name'] }}</h3>
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-md capitalize {{ $roleBadgeClass }}">
                                {{ $user['type'] === 'consumer' ? 'Konsumen' : ($user['type'] === 'mitra' ? 'Mitra' : 'Lembaga') }}
                            </span>
                            @if($user['type'] !== 'consumer' && $user['verified'])
                                <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-blue-50 text-blue-600 flex items-center gap-1 border border-blue-100">
                                    <i data-lucide="check-circle" class="w-3 h-3"></i> Verified
                                </span>
                            @endif
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-md border {{ $statusBadgeClass }}">
                                @if($user['status'] === 'warned')
                                    <span class="flex items-center gap-1"><i data-lucide="alert-triangle" class="w-3 h-3"></i> {{ $user['warnings'] }} Peringatan</span>
                                @elseif($user['status'] === 'blocked')
                                    <span class="flex items-center gap-1"><i data-lucide="ban" class="w-3 h-3"></i> Diblokir</span>
                                @else
                                    Aktif
                                @endif
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                            <div class="flex items-center gap-1.5"><i data-lucide="mail" class="w-4 h-4"></i> {{ $user['email'] }}</div>
                            <div class="flex items-center gap-1.5"><i data-lucide="phone" class="w-4 h-4"></i> {{ $user['phone'] ?? '-' }}</div>
                            <div class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-4 h-4"></i> Bergabung: {{ $user['joined_at'] }}</div>
                        </div>
                    </div>
                    
                    <!-- Stats -->
                    <div class="text-right flex-shrink-0">
                        <div class="text-2xl font-bold text-green-600">{{ $user['transactions'] }}</div>
                        <div class="text-xs text-gray-500">Transaksi</div>
                    </div>
                </div>

                <!-- Warning/Blocked Alerts -->
                @if($user['status'] === 'warned' || $user['warnings'] > 0)
                    <div class="mt-4 bg-orange-50/50 border border-orange-100 rounded-xl p-4 flex gap-3">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-orange-500 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <div class="text-sm font-bold text-orange-900">Peringatan Terakhir: {{ $user['last_warning'] ?? 'Belum ada data' }}</div>
                            <p class="text-sm text-orange-700 mt-1">{{ $user['warning_reason'] ?? 'Pelanggaran ketentuan sistem.' }}</p>
                        </div>
                    </div>
                @endif

                @if($user['status'] === 'blocked')
                    <div class="mt-4 bg-red-50/50 border border-red-100 rounded-xl p-4 flex gap-3">
                        <i data-lucide="ban" class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <div class="text-sm font-bold text-red-900">Diblokir: {{ $user['blocked_at'] ?? 'Belum ada data' }}</div>
                            <p class="text-sm text-red-700 mt-1"><span class="font-bold">Alasan:</span> {{ $user['block_reason'] ?? 'Pelanggaran berat terhadap ketentuan ShareMeal.' }}</p>
                        </div>
                    </div>
                @endif

                <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap gap-2">
                    @if($user['status'] !== 'blocked')
                        <form action="{{ route('admin.users.warn', $user['id']) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 px-4 py-2 border border-orange-200 text-orange-600 bg-orange-50 rounded-lg hover:bg-orange-100 transition text-sm font-bold active:scale-95">
                                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                Beri Peringatan
                            </button>
                        </form>
                        
                        <!-- Blokir Form & Modal Trigger -->
                        <button type="button" onclick="openBlockModal({{ $user['id'] }})" class="flex items-center gap-2 px-4 py-2 border border-red-200 text-red-600 hover:bg-red-50 rounded-lg transition text-sm font-bold active:scale-95">
                            <i data-lucide="ban" class="w-4 h-4"></i>
                            Blokir Akun
                        </button>
                    @else
                        <form action="{{ route('admin.users.unblock', $user['id']) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-bold active:scale-95 shadow-lg shadow-green-200">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                Buka Blokir
                            </button>
                        </form>
                    @endif
                    
                    <button type="button" class="flex items-center gap-2 px-4 py-2 border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-lg transition text-sm font-bold active:scale-95 ml-auto md:ml-0">
                        Lihat Detail
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-white p-8 rounded-2xl border border-gray-100 text-center text-gray-500">
                <i data-lucide="users" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
                <p>Tidak ada pengguna yang ditemukan.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal Blokir User -->
<div id="blockModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closeBlockModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-2xl shadow-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Blokir Pengguna</h3>
            <button type="button" onclick="closeBlockModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="blockForm" method="POST" action="">
            @csrf
            <div class="space-y-4">
                <div class="bg-red-50 text-red-600 p-3 rounded-xl text-sm flex gap-2">
                    <i data-lucide="alert-octagon" class="w-5 h-5 flex-shrink-0"></i>
                    <p>Memblokir pengguna akan mencegah mereka untuk login dan menggunakan layanan ShareMeal.</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Alasan Pemblokiran</label>
                    <textarea name="reason" rows="3" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 outline-none resize-none" placeholder="Masukkan alasan pemblokiran (mis. Penipuan, Menjual makanan tidak layak)..." required></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeBlockModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-xl font-bold transition">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition">Blokir Akun</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openBlockModal(userId) {
        document.getElementById('blockModal').classList.remove('hidden');
        document.getElementById('blockForm').action = "{{ url('admin/users') }}/" + userId + "/block";
    }

    function closeBlockModal() {
        document.getElementById('blockModal').classList.add('hidden');
    }
</script>
@endsection
