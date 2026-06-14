@extends('layouts.dashboard')

@section('content')
@php
$totalUsers = $stats['totalUsers'];
$totalKonsumen = $stats['totalKonsumen'];
$totalMitra = $stats['totalMitra'];
$totalLembaga = $stats['totalLembaga'];
$totalAktif = $stats['totalAktif'];
$totalWarning = $stats['totalWarning'];
$totalBlocked = $stats['totalBlocked'];
@endphp
<div class="space-y-8" x-data="{ 
    isActionDialogOpen: false,
    actionType: '',
    actionUrl: '',
    actionTitle: '',
    actionMessage: '',
    reason: '',
    currentStep: 1,

    isDetailOpen: false,
    detail: {},

    openDetail(user) {
        this.detail = user;
        this.isDetailOpen = true;
    },
    
    openConfirm(type, url, title, msg) {
        this.actionType = type;
        this.actionUrl = url;
        this.actionTitle = title;
        this.actionMessage = msg;
        this.reason = '';
        this.currentStep = 1;
        this.isActionDialogOpen = true;
    },
    
    nextStep() {
        this.currentStep = 2;
    },
    
    async submitAction() {
        this.currentStep = 3;
        
        try {
            const response = await fetch(this.actionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    reason: this.reason
                })
            });
            
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            if (response.ok) {
                this.currentStep = 4;
                setTimeout(() => {
                    this.isActionDialogOpen = false;
                    window.location.reload();
                }, 2000);
            } else {
                throw new Error('Gagal memproses tindakan');
            }
        } catch (error) {
            this.currentStep = 1;
            alert('Terjadi kesalahan: ' + error.message);
        }
    }
}">
    <!-- Decorative Glow Top Right -->
    <div class="absolute top-0 right-0 w-[30rem] h-[30rem] bg-emerald-100/10 rounded-full blur-[120px] pointer-events-none z-0"></div>

    <!-- Title Header -->
    <div class="relative z-10 mb-12 reveal">
        <h1 class="text-5xl font-serif font-black text-luxury-forest leading-tight tracking-tight">Manajemen Data User</h1>
        <p class="text-luxury-slate font-medium mt-3 text-lg leading-relaxed max-w-3xl">Kelola akun, pantau transaksi keaktifan, serta lakukan moderasi dan blokir pelanggaran secara real-time demi keamanan platform.</p>
    </div>

    @if(session('success'))
    <div class="relative z-10 bg-emerald-50 border border-emerald-200 text-emerald-700 px-6 py-4 rounded-[1.5rem] flex items-center gap-3 animate-in fade-in duration-300">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0 stroke-[2.5]"></i>
        <span class="text-sm font-semibold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Statistics Cards (7 columns) -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-5 mb-12 relative z-10">
        <!-- Total User -->
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group reveal text-center">
            <div class="w-11 h-11 bg-slate-50 text-slate-650 border border-slate-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-305 mx-auto">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <div class="text-2xl font-serif font-black text-luxury-forest leading-none">{{ $totalUsers }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Total User</div>
        </div>
        <!-- Konsumen -->
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group reveal text-center">
            <div class="w-11 h-11 bg-blue-50 text-blue-600 border border-blue-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-305 mx-auto">
                <i data-lucide="user" class="w-5 h-5"></i>
            </div>
            <div class="text-2xl font-serif font-black text-luxury-forest group-hover:text-blue-600 transition-colors leading-none">{{ $totalKonsumen }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Konsumen</div>
        </div>
        <!-- Mitra -->
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group reveal text-center">
            <div class="w-11 h-11 bg-green-50 text-green-600 border border-green-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-305 mx-auto">
                <i data-lucide="store" class="w-5 h-5"></i>
            </div>
            <div class="text-2xl font-serif font-black text-luxury-forest group-hover:text-green-600 transition-colors leading-none">{{ $totalMitra }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Mitra</div>
        </div>
        <!-- Lembaga -->
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group reveal text-center">
            <div class="w-11 h-11 bg-purple-50 text-purple-600 border border-purple-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-305 mx-auto">
                <i data-lucide="heart" class="w-5 h-5"></i>
            </div>
            <div class="text-2xl font-serif font-black text-luxury-forest group-hover:text-purple-600 transition-colors leading-none">{{ $totalLembaga }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Lembaga</div>
        </div>
        <!-- Aktif -->
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group reveal text-center">
            <div class="w-11 h-11 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-305 mx-auto">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div class="text-2xl font-serif font-black text-luxury-forest group-hover:text-emerald-600 transition-colors leading-none">{{ $totalAktif }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Aktif</div>
        </div>
        <!-- Warning -->
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group reveal text-center">
            <div class="w-11 h-11 bg-orange-50 text-orange-500 border border-orange-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-305 mx-auto animate-pulse">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
            <div class="text-2xl font-serif font-black text-luxury-forest group-hover:text-orange-500 transition-colors leading-none">{{ $totalWarning }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Warning</div>
        </div>
        <!-- Blocked -->
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group reveal text-center">
            <div class="w-11 h-11 bg-red-50 text-red-650 border border-red-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-305 mx-auto">
                <i data-lucide="ban" class="w-5 h-5"></i>
            </div>
            <div class="text-2xl font-serif font-black text-luxury-forest group-hover:text-red-650 transition-colors leading-none">{{ $totalBlocked }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Blocked</div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="glass-card rounded-[2rem] p-6 mb-8 relative z-10 reveal">
        <form action="{{ route('admin.users') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-center">
            <div class="relative flex-1 w-full">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-luxury-slate/60"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." 
                       class="w-full pl-12 pr-4 py-4 border border-luxury-alabas/85 rounded-2xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 outline-none bg-white/40 font-medium text-luxury-forest placeholder:text-luxury-slate/40 transition duration-300">
            </div>
            <div class="flex gap-4 w-full md:w-auto shrink-0">
                <select name="type" class="flex-1 md:flex-none px-5 py-4 border border-luxury-alabas/85 rounded-2xl bg-white/40 text-xs font-black uppercase tracking-wider text-luxury-forest focus:ring-2 focus:ring-emerald-500/30 outline-none cursor-pointer" onchange="this.form.submit()">
                    <option value="all" {{ request('type') === 'all' ? 'selected' : '' }}>Semua Tipe</option>
                    <option value="consumer" {{ request('type') === 'consumer' ? 'selected' : '' }}>Konsumen</option>
                    <option value="mitra" {{ request('type') === 'mitra' ? 'selected' : '' }}>Mitra</option>
                    <option value="lembaga" {{ request('type') === 'lembaga' ? 'selected' : '' }}>Lembaga</option>
                </select>
                <select name="status" class="flex-1 md:flex-none px-5 py-4 border border-luxury-alabas/85 rounded-2xl bg-white/40 text-xs font-black uppercase tracking-wider text-luxury-forest focus:ring-2 focus:ring-emerald-500/30 outline-none cursor-pointer" onchange="this.form.submit()">
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="warned" {{ request('status') === 'warned' ? 'selected' : '' }}>Warning</option>
                    <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Diblokir</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Users List -->
    <div class="space-y-6 relative z-10">
        @forelse($users as $user)
            @php
                $roleBadgeClass = match($user['type']) {
                    'consumer' => 'bg-blue-50 text-blue-750 border border-blue-200/50',
                    'mitra' => 'bg-green-50 text-green-755 border border-green-200/50',
                    'lembaga' => 'bg-purple-50 text-purple-750 border border-purple-200/50',
                    default => 'bg-gray-50 text-gray-700 border border-gray-200/50'
                };
                $statusBadgeClass = match($user['status']) {
                    'active' => 'bg-emerald-50 text-emerald-700 border-emerald-250/30',
                    'warned' => 'bg-orange-50 text-orange-600 border-orange-200/30',
                    'blocked' => 'bg-red-50 text-red-700 border-red-250/30',
                    default => 'bg-gray-50 text-gray-705 border-gray-255/30'
                };
            @endphp
            <div class="glass-card rounded-[2.5rem] overflow-hidden transition-all duration-500 hover:shadow-2xl hover:shadow-emerald-950/5 group reveal">
                <div class="p-8 md:p-10">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                        <!-- User Info -->
                        <div class="flex items-start gap-5">
                            <!-- Initials Avatar -->
                            <div class="h-16 w-16 shrink-0 rounded-[1.3rem] bg-gradient-to-tr from-[#174413] to-emerald-600 text-white border-2 border-white shadow-md flex items-center justify-center font-serif font-black text-2xl transition-transform duration-500 group-hover:scale-105">
                                {{ substr($user['name'], 0, 1) }}
                            </div>
                            
                            <div>
                                <div class="flex flex-wrap items-center gap-2.5 mb-3">
                                    <h3 class="font-serif text-2xl font-bold text-luxury-forest tracking-tight">{{ $user['name'] }}</h3>
                                    
                                    <!-- Role Badge -->
                                    <span class="inline-flex items-center px-3.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $roleBadgeClass }}">
                                        {{ $user['type'] === 'consumer' ? 'Konsumen' : ($user['type'] === 'mitra' ? 'Mitra' : 'Lembaga') }}
                                    </span>
                                    
                                    <!-- Verified Badge -->
                                    @if($user['type'] !== 'consumer' && $user['verified'])
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200/50 shadow-sm gap-1">
                                            <i data-lucide="check-circle" class="w-3 h-3 stroke-[2.5]"></i> Verified
                                        </span>
                                    @endif
                                    
                                    <!-- Status Badge -->
                                    <span class="inline-flex items-center px-3.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $statusBadgeClass }}">
                                        @if($user['status'] === 'warned')
                                            <span class="flex items-center gap-1.5"><i data-lucide="alert-triangle" class="w-3 h-3"></i> {{ $user['warnings'] }} Peringatan</span>
                                        @elseif($user['status'] === 'blocked')
                                            <span class="flex items-center gap-1.5"><i data-lucide="ban" class="w-3 h-3"></i> Diblokir</span>
                                        @else
                                            Aktif
                                        @endif
                                    </span>
                                </div>
                                
                                <div class="flex flex-wrap items-center gap-3 text-xs font-bold text-luxury-slate">
                                    <div class="flex items-center gap-1.5 bg-white/40 border border-luxury-alabas/50 px-4 py-1.5 rounded-full"><i data-lucide="mail" class="w-4 h-4 text-luxury-gold shrink-0"></i> {{ $user['email'] }}</div>
                                    <div class="flex items-center gap-1.5 bg-white/40 border border-luxury-alabas/50 px-4 py-1.5 rounded-full"><i data-lucide="phone" class="w-4 h-4 text-luxury-gold shrink-0"></i> {{ $user['phone'] ?? '-' }}</div>
                                    <div class="flex items-center gap-1.5 bg-white/40 border border-luxury-alabas/50 px-4 py-1.5 rounded-full"><i data-lucide="calendar" class="w-4 h-4 text-luxury-gold shrink-0"></i> Bergabung: {{ $user['joined_at'] }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Stats (Transactions) -->
                        <div class="text-center shrink-0 bg-white/45 border border-luxury-alabas/70 px-6 py-4 rounded-[1.5rem] min-w-[130px] self-start lg:self-auto shadow-sm">
                            <div class="text-3xl font-serif font-black text-green-600 leading-none">{{ $user['transactions'] }}</div>
                            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-2.5">Transaksi</div>
                        </div>
                    </div>

                    <!-- Warning Alert Banner -->
                    @if($user['status'] === 'warned' || $user['warnings'] > 0)
                        <div class="mt-6 bg-orange-50/50 border border-orange-100 rounded-2xl p-5 flex gap-4 animate-in fade-in duration-300">
                            <div class="w-10 h-10 bg-orange-100/50 text-orange-600 rounded-xl flex items-center justify-center shrink-0 shadow-sm animate-pulse">
                                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-orange-900">Peringatan Terakhir: {{ $user['last_warning'] ?? 'Belum ada data' }}</div>
                                <p class="text-xs text-orange-700 font-medium mt-1 leading-relaxed">{{ $user['warning_reason'] ?? 'Pelanggaran ketentuan sistem.' }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Blocked Alert Banner -->
                    @if($user['status'] === 'blocked')
                        <div class="mt-6 bg-red-50/50 border border-red-100 rounded-2xl p-5 flex gap-4 animate-in fade-in duration-300">
                            <div class="w-10 h-10 bg-red-100/50 text-red-600 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
                                <i data-lucide="ban" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-red-900">Diblokir: {{ $user['blocked_at'] ?? 'Belum ada data' }}</div>
                                <p class="text-xs text-red-700 font-medium mt-1 leading-relaxed"><span class="font-extrabold text-red-800">Alasan:</span> {{ $user['block_reason'] ?? 'Pelanggaran berat terhadap ketentuan ShareMeal.' }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Action Footer Links -->
                    <div class="mt-6 pt-6 border-t border-luxury-alabas/70 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex flex-wrap gap-3">
                            @if($user['status'] !== 'blocked')
                                <button type="button" 
                                        @click="openConfirm('warn', '{{ route('admin.users.warn', $user['id']) }}', 'Beri Peringatan', 'Apakah Anda yakin ingin memberikan peringatan resmi kepada pengguna ini?')"
                                        class="flex items-center gap-2 px-5 py-3 border border-orange-200 text-orange-600 bg-orange-50 rounded-xl hover:bg-orange-100 hover:text-orange-700 transition duration-300 text-xs font-black uppercase tracking-wider active:scale-95 cursor-pointer">
                                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                    Beri Peringatan
                                </button>
                                
                                <button type="button" 
                                        @click="openConfirm('block', '{{ url('admin/users/' . $user['id'] . '/block') }}', 'Blokir Akun', 'Apakah Anda yakin ingin memblokir akses pengguna ini secara permanen?')"
                                        class="flex items-center gap-2 px-5 py-3 border border-red-205 text-red-600 hover:bg-red-50 rounded-xl transition duration-300 text-xs font-black uppercase tracking-wider active:scale-95 cursor-pointer">
                                    <i data-lucide="ban" class="w-4 h-4"></i>
                                    Blokir Akun
                                </button>
                            @else
                                <form action="{{ route('admin.users.unblock', $user['id']) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-emerald-600 to-green-600 text-white rounded-xl hover:shadow-lg hover:shadow-emerald-950/10 hover:from-emerald-700 hover:to-green-700 transition duration-300 text-xs font-black uppercase tracking-wider active:scale-95 cursor-pointer shadow-md">
                                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                                        Buka Blokir
                                    </button>
                                </form>
                            @endif
                        </div>
                        
                        <button type="button"
                                @click="openDetail({
                                    name: '{{ addslashes($user['name']) }}',
                                    email: '{{ addslashes($user['email']) }}',
                                    phone: '{{ addslashes($user['phone'] ?? '-') }}',
                                    joined: '{{ addslashes($user['joined_at']) }}',
                                    type: '{{ $user['type'] }}',
                                    status: '{{ $user['status'] }}',
                                    warnings: {{ $user['warnings'] ?? 0 }},
                                    verified: {{ $user['verified'] ? 'true' : 'false' }},
                                    initial: '{{ strtoupper(substr($user['name'], 0, 1)) }}',
                                    transactions: {{ $user['transactions'] ?? 0 }}
                                })"
                                class="flex items-center gap-2 px-5 py-3 border border-luxury-alabas/85 text-luxury-forest hover:bg-emerald-50 hover:border-emerald-200 rounded-xl transition duration-300 text-xs font-black uppercase tracking-wider active:scale-95 cursor-pointer">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                            Lihat Detail
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <!-- Premium Glassmorphism Empty State -->
            <div class="bg-white/40 rounded-[3rem] border border-dashed border-gray-250 p-20 text-center shadow-sm relative overflow-hidden reveal">
                <div class="absolute inset-0 bg-gradient-to-b from-white/30 to-transparent pointer-events-none"></div>
                <div class="h-20 w-20 bg-gradient-to-tr from-[#174413]/5 to-emerald-55 text-luxury-forest rounded-2xl flex items-center justify-center mx-auto mb-6 border border-luxury-alabas/80 shadow-inner">
                    <i data-lucide="users" class="w-10 h-10 text-luxury-forest"></i>
                </div>
                <h3 class="text-3xl font-serif font-black text-luxury-forest mb-2">Tidak Ada Pengguna</h3>
                <p class="text-luxury-slate font-medium max-w-sm mx-auto">Tidak ada pengguna yang terdaftar dengan kriteria pencarian Anda.</p>
            </div>
        @endforelse
    </div>

    <!-- ===== MODAL DETAIL USER ===== -->
    <div x-show="isDetailOpen"
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-[#0d1f0d]/60 backdrop-blur-md" @click="isDetailOpen = false"></div>

        <!-- Panel -->
        <div x-show="isDetailOpen"
             x-transition:enter="ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 translate-y-10 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-10 scale-95"
             class="relative w-full max-w-lg bg-white rounded-[3rem] shadow-2xl z-10 overflow-hidden border border-white/70"
             @click.stop>

            <!-- Header gradient -->
            <div class="h-36 bg-gradient-to-br from-[#174413] via-emerald-700 to-green-500 relative overflow-hidden">
                <div class="absolute inset-0 opacity-20"
                     style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px), radial-gradient(circle at 80% 20%, white 1px, transparent 1px); background-size: 40px 40px;"></div>
                <!-- Close -->
                <button type="button" @click="isDetailOpen = false"
                        class="absolute top-5 right-5 w-9 h-9 flex items-center justify-center rounded-full bg-white/20 hover:bg-white/30 text-white transition cursor-pointer border border-white/30">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
                <!-- Avatar -->
                <div class="absolute -bottom-9 left-1/2 -translate-x-1/2">
                    <div class="w-20 h-20 rounded-[1.5rem] bg-white shadow-xl border-4 border-white flex items-center justify-center font-serif font-black text-3xl text-emerald-700"
                         x-text="detail.initial"></div>
                </div>
            </div>

            <!-- Body -->
            <div class="px-8 pt-14 pb-8 space-y-6">

                <!-- Name + badges -->
                <div class="text-center space-y-2">
                    <h3 class="text-2xl font-serif font-black text-luxury-forest" x-text="detail.name"></h3>
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <!-- Role badge -->
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border"
                              :class="{
                                'bg-blue-50 text-blue-700 border-blue-200/50': detail.type === 'consumer',
                                'bg-green-50 text-green-700 border-green-200/50': detail.type === 'mitra',
                                'bg-purple-50 text-purple-700 border-purple-200/50': detail.type === 'lembaga'
                              }"
                              x-text="detail.type === 'consumer' ? 'Konsumen' : (detail.type === 'mitra' ? 'Mitra' : 'Lembaga')"></span>
                        <!-- Status badge -->
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border"
                              :class="{
                                'bg-emerald-50 text-emerald-700 border-emerald-200/50': detail.status === 'active',
                                'bg-orange-50 text-orange-600 border-orange-200/50': detail.status === 'warned',
                                'bg-red-50 text-red-700 border-red-200/50': detail.status === 'blocked'
                              }"
                              x-text="detail.status === 'active' ? 'Aktif' : (detail.status === 'warned' ? 'Diperingatkan' : 'Diblokir')"></span>
                        <!-- Verified -->
                        <span x-show="detail.verified && detail.type !== 'consumer'"
                              class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200/50">
                            <i data-lucide="check-circle" class="w-3 h-3 stroke-[2.5]"></i> Verified
                        </span>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-3">
                    <!-- Email -->
                    <div class="col-span-2 bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 flex items-center gap-3">
                        <div class="w-9 h-9 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="mail" class="w-4 h-4 text-emerald-600"></i>
                        </div>
                        <div>
                            <div class="text-[9px] font-black text-luxury-slate uppercase tracking-wider">Email</div>
                            <div class="text-sm font-bold text-luxury-forest truncate" x-text="detail.email"></div>
                        </div>
                    </div>
                    <!-- Phone -->
                    <div class="bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 flex items-center gap-3">
                        <div class="w-9 h-9 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="phone" class="w-4 h-4 text-blue-500"></i>
                        </div>
                        <div>
                            <div class="text-[9px] font-black text-luxury-slate uppercase tracking-wider">Telepon</div>
                            <div class="text-sm font-bold text-luxury-forest" x-text="detail.phone"></div>
                        </div>
                    </div>
                    <!-- Join date -->
                    <div class="bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 flex items-center gap-3">
                        <div class="w-9 h-9 bg-purple-50 border border-purple-100 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="calendar" class="w-4 h-4 text-purple-500"></i>
                        </div>
                        <div>
                            <div class="text-[9px] font-black text-luxury-slate uppercase tracking-wider">Bergabung</div>
                            <div class="text-sm font-bold text-luxury-forest" x-text="detail.joined"></div>
                        </div>
                    </div>
                    <!-- Warnings -->
                    <div class="bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 flex items-center gap-3">
                        <div class="w-9 h-9 border rounded-xl flex items-center justify-center shrink-0"
                             :class="detail.warnings > 0 ? 'bg-orange-50 border-orange-100' : 'bg-gray-100 border-gray-200'">
                            <i data-lucide="alert-triangle" class="w-4 h-4"
                               :class="detail.warnings > 0 ? 'text-orange-500' : 'text-gray-400'"></i>
                        </div>
                        <div>
                            <div class="text-[9px] font-black text-luxury-slate uppercase tracking-wider">Peringatan</div>
                            <div class="text-sm font-bold"
                                 :class="detail.warnings > 0 ? 'text-orange-600' : 'text-gray-400'"
                                 x-text="detail.warnings + ' kali'"></div>
                        </div>
                    </div>
                    <!-- Transactions -->
                    <div class="bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 flex items-center gap-3">
                        <div class="w-9 h-9 bg-green-50 border border-green-100 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="receipt" class="w-4 h-4 text-green-600"></i>
                        </div>
                        <div>
                            <div class="text-[9px] font-black text-luxury-slate uppercase tracking-wider">Transaksi</div>
                            <div class="text-sm font-bold text-green-600" x-text="detail.transactions + ' transaksi'"></div>
                        </div>
                    </div>
                </div>

                <!-- Close button -->
                <button type="button" @click="isDetailOpen = false"
                        class="w-full py-4 rounded-2xl bg-gradient-to-r from-[#174413] to-emerald-600 text-white text-xs font-black uppercase tracking-[0.2em] hover:from-emerald-800 hover:to-emerald-700 transition active:scale-95 shadow-md shadow-emerald-950/10 cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    <!-- ===== END MODAL DETAIL ===== -->

    <!-- Modal Aksi Dialog: Peringatan & Pemblokiran (Z-Index 100 exactly) -->
    <div x-show="isActionDialogOpen" 
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" 
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     x-cloak>
    
    <!-- Backdrop -->
    <div class="fixed inset-0 backdrop-blur-md" 
         :class="actionType === 'warn' ? 'bg-orange-950/60' : 'bg-red-950/60'"
         @click="isActionDialogOpen = false"></div>
    
    <!-- Modal Content -->
    <div x-show="isActionDialogOpen"
         x-transition:enter="ease-out duration-500 delay-100"
         x-transition:enter-start="opacity-0 translate-y-12 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-12 scale-95"
         class="relative bg-white rounded-[3.5rem] w-full max-w-md p-10 shadow-2xl z-10 border border-white/60" @click.stop>
        
        <!-- Close button -->
        <div class="absolute top-8 right-8">
            <button type="button" @click="isActionDialogOpen = false" 
                    class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 text-gray-405 hover:bg-gray-100 transition-all cursor-pointer border border-gray-150 shadow-sm">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- STEP 1: CONFIRM -->
        <div x-show="currentStep === 1" class="text-center space-y-6 pt-4">
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto shadow-sm border"
                 :class="actionType === 'warn' ? 'bg-orange-50 border-orange-100 text-orange-600' : 'bg-red-50 border-red-100 text-red-650'">
                <template x-if="actionType === 'warn'">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
                </template>
                <template x-if="actionType === 'block'">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="9.5" x2="14.5" y1="14.5" y2="9.5"/><line x1="14.5" x2="9.5" y1="14.5" y2="9.5"/></svg>
                </template>
            </div>

            <div class="space-y-2">
                <h3 class="text-3xl font-serif font-black text-gray-900 leading-tight" x-text="actionTitle"></h3>
                <p class="text-xs font-semibold text-luxury-slate max-w-sm mx-auto leading-relaxed" x-text="actionMessage"></p>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="button" @click="isActionDialogOpen = false" 
                        class="flex-1 py-4 px-6 text-[10px] font-black uppercase tracking-[0.2em] text-gray-555 hover:text-gray-900 transition active:scale-95 cursor-pointer">
                    Batal
                </button>
                <button type="button" @click="nextStep()" 
                        class="flex-1 py-4 px-6 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] text-white transition active:scale-95 shadow-md cursor-pointer"
                        :class="actionType === 'warn' ? 'bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 shadow-orange-100' : 'bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 shadow-red-100'">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>

        <!-- STEP 2: INPUT REASON -->
        <div x-show="currentStep === 2" class="space-y-6 pt-4" x-cloak>
            <div class="text-center space-y-2">
                <div class="w-16 h-16 rounded-2xl bg-gray-50 border border-gray-100 text-gray-600 flex items-center justify-center mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 stroke-[2.5]"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                </div>
                <h3 class="text-2xl font-serif font-black text-gray-900 leading-tight">Masukkan Alasan</h3>
                <p class="text-xs text-luxury-slate font-medium" x-text="actionType === 'warn' ? 'Alasan peringatan akan dikirimkan ke riwayat peringatan akun.' : 'Alasan pemblokiran akan dicatat di status blokir akun.'"></p>
            </div>

            <div class="space-y-2.5">
                <label class="text-[10px] font-black text-luxury-gold uppercase tracking-wider block text-left">Alasan Tindakan</label>
                <textarea x-model="reason" rows="4" required 
                          class="w-full bg-gray-55 border border-luxury-alabas/85 rounded-2xl p-5 outline-none focus:ring-2 transition font-medium text-gray-900 placeholder:text-gray-400 resize-none h-[110px]" 
                          :class="actionType === 'warn' ? 'focus:ring-orange-500/50 focus:border-orange-500' : 'focus:ring-red-500/50 focus:border-red-500'"
                          :placeholder="actionType === 'warn' ? 'Contoh: Terlalu sering membatalkan pesanan sepihak atau melanggar SOP pelayanan...' : 'Contoh: Melakukan kecurangan transaksi atau menyebarkan makanan tidak layak...'"></textarea>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="button" @click="currentStep = 1" 
                        class="flex-1 py-4 px-6 text-[10px] font-black uppercase tracking-[0.2em] text-gray-555 hover:text-gray-900 transition active:scale-95 cursor-pointer">
                    Kembali
                </button>
                <button type="button" @click="submitAction()" :disabled="!reason.trim()"
                        class="flex-1 py-4 px-6 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] text-white transition active:scale-95 shadow-md disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                        :class="actionType === 'warn' ? 'bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 shadow-orange-100' : 'bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 shadow-red-100'">
                    Kirim Tindakan
                </button>
            </div>
        </div>

        <!-- STEP 3: LOADING -->
        <div x-show="currentStep === 3" class="text-center py-8 space-y-6" x-cloak>
            <div class="w-20 h-20 relative flex items-center justify-center mx-auto">
                <div class="absolute inset-0 border-4 border-gray-150 rounded-full animate-spin"
                     :class="actionType === 'warn' ? 'border-t-orange-500' : 'border-t-red-600'"></div>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 animate-pulse"
                     :class="actionType === 'warn' ? 'text-orange-500' : 'text-red-600'">
                    <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
                </svg>
            </div>
            <div class="space-y-1">
                <h4 class="text-xl font-serif font-black text-gray-900">Memproses Tindakan</h4>
                <p class="text-[9px] text-luxury-slate font-black uppercase tracking-[0.2em] animate-pulse">Mohon tunggu sebentar...</p>
            </div>
        </div>

        <!-- STEP 4: SUCCESS -->
        <div x-show="currentStep === 4" class="text-center py-8 space-y-6" x-cloak>
            <div class="w-24 h-24 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto border border-emerald-100 shadow-md relative overflow-hidden">
                <svg class="w-12 h-12 text-emerald-600 stroke-[3.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" style="stroke: #10B981; stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 2; stroke-miterlimit: 10; animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;"/>
                    <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" style="stroke: #10B981; stroke-dasharray: 48; stroke-dashoffset: 48; stroke-width: 4; stroke-linecap: round; stroke-linejoin: round; animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.6s forwards;"/>
                </svg>
            </div>
            <div class="space-y-1">
                <h4 class="text-3xl font-serif font-black text-gray-900">Berhasil Disimpan!</h4>
                <p class="text-xs text-luxury-slate font-semibold max-w-xs mx-auto leading-relaxed">Tindakan moderasi berhasil diterapkan dan riwayat pengguna telah diperbarui.</p>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    // Initialize Lucide Icons on DOM Load
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endsection
