@extends('layouts.dashboard')

@section('content')
<style>
    @keyframes progress-bar-stripes {
        0% { background-position: 1rem 0; }
        100% { background-position: 0 0; }
    }
    .stepper-progress-bar {
        background-image: linear-gradient(
            45deg,
            rgba(255, 255, 255, 0.35) 25%,
            transparent 25%,
            transparent 50%,
            rgba(255, 255, 255, 0.35) 50%,
            rgba(255, 255, 255, 0.35) 75%,
            transparent 75%,
            transparent
        );
        background-size: 1rem 1rem;
        animation: progress-bar-stripes 0.85s linear infinite;
    }
    @keyframes route-dash {
        to {
            stroke-dashoffset: -20;
        }
    }
    @keyframes courier-travel {
        0% {
            offset-distance: 0%;
        }
        75%, 100% {
            offset-distance: 100%;
        }
    }
    .delivery-route-line {
        animation: route-dash 1.2s linear infinite;
    }
</style>
<div class="space-y-8">
    <!-- Decorative Ambient Glow -->
    <div class="absolute top-0 right-0 w-[30rem] h-[30rem] bg-emerald-100/10 rounded-full blur-[120px] pointer-events-none z-0"></div>

    <!-- Header Banner -->
    <div class="relative z-10 reveal">
        <h1 class="text-4xl md:text-5xl font-serif font-black text-luxury-forest leading-tight tracking-tight">Pesanan Aktif</h1>
        <p class="text-luxury-slate font-medium mt-3 text-lg leading-relaxed max-w-3xl">Pantau proses penyiapan dan status pengiriman pesanan makanan Anda secara langsung.</p>
    </div>

    <!-- Active Orders List -->
    <div class="space-y-8 relative z-10">
        @forelse($activeOrders as $t)
            @php
                $activeStep = 1;
                $statusText = 'Menunggu Konfirmasi';
                $statusDesc = 'Mitra sedang meninjau pesanan Anda.';
                
                if ($t->status === 'pending') {
                    $activeStep = 1;
                    $statusText = 'Menunggu Konfirmasi';
                    $statusDesc = 'Pesanan Anda telah dikirim dan menunggu konfirmasi dari Toko.';
                } elseif ($t->status === 'processing') {
                    $activeStep = 2;
                    $statusText = 'Sedang Disiapkan';
                    $statusDesc = 'Makanan Anda sedang disiapkan dan dikemas dengan higienis oleh Toko.';
                } elseif ($t->status === 'ready') {
                    $activeStep = 3;
                    if ($t->receiving_method === 'delivery') {
                        $statusText = 'Siap Dikirim';
                        $statusDesc = 'Pesanan Anda sudah siap dikirim dan menunggu kurir menjemput.';
                    } else {
                        $statusText = 'Siap Diambil';
                        $statusDesc = 'Makanan Anda sudah selesai dikemas dan siap diambil di toko.';
                    }
                } elseif ($t->status === 'shipping') {
                    if ($t->receiving_method === 'delivery') {
                        $activeStep = 4;
                        $statusText = 'Dalam Perjalanan';
                        $statusDesc = 'Pesanan sedang dalam pengiriman ke alamat tujuan Anda.';
                    } else {
                        $activeStep = 3;
                        $statusText = 'Siap Diambil';
                        $statusDesc = 'Makanan Anda sudah selesai dikemas dan siap diambil di toko.';
                    }
                } elseif ($t->status === 'completed') {
                    $activeStep = $t->receiving_method === 'delivery' ? 5 : 4;
                    $statusText = 'Selesai';
                    $statusDesc = 'Pesanan Anda telah berhasil diterima.';
                }

                if ($t->receiving_method === 'delivery') {
                    $steps = [
                        1 => 'Menunggu Konfirmasi',
                        2 => 'Diproses',
                        3 => 'Siap',
                        4 => 'Dalam Perjalanan',
                        5 => 'Sampai'
                    ];
                } else {
                    $steps = [
                        1 => 'Menunggu Konfirmasi',
                        2 => 'Diproses',
                        3 => 'Siap Diambil',
                        4 => 'Selesai'
                    ];
                }
                $totalSteps = count($steps);
                $progressPercent = (($activeStep - 1) / ($totalSteps - 1)) * 100;
            @endphp
            
            <div class="glass-card rounded-[2.5rem] overflow-hidden shadow-lg border border-white/40 reveal">
                <div class="p-8 sm:p-10 space-y-6">
                    
                    <!-- Card Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-luxury-alabas/50 pb-4 gap-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <h3 class="text-2xl font-serif font-bold text-luxury-forest">{{ $t->store }}</h3>
                            
                            <!-- Status Badge -->
                            <span class="px-3.5 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border flex items-center gap-1.5 transition-all duration-300
                                {{ $t->status === 'pending' ? 'bg-orange-50 text-orange-700 border-orange-200/70 shadow-[0_2px_10px_rgba(249,115,22,0.1)]' :
                                   ($t->status === 'processing' ? 'bg-amber-50 text-amber-700 border-amber-200 shadow-[0_2px_10px_rgba(245,158,11,0.1)]' :
                                    ($t->status === 'ready' ? 'bg-emerald-50 text-emerald-700 border-emerald-250 shadow-[0_2px_10px_rgba(16,185,129,0.1)]' :
                                     'bg-blue-50 text-blue-700 border-blue-200 shadow-[0_2px_10px_rgba(59,130,246,0.1)]')) }}">
                                <span class="relative flex h-1.5 w-1.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75
                                        {{ $t->status === 'pending' ? 'bg-orange-400' :
                                           ($t->status === 'processing' ? 'bg-amber-400' :
                                            ($t->status === 'ready' ? 'bg-emerald-400' : 'bg-blue-400')) }}"></span>
                                    <span class="relative inline-flex rounded-full h-1.5 w-1.5
                                        {{ $t->status === 'pending' ? 'bg-orange-500' :
                                           ($t->status === 'processing' ? 'bg-amber-500' :
                                            ($t->status === 'ready' ? 'bg-emerald-500' : 'bg-blue-500')) }}"></span>
                                </span>
                                {{ $statusText }}
                            </span>
                            @if($t->is_delayed)
                                <span class="bg-amber-500 text-white px-3.5 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border border-amber-600 flex items-center gap-1.5 shadow-[0_2px_10px_rgba(245,158,11,0.2)] select-none">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 text-white"></i>
                                    Delayed
                                </span>
                            @endif

                            <!-- Delivery/Pickup Badge -->
                            @if($t->receiving_method === 'delivery')
                                <span class="bg-indigo-50/70 text-indigo-700 px-3.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border border-indigo-200/50 flex items-center gap-1">
                                    <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                    Delivery
                                </span>
                            @else
                                <span class="bg-emerald-50/70 text-emerald-800 px-3.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border border-emerald-200/50 flex items-center gap-1">
                                    <i data-lucide="store" class="w-3.5 h-3.5"></i>
                                    Pickup
                                </span>
                            @endif
                        </div>
                        <div class="text-left sm:text-right">
                            <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-0.5">ID Transaksi</span>
                            <span class="font-mono text-xs font-bold text-luxury-forest tracking-tighter">#{{ $t->orderId }}</span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                        
                        <!-- Left Details (3 Cols) -->
                        <div class="lg:col-span-3 space-y-5 text-left">
                            <div>
                                <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-2">Rincian Pembelian</span>
                                <div class="space-y-2">
                                    @foreach($t->items as $item)
                                        <div class="flex justify-between items-center text-sm">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-luxury-forest">{{ $item->product ? $item->product->name : $item->name }}</span>
                                                <span class="text-[10px] text-luxury-slate font-black uppercase tracking-wider bg-luxury-alabas/40 px-2 py-0.5 rounded-md">x{{ $item->quantity }}</span>
                                            </div>
                                            <span class="font-bold text-luxury-forest">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @php
                                $earliestExpiry = $t->items
                                    ->filter(fn($i) => $i->product && $i->product->expires_at)
                                    ->map(fn($i) => $i->product->expires_at)
                                    ->sort()
                                    ->first();
                            @endphp
                            @if($earliestExpiry)
                                <div class="flex items-center gap-2 text-orange-500">
                                    <i data-lucide="clock" class="w-4 h-4 shrink-0"></i>
                                    <span class="text-sm font-medium">Layak Konsumsi s/d: {{ \Carbon\Carbon::parse($earliestExpiry)->format('d M Y, H:i') }}</span>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-4 pt-3 border-t border-luxury-alabas/40">
                                <div>
                                    <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-1">Total Pembayaran</span>
                                    <span class="text-lg font-serif font-black text-luxury-forest">Rp {{ number_format($t->total, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-1">Metode Bayar</span>
                                    <span class="text-xs font-bold text-luxury-forest uppercase tracking-wider">{{ $t->payment_method ?: 'QRIS' }}</span>
                                </div>
                            </div>

                            <div class="p-4 bg-white rounded-2xl border border-luxury-alabas/50 shadow-sm">
                                <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-1.5">{{ $t->receiving_method === 'delivery' ? 'Alamat Pengiriman' : 'Lokasi Penjemputan' }}</span>
                                <div class="text-xs font-bold text-luxury-forest mb-0.5">{{ $t->receiving_method === 'delivery' ? Auth::user()->name : $t->store }}</div>
                                <div class="text-[11px] text-luxury-slate leading-relaxed font-medium italic opacity-85">{{ $t->receiving_method === 'delivery' ? (Auth::user()->profile?->address ?? Auth::user()->address ?? 'Jl. Telekomunikasi No. 1, Bandung') : $t->storeAddress }}</div>
                            </div>
                        </div>

                        <!-- Right Info & Countdown (2 Cols) -->
                        <div class="lg:col-span-2 flex flex-col justify-between text-left space-y-4">
                            <div class="space-y-2">
                                <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block">Status Terkini</span>
                                <p class="text-sm font-bold text-luxury-forest leading-snug">{{ $statusDesc }}</p>
                                
                                @if($t->is_delayed)
                                <div class="bg-amber-50 border border-amber-200/70 rounded-2xl p-4 flex items-start gap-3 mt-3 shadow-sm select-none">
                                    <div class="bg-amber-100 p-1.5 rounded-lg text-amber-700 shrink-0">
                                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <div class="text-[10px] font-black text-amber-800 uppercase tracking-widest mb-0.5">Pemberitahuan Delay</div>
                                        <div class="text-xs font-bold text-amber-700 leading-normal">
                                            @if($t->receiving_method === 'delivery')
                                                Makanan ini kemungkinan akan terlambat datang.
                                            @else
                                                Pesanan akan delay jadinya mohon ditunggu sebentar.
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            @if($t->status === 'completed')
                                <div class="space-y-4 pt-4 border-t border-luxury-alabas/40 mt-auto">
                                    <div class="flex items-start gap-3 p-3.5 bg-emerald-50/60 rounded-2xl border border-emerald-100/50 text-emerald-800">
                                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5"></i>
                                        <div class="flex-1 text-left">
                                            <p class="text-[9px] font-black uppercase tracking-widest leading-none mb-1">
                                                {{ $t->receiving_method === 'delivery' ? 'Pesanan Sampai' : 'Pesanan Diambil' }}
                                            </p>
                                            <span class="text-[11px] font-semibold leading-tight block text-left">
                                                {{ $t->receiving_method === 'delivery' 
                                                    ? 'Silakan periksa makanan Anda dan konfirmasi bahwa pesanan telah diterima dengan baik.' 
                                                    : 'Silakan konfirmasi jika Anda telah menerima pesanan Anda di lokasi toko dengan baik.' }}
                                            </span>
                                        </div>
                                    </div>
                                    <form action="{{ route('consumer.orders.confirm-complete', $t->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center justify-center gap-2 bg-luxury-forest text-white py-3.5 px-6 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] shadow-lg hover:bg-luxury-gold hover:shadow-xl transition-all duration-300">
                                            Konfirmasi Pesanan Selesai
                                        </button>
                                    </form>
                                </div>
                            @else
                                @if($t->status === 'shipping' && $t->receiving_method === 'delivery')
                                    <!-- Live Map Tracking -->
                                    <div class="space-y-3 pt-4 border-t border-luxury-alabas/40">
                                        <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block">Lokasi Kurir (Live Map)</span>
                                        <div class="relative w-full h-40 rounded-2xl border border-luxury-alabas/40 bg-slate-50 overflow-hidden shadow-inner flex items-center justify-center">
                                            <!-- Simulated Map Grid & Streets -->
                                            <div class="absolute inset-0 opacity-[0.07] pointer-events-none" style="background-image: radial-gradient(circle, #0f172a 1.5px, transparent 1.5px); background-size: 16px 16px;"></div>
                                            
                                            <!-- Custom Map SVG Graphics -->
                                            <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
                                                <!-- Streets/Roads -->
                                                <path d="M -20 70 Q 80 15, 180 80 T 380 20" fill="none" stroke="#e2e8f0" stroke-width="12" stroke-linecap="round" />
                                                <path d="M 50 -20 V 180" fill="none" stroke="#e2e8f0" stroke-width="10" stroke-linecap="round" />
                                                <path d="M 260 -20 V 180" fill="none" stroke="#e2e8f0" stroke-width="10" stroke-linecap="round" />
                                                <path d="M -20 130 H 380" fill="none" stroke="#e2e8f0" stroke-width="8" stroke-linecap="round" />

                                                <!-- Route Line (Emerald) -->
                                                <path id="delivery-route" d="M 50 35 Q 80 15, 180 80 T 260 130" fill="none" stroke="#10b981" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="8 6" class="delivery-route-line" />
                                                
                                                <!-- Store Pin -->
                                                <circle cx="50" cy="35" r="7" fill="#10b981" />
                                                <circle cx="50" cy="35" r="7" fill="#10b981" fill-opacity="0.4">
                                                    <animate attributeName="r" values="7;18" dur="1.5s" repeatCount="indefinite" />
                                                    <animate attributeName="fill-opacity" values="0.4;0" dur="1.5s" repeatCount="indefinite" />
                                                </circle>
                                                
                                                <!-- User Home Pin -->
                                                <circle cx="260" cy="130" r="7" fill="#ef4444" />
                                                <circle cx="260" cy="130" r="7" fill="#ef4444" fill-opacity="0.4">
                                                    <animate attributeName="r" values="7;18" dur="1.5s" repeatCount="indefinite" />
                                                    <animate attributeName="fill-opacity" values="0.4;0" dur="1.5s" repeatCount="indefinite" />
                                                </circle>
                                            </svg>

                                            <!-- Moving Courier Icon -->
                                            <div class="absolute w-8 h-8 rounded-full bg-luxury-forest text-white border border-white shadow-md flex items-center justify-center animate-[courier-travel_12s_linear_infinite]" 
                                                 style="left: 0; top: 0; offset-path: path('M 50 35 Q 80 15, 180 80 T 260 130'); offset-rotate: auto;">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><rect x="1" y="3" width="15" height="13" rx="2" ry="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                                            </div>
                                            
                                            <!-- Map Labels -->
                                            <div class="absolute left-2.5 top-2.5 bg-white/95 backdrop-blur-sm border border-slate-100 px-2 py-0.5 rounded text-[8px] font-black text-emerald-800 shadow-sm flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Toko
                                            </div>
                                            <div class="absolute right-2.5 bottom-2.5 bg-white/95 backdrop-blur-sm border border-slate-100 px-2 py-0.5 rounded text-[8px] font-black text-red-800 shadow-sm flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rumah Anda
                                            </div>
                                        </div>

                                        <!-- Driver Info Card -->
                                        <div class="flex items-center justify-between p-3 bg-white rounded-2xl border border-luxury-alabas/50 shadow-sm">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-lg bg-luxury-forest/10 border border-luxury-forest/20 flex items-center justify-center text-luxury-forest font-bold text-xs">
                                                    RH
                                                </div>
                                                <div>
                                                     <span class="text-[7px] font-black text-luxury-gold uppercase tracking-[0.2em] block leading-none mb-0.5">Kurir Pengirim</span>
                                                     <div class="text-[11px] font-bold text-luxury-forest leading-none">Rian Hidayat</div>
                                                     <div class="text-[9px] font-semibold text-luxury-slate mt-0.5">Honda Beat • D 3192 ACJ</div>
                                                </div>
                                            </div>
                                            <div class="flex gap-1.5">
                                                <a href="tel:08123456789" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-250/30 rounded-lg text-emerald-700 transition" title="Telepon Kurir">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                                </a>
                                                <a href="https://wa.me/628123456789" target="_blank" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-250/30 rounded-lg text-emerald-700 transition" title="Kirim WhatsApp">
                                                    <svg xmlns="http://www.w3.org/250/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Pickup Code / Delivery Schedule -->
                                <div class="space-y-3 pt-4 border-t border-luxury-alabas/40">
                                    @if($t->receiving_method === 'pickup')
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-0.5">Jadwal Pengambilan</span>
                                                <span class="text-xs font-bold text-luxury-forest tracking-wide">{{ $t->pickupTime }}</span>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-0.5">Kode Klaim</span>
                                                <span class="font-mono text-lg font-black text-luxury-forest tracking-tighter bg-luxury-gold/10 px-2.5 py-0.5 rounded border border-luxury-gold/25 inline-block">{{ $t->pickupCode }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <div>
                                            <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-0.5">Perkiraan Tiba</span>
                                            <span class="text-xs font-black text-luxury-forest uppercase tracking-wider">{{ $t->delivery_time_slot }}</span>
                                        </div>
                                    @endif
                                </div>                                <!-- Countdown timer for Pickup (if pending/ready) -->
                                @if($t->receiving_method === 'pickup')
                                    @if($t->status === 'pending')
                                        <!-- Menunggu konfirmasi: Batas waktu belum berjalan -->
                                        <div class="flex items-center gap-3 p-3.5 bg-amber-50/65 rounded-2xl border border-amber-100/50 text-amber-800 mt-auto">
                                            <i data-lucide="clock" class="w-4 h-4 animate-pulse text-amber-600"></i>
                                            <div class="flex-1 text-left">
                                                <p class="text-[9px] font-black uppercase tracking-widest leading-none mb-1">Batas Waktu Pengambilan</p>
                                                <span class="text-xs font-bold">Ambil sebelum: <span class="bg-white px-2 py-0.5 rounded-md border border-amber-150 ml-1 text-[11px] font-extrabold text-amber-700">1 jam setelah pesanan diproses</span></span>
                                            </div>
                                        </div>
                                    @else
                                        <!-- Sudah diproses/ready: Batas waktu berjalan -->
                                        <div x-data="{
                                            endTime: new Date('{{ $t->expires_at->toIso8601String() }}').getTime(),
                                            timeRemaining: '',
                                            isExpired: false,
                                            init() {
                                                this.updateTime();
                                                setInterval(() => this.updateTime(), 1000);
                                            },
                                            updateTime() {
                                                const now = new Date().getTime();
                                                const distance = this.endTime - now;

                                                if (distance < 0) {
                                                    this.isExpired = true;
                                                    return;
                                                }

                                                const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                                const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                                const s = Math.floor((distance % (1000 * 60)) / 1000);

                                                this.timeRemaining = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                                            }
                                        }" class="flex items-center gap-3 p-3.5 bg-red-50/60 rounded-2xl border border-red-100/50 text-red-650 mt-auto">
                                            <i data-lucide="clock" class="w-4 h-4 animate-pulse text-red-500"></i>
                                            <div class="flex-1">
                                                <p class="text-[9px] font-black uppercase tracking-widest leading-none mb-1">Batas Waktu Pengambilan</p>
                                                <span x-show="!isExpired" class="text-xs font-bold">Ambil sebelum: <span x-text="timeRemaining" class="font-mono bg-white px-2 py-0.5 rounded-md border border-red-100 ml-1.5 text-xs text-red-600"></span></span>
                                                <span x-show="isExpired" class="text-xs font-black uppercase tracking-widest" x-cloak>Waktu Habis</span>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            @endif

                        </div>

                    </div>

                    <!-- Roadmap Tracker (Card Footer) -->
                    <div class="mt-6 pt-8 border-t border-luxury-alabas/50 bg-gray-50/40 rounded-[1.5rem] p-6 border border-gray-100/50">
                        <div class="relative flex items-center justify-between">
                            <!-- Connecting Line -->
                            <div class="absolute left-6 right-6 top-[20px] h-2.5 bg-slate-100 -z-10 rounded-full overflow-hidden shadow-inner border border-slate-200/30">
                                <!-- Completed Progress (Solid Emerald) -->
                                <div class="absolute top-0 bottom-0 left-0 bg-gradient-to-r from-emerald-500 to-teal-650 rounded-full transition-all duration-700 shadow-[0_0_8px_rgba(16,185,129,0.4)]" 
                                     style="width: {{ $progressPercent }}%"></div>
                                
                                <!-- Active Segment Progress (Animated Stripes) -->
                                @if($activeStep < $totalSteps)
                                    @php
                                        $segmentWidth = 100 / ($totalSteps - 1);
                                        $activeLeft = ($activeStep - 1) * $segmentWidth;
                                    @endphp
                                    <div class="absolute top-0 bottom-0 bg-gradient-to-r from-emerald-400 to-teal-500 stepper-progress-bar transition-all duration-700 shadow-[inset_0_1px_2px_rgba(255,255,255,0.2)]"
                                         style="left: {{ $activeLeft }}%; width: {{ $segmentWidth }}%"></div>
                                @endif
                            </div>
                            
                            @foreach($steps as $stepNum => $stepLabel)
                                <div class="flex flex-col items-center flex-1">
                                    <div class="relative w-10 h-10">
                                        <!-- Pulsing ring behind the active step -->
                                        @if($activeStep === $stepNum)
                                            <span class="absolute -inset-2.5 rounded-full bg-emerald-500/10 animate-ping"></span>
                                            <span class="absolute inset-0 rounded-full bg-luxury-forest/15 animate-pulse"></span>
                                            <span class="absolute -inset-1.5 rounded-full border border-dashed border-luxury-forest/60 animate-spin [animation-duration:6s]"></span>
                                        @endif
                                        <div class="absolute inset-0 rounded-full flex items-center justify-center border-2 font-bold text-xs transition-all duration-300
                                                    {{ $activeStep > $stepNum ? 'bg-luxury-forest text-white border-luxury-forest shadow-md' : 
                                                       ($activeStep === $stepNum ? 'bg-luxury-gold text-white border-luxury-gold shadow-md' : 'bg-white text-gray-400 border-gray-200') }}">
                                            @if($activeStep > $stepNum)
                                                <i data-lucide="check" class="w-4 h-4"></i>
                                            @elseif($activeStep === $stepNum)
                                                <i data-lucide="loader" class="w-4 h-4 animate-spin"></i>
                                            @else
                                                {{ $stepNum }}
                                            @endif
                                        </div>
                                    </div>
                                    <span class="text-[9px] uppercase tracking-wider mt-2.5 text-center px-1 transition-all duration-300
                                        {{ $activeStep === $stepNum ? 'text-luxury-forest font-black animate-pulse' : 
                                           ($activeStep > $stepNum ? 'text-luxury-forest font-bold' : 'text-gray-400 font-medium') }}">
                                        {{ $stepLabel }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        @empty
            <!-- Empty State Card -->
            <div class="glass-card rounded-[3rem] p-16 text-center max-w-2xl mx-auto reveal">
                <div class="w-20 h-20 bg-luxury-ivory border border-luxury-alabas/55 rounded-3xl flex items-center justify-center mx-auto mb-6 text-luxury-slate/40 shadow-inner">
                    <i data-lucide="shopping-bag" class="w-10 h-10 stroke-[1.5]"></i>
                </div>
                <h3 class="font-serif text-2xl font-bold text-luxury-forest mb-2.5">Tidak Ada Pesanan Aktif</h3>
                <p class="text-sm font-medium text-luxury-slate max-w-md mx-auto leading-relaxed">
                    Saat ini Anda tidak memiliki pesanan yang sedang diproses. Silakan menjelajah menu kami untuk memesan surplus makanan lezat!
                </p>
                <div class="mt-8">
                    <a href="{{ route('consumer.search') }}" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-luxury-forest text-white border border-luxury-forest text-xs font-black uppercase tracking-widest hover:bg-transparent hover:text-luxury-forest transition-all duration-300 shadow-md">
                        <i data-lucide="search" class="w-4 h-4 stroke-[2.5]"></i>
                        Mulai Jelajah Makanan
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) lucide.createIcons();
    });
</script>
@endsection
