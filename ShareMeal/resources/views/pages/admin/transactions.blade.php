@extends('layouts.dashboard')

@section('content')
<div class="space-y-8" x-data="{
    isDetailOpen: false,
    trx: {},
    exporting: false,

    openDetail(data) {
        this.trx = data;
        this.isDetailOpen = true;
    },

    async exportCsv() {
        this.exporting = true;
        try {
            /* Simulasi delay ringan agar loading terasa nyata */
            await new Promise(resolve => setTimeout(resolve, 900));
            window.location.href = '{{ route('admin.transactions.export-csv') }}';
            /* Reset setelah jeda agar browser sempat mulai download */
            await new Promise(resolve => setTimeout(resolve, 1500));
        } finally {
            this.exporting = false;
        }
    }
}">
    <!-- Decorative Glow -->
    <div class="absolute top-0 right-0 w-[30rem] h-[30rem] bg-emerald-100/10 rounded-full blur-[120px] pointer-events-none z-0"></div>

    <!-- Title Header -->
    <div class="relative z-10 mb-10 reveal">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
            <div>
                <h1 class="text-5xl font-serif font-black text-luxury-forest leading-tight tracking-tight">Pemantauan Transaksi</h1>
                <p class="text-luxury-slate font-medium mt-3 text-lg leading-relaxed max-w-3xl">Pantau seluruh aktivitas transaksi di platform ShareMeal secara real-time.</p>
            </div>
            <button @click="exportCsv()" :disabled="exporting"
                    class="flex items-center gap-2.5 px-6 py-3.5 bg-gradient-to-r from-[#174413] to-emerald-600 text-white rounded-2xl text-xs font-black uppercase tracking-wider hover:from-emerald-800 hover:to-emerald-700 transition active:scale-95 shadow-md shadow-emerald-950/10 cursor-pointer shrink-0 disabled:opacity-70 disabled:cursor-not-allowed disabled:active:scale-100">
                <!-- Spinner saat loading -->
                <svg x-show="exporting" class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <!-- Ikon download normal -->
                <i x-show="!exporting" data-lucide="download" class="w-4 h-4"></i>
                <span x-text="exporting ? 'Mengekspor...' : 'Export CSV'"></span>
            </button>
        </div>
    </div>

    <!-- Stats Cards (4 kolom) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 relative z-10">
        <!-- Total Transaksi -->
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group reveal text-center">
            <div class="w-11 h-11 bg-blue-50 text-blue-600 border border-blue-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300 mx-auto">
                <i data-lucide="receipt" class="w-5 h-5"></i>
            </div>
            <div class="text-3xl font-serif font-black text-luxury-forest group-hover:text-blue-600 transition-colors leading-none">{{ number_format($stats['total_transaksi']) }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Total Transaksi</div>
        </div>

        <!-- Selesai -->
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group reveal text-center">
            <div class="w-11 h-11 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300 mx-auto">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div class="text-3xl font-serif font-black text-luxury-forest group-hover:text-emerald-600 transition-colors leading-none">{{ number_format($stats['total_selesai']) }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Transaksi Selesai</div>
        </div>

        <!-- Pending -->
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group reveal text-center">
            <div class="w-11 h-11 bg-orange-50 text-orange-500 border border-orange-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300 mx-auto animate-pulse">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
            <div class="text-3xl font-serif font-black text-luxury-forest group-hover:text-orange-500 transition-colors leading-none">{{ number_format($stats['total_pending']) }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Transaksi Pending</div>
        </div>

        <!-- GMV -->
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group reveal text-center">
            <div class="w-11 h-11 bg-yellow-50 text-yellow-600 border border-yellow-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300 mx-auto">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
            </div>
            <div class="text-2xl font-serif font-black text-luxury-forest group-hover:text-yellow-600 transition-colors leading-none">{{ $stats['gmv'] }}</div>
            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-3">Total GMV Platform</div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="glass-card rounded-[2rem] overflow-hidden relative z-10 reveal">
        <!-- Table Header -->
        <div class="px-8 py-6 border-b border-luxury-alabas/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-serif font-black text-luxury-forest">Riwayat Transaksi</h2>
                <p class="text-xs text-luxury-slate font-medium mt-1">Halaman <span class="font-black text-luxury-forest">{{ $page }}</span> dari <span class="font-black text-luxury-forest">{{ $totalPages }}</span></p>
            </div>
            <form action="{{ route('admin.transactions') }}" method="GET" class="relative w-full sm:w-72">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-luxury-slate/50"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari ID, konsumen, mitra..."
                       class="w-full pl-11 pr-4 py-3 border border-luxury-alabas/85 rounded-2xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 outline-none bg-white/40 font-medium text-luxury-forest placeholder:text-luxury-slate/40 text-sm transition duration-300">
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr class="bg-white/30 border-b border-luxury-alabas/50">
                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-luxury-slate">ID Transaksi</th>
                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-luxury-slate">Konsumen & Mitra</th>
                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-luxury-slate">Total Harga</th>
                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-luxury-slate">Status</th>
                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-luxury-slate">Waktu</th>
                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-luxury-slate text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-alabas/40">
                    @forelse($transactions as $trx)
                    @php
                        $trxId = 'TRX-' . str_pad($trx->id, 5, '0', STR_PAD_LEFT);
                        $customerName = $trx->customer->name ?? 'User Tidak Diketahui';
                        $mitraName = $trx->mitra->displayName ?? 'Mitra Tidak Diketahui';
                        $total = 'Rp ' . number_format($trx->total_amount, 0, ',', '.');
                        $statusLabel = match($trx->status) {
                            'completed' => 'Selesai',
                            'pending'   => 'Menunggu',
                            default     => 'Dibatalkan',
                        };
                        $tglFormatted = $trx->created_at->format('d M Y');
                        $jamFormatted = $trx->created_at->format('H:i') . ' WIB';
                    @endphp
                    <tr class="hover:bg-emerald-50/30 transition-colors duration-200 group">
                        <!-- ID -->
                        <td class="px-8 py-5">
                            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-[#174413]/5 border border-[#174413]/10 rounded-xl text-xs font-black text-luxury-forest tracking-wider">
                                <i data-lucide="hash" class="w-3 h-3 text-emerald-600"></i>
                                {{ $trxId }}
                            </span>
                        </td>

                        <!-- Konsumen & Mitra -->
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#174413] to-emerald-600 text-white flex items-center justify-center font-serif font-black text-sm shrink-0">
                                    {{ strtoupper(substr($customerName, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-luxury-forest">{{ $customerName }}</div>
                                    <div class="flex items-center gap-1 text-xs text-luxury-slate font-medium mt-0.5">
                                        <i data-lucide="store" class="w-3 h-3 text-emerald-600 shrink-0"></i>
                                        {{ $mitraName }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Total -->
                        <td class="px-8 py-5">
                            <span class="text-sm font-black text-luxury-forest">{{ $total }}</span>
                        </td>

                        <!-- Status -->
                        <td class="px-8 py-5">
                            @if($trx->status === 'completed')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Selesai
                                </span>
                            @elseif($trx->status === 'pending')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span> Menunggu
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-50 text-red-700 border border-red-200/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Dibatalkan
                                </span>
                            @endif
                        </td>

                        <!-- Waktu -->
                        <td class="px-8 py-5">
                            <div class="text-sm font-bold text-luxury-forest">{{ $tglFormatted }}</div>
                            <div class="text-xs text-luxury-slate font-medium mt-0.5">{{ $jamFormatted }}</div>
                        </td>

                        <!-- Aksi -->
                        <td class="px-8 py-5 text-right">
                            <button
                                @click="openDetail({
                                    id: '{{ $trxId }}',
                                    customer: '{{ addslashes($customerName) }}',
                                    initial: '{{ strtoupper(substr($customerName, 0, 1)) }}',
                                    mitra: '{{ addslashes($mitraName) }}',
                                    total: '{{ $total }}',
                                    status: '{{ $trx->status }}',
                                    statusLabel: '{{ $statusLabel }}',
                                    date: '{{ $tglFormatted }}',
                                    time: '{{ $jamFormatted }}'
                                })"
                                class="inline-flex items-center gap-1.5 px-4 py-2 border border-luxury-alabas/85 text-luxury-forest hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-700 rounded-xl transition duration-300 text-xs font-black uppercase tracking-wider active:scale-95 cursor-pointer">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                Detail
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gradient-to-tr from-[#174413]/5 to-emerald-50 rounded-2xl flex items-center justify-center mb-4 border border-luxury-alabas/80">
                                    <i data-lucide="inbox" class="w-8 h-8 text-luxury-forest/40"></i>
                                </div>
                                <p class="font-serif font-black text-xl text-luxury-forest mb-1">Belum ada transaksi</p>
                                <p class="text-sm text-luxury-slate font-medium">Data transaksi akan muncul di sini saat ada pesanan masuk.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($transactions->count() > 0)
        <div class="px-8 py-5 border-t border-luxury-alabas/50 flex items-center justify-between gap-4">
            <p class="text-xs text-luxury-slate font-medium">
                Menampilkan halaman <span class="font-black text-luxury-forest">{{ $page }}</span> dari <span class="font-black text-luxury-forest">{{ $totalPages }}</span> halaman
            </p>
            <div class="flex items-center gap-2">
                @if($page == 1)
                    <span class="px-4 py-2 border border-luxury-alabas/60 rounded-xl text-xs font-black text-luxury-slate/40 cursor-not-allowed bg-white/20">
                        <i data-lucide="chevron-left" class="w-3.5 h-3.5 inline"></i> Prev
                    </span>
                @else
                    <a href="?page={{ $page - 1 }}{{ $search ? '&search=' . urlencode($search) : '' }}"
                       class="px-4 py-2 border border-luxury-alabas/85 rounded-xl text-xs font-black text-luxury-forest hover:bg-emerald-50 hover:border-emerald-200 transition cursor-pointer">
                        <i data-lucide="chevron-left" class="w-3.5 h-3.5 inline"></i> Prev
                    </a>
                @endif

                @for($i = 1; $i <= $totalPages; $i++)
                <a href="?page={{ $i }}{{ $search ? '&search=' . urlencode($search) : '' }}"
                   class="w-9 h-9 flex items-center justify-center rounded-xl text-xs font-black transition cursor-pointer
                          {{ $page == $i ? 'bg-gradient-to-br from-[#174413] to-emerald-600 text-white shadow-md shadow-emerald-950/10' : 'border border-luxury-alabas/85 text-luxury-forest hover:bg-emerald-50' }}">{{ $i }}</a>
                @endfor

                @if($page == $totalPages)
                    <span class="px-4 py-2 border border-luxury-alabas/60 rounded-xl text-xs font-black text-luxury-slate/40 cursor-not-allowed bg-white/20">
                        Next <i data-lucide="chevron-right" class="w-3.5 h-3.5 inline"></i>
                    </span>
                @else
                    <a href="?page={{ $page + 1 }}{{ $search ? '&search=' . urlencode($search) : '' }}"
                       class="px-4 py-2 border border-luxury-alabas/85 rounded-xl text-xs font-black text-luxury-forest hover:bg-emerald-50 hover:border-emerald-200 transition cursor-pointer">
                        Next <i data-lucide="chevron-right" class="w-3.5 h-3.5 inline"></i>
                    </a>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- ===== MODAL DETAIL TRANSAKSI ===== -->
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
             class="relative w-full max-w-md bg-white rounded-[3rem] shadow-2xl z-10 overflow-hidden border border-white/70"
             @click.stop>

            <!-- Header Gradient -->
            <div class="h-32 bg-gradient-to-br from-[#174413] via-emerald-700 to-green-500 relative overflow-hidden">
                <div class="absolute inset-0 opacity-20"
                     style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px), radial-gradient(circle at 80% 20%, white 1px, transparent 1px); background-size: 40px 40px;"></div>
                <!-- Close -->
                <button type="button" @click="isDetailOpen = false"
                        class="absolute top-5 right-5 w-9 h-9 flex items-center justify-center rounded-full bg-white/20 hover:bg-white/30 text-white transition cursor-pointer border border-white/30">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
                <!-- ID Badge -->
                <div class="absolute bottom-4 left-8">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/20 border border-white/30 rounded-xl text-xs font-black text-white tracking-wider backdrop-blur-sm">
                        <i data-lucide="hash" class="w-3 h-3"></i>
                        <span x-text="trx.id"></span>
                    </span>
                </div>
            </div>

            <!-- Body -->
            <div class="px-8 pt-6 pb-8 space-y-5">

                <!-- Konsumen row -->
                <div class="flex items-center gap-4 p-4 bg-gray-50 border border-gray-100 rounded-2xl">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#174413] to-emerald-600 text-white flex items-center justify-center font-serif font-black text-xl shrink-0"
                         x-text="trx.initial"></div>
                    <div>
                        <div class="text-[9px] font-black text-luxury-slate uppercase tracking-wider">Konsumen</div>
                        <div class="text-base font-bold text-luxury-forest mt-0.5" x-text="trx.customer"></div>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-3">
                    <!-- Mitra -->
                    <div class="col-span-2 bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 flex items-center gap-3">
                        <div class="w-9 h-9 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="store" class="w-4 h-4 text-emerald-600"></i>
                        </div>
                        <div>
                            <div class="text-[9px] font-black text-luxury-slate uppercase tracking-wider">Mitra</div>
                            <div class="text-sm font-bold text-luxury-forest" x-text="trx.mitra"></div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 flex items-center gap-3">
                        <div class="w-9 h-9 bg-yellow-50 border border-yellow-100 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="banknote" class="w-4 h-4 text-yellow-600"></i>
                        </div>
                        <div>
                            <div class="text-[9px] font-black text-luxury-slate uppercase tracking-wider">Total</div>
                            <div class="text-sm font-black text-luxury-forest" x-text="trx.total"></div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 flex items-center gap-3">
                        <div class="w-9 h-9 border rounded-xl flex items-center justify-center shrink-0"
                             :class="{
                                'bg-emerald-50 border-emerald-100': trx.status === 'completed',
                                'bg-orange-50 border-orange-100': trx.status === 'pending',
                                'bg-red-50 border-red-100': trx.status === 'cancelled'
                             }">
                            <i data-lucide="activity" class="w-4 h-4"
                               :class="{
                                'text-emerald-600': trx.status === 'completed',
                                'text-orange-500': trx.status === 'pending',
                                'text-red-500': trx.status === 'cancelled'
                               }"></i>
                        </div>
                        <div>
                            <div class="text-[9px] font-black text-luxury-slate uppercase tracking-wider">Status</div>
                            <div class="text-sm font-bold"
                                 :class="{
                                    'text-emerald-600': trx.status === 'completed',
                                    'text-orange-500': trx.status === 'pending',
                                    'text-red-500': trx.status === 'cancelled'
                                 }"
                                 x-text="trx.statusLabel"></div>
                        </div>
                    </div>

                    <!-- Tanggal -->
                    <div class="bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 flex items-center gap-3">
                        <div class="w-9 h-9 bg-purple-50 border border-purple-100 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="calendar" class="w-4 h-4 text-purple-500"></i>
                        </div>
                        <div>
                            <div class="text-[9px] font-black text-luxury-slate uppercase tracking-wider">Tanggal</div>
                            <div class="text-sm font-bold text-luxury-forest" x-text="trx.date"></div>
                        </div>
                    </div>

                    <!-- Jam -->
                    <div class="bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 flex items-center gap-3">
                        <div class="w-9 h-9 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="clock" class="w-4 h-4 text-blue-500"></i>
                        </div>
                        <div>
                            <div class="text-[9px] font-black text-luxury-slate uppercase tracking-wider">Jam</div>
                            <div class="text-sm font-bold text-luxury-forest" x-text="trx.time"></div>
                        </div>
                    </div>
                </div>

                <!-- Close Button -->
                <button type="button" @click="isDetailOpen = false"
                        class="w-full py-4 rounded-2xl bg-gradient-to-r from-[#174413] to-emerald-600 text-white text-xs font-black uppercase tracking-[0.2em] hover:from-emerald-800 hover:to-emerald-700 transition active:scale-95 shadow-md shadow-emerald-950/10 cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    <!-- ===== END MODAL DETAIL TRANSAKSI ===== -->

</div>
@endsection
