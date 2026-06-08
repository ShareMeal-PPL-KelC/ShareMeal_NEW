@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="checkoutPage">
    <!-- Loading Overlay -->
    <div x-show="isProcessing" 
         class="fixed inset-0 z-[100] flex items-center justify-center bg-luxury-forest/30 backdrop-blur-md px-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-cloak>
        <div class="bg-white/90 backdrop-blur-xl border border-white/60 rounded-[2.5rem] p-10 max-w-md w-full shadow-2xl text-center space-y-8 relative overflow-hidden"
             :class="'bg-gradient-to-br ' + methodConfig.gradient">
            
            <!-- Animated Background Glow -->
            <div class="absolute -top-20 -left-20 w-40 h-40 rounded-full blur-3xl opacity-35 animate-pulse" :class="methodConfig.bgClass"></div>
            <div class="absolute -bottom-20 -right-20 w-40 h-40 rounded-full blur-3xl opacity-35 animate-pulse" :class="methodConfig.bgClass"></div>

            <!-- Payment Method Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest shadow-sm mx-auto"
                 :class="methodConfig.badgeBg">
                <span class="w-2 h-2 rounded-full animate-ping" :class="methodConfig.badgeDot"></span>
                Proses Pembayaran <span x-text="methodConfig.name"></span>
            </div>

            <!-- Custom Spinner & Center Icon -->
            <div class="relative w-28 h-28 mx-auto">
                <!-- Base Circle -->
                <div class="absolute inset-0 border-4 border-gray-200/50 rounded-full"></div>
                <!-- Spin border -->
                <div class="absolute inset-0 border-4 rounded-full border-t-transparent animate-spin"
                     :class="methodConfig.spinnerClass"></div>
                <!-- Center Icon -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <template x-if="paymentMethod === 'qris'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-emerald-600 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect width="6" height="6" x="3" y="3" rx="1" />
                            <rect width="6" height="6" x="15" y="3" rx="1" />
                            <rect width="6" height="6" x="3" y="15" rx="1" />
                            <path d="M16 16h2v2h-2zm2 2h2v2h-2zm-2 2h2v2h-2z" />
                            <path stroke-linecap="round" d="M15 15h1m1 0h2m-4 5v-1m2 1h1" />
                        </svg>
                    </template>
                    <template x-if="paymentMethod === 'gopay'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-cyan-600 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="16" rx="2" />
                            <circle cx="8" cy="12" r="2" />
                            <path stroke-linecap="round" d="M16 12h3M16 8h2" />
                        </svg>
                    </template>
                    <template x-if="paymentMethod === 'ovo'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-purple-600 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                        </svg>
                    </template>
                    <template x-if="paymentMethod === 'dana'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-blue-600 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
                        </svg>
                    </template>
                </div>
            </div>

            <!-- Messages -->
            <div class="space-y-3">
                <h3 class="text-xl font-black text-gray-900 tracking-tight transition-all duration-300" x-text="processingMessage"></h3>
                <p class="text-xs text-luxury-slate font-medium leading-relaxed">Sistem sedang memproses transaksi secara aman. Mohon tidak menutup tab atau menekan tombol kembali.</p>
            </div>

            <!-- Progress Tracker -->
            <div class="pt-6 border-t border-gray-150 space-y-4">
                <div class="text-[10px] font-black uppercase tracking-[0.2em] text-luxury-gold text-left">Langkah Transaksi</div>
                <div class="space-y-3">
                    <template x-for="(step, index) in paymentSteps" :key="index">
                        <div class="flex items-center gap-4 text-left">
                            <!-- Icon / Status indicator -->
                            <div class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center transition-all duration-500"
                                 :class="step.status === 'done' ? 'bg-emerald-500/20 text-emerald-600' : (step.status === 'active' ? 'bg-luxury-gold/20 text-luxury-gold' : 'bg-gray-100 text-gray-400')">
                                <template x-if="step.status === 'done'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                </template>
                                <template x-if="step.status === 'active'">
                                    <span class="w-2 h-2 rounded-full bg-luxury-gold animate-ping"></span>
                                </template>
                                <template x-if="step.status === 'pending'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                </template>
                            </div>
                            <!-- Label -->
                            <div class="text-sm font-bold transition-all duration-500"
                                 :class="step.status === 'done' ? 'text-gray-500 line-through decoration-gray-300' : (step.status === 'active' ? 'text-gray-900 font-extrabold' : 'text-gray-400 font-medium')"
                                 x-text="step.label"></div>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>

    <!-- Header -->
    <div x-show="!paymentComplete" class="reveal">
        <a href="{{ route('consumer.search') }}" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-luxury-gold hover:text-luxury-forest transition-colors mb-6 group">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 transition-transform group-hover:-translate-x-1">
                <line x1="19" x2="5" y1="12" y2="12"/><polyline points="12 19 5 12 12 5"/>
            </svg>
            Kembali ke Pencarian
        </a>
        <h1 class="text-5xl font-serif font-bold text-luxury-forest leading-tight">Menyelesaikan Pemesanan</h1>
        <p class="text-luxury-slate font-medium mt-2 tracking-wide">Selesaikan pesanan Anda untuk membantu mengurangi pembuangan surplus makanan.</p>
    </div>

    <!-- Fase Checkout -->
    <div x-show="!paymentComplete" class="grid lg:grid-cols-3 gap-12">
        <!-- Left Column - Methods & Payment -->
        <div class="lg:col-span-2 space-y-10">
            <!-- Timer Card -->
            <div class="rounded-[1.5rem] bg-luxury-gold/5 border border-luxury-gold/20 px-8 py-4 flex items-center justify-between reveal">
                <div class="flex items-center gap-4">
                    <div class="w-2 h-2 bg-luxury-gold rounded-full animate-pulse"></div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-luxury-gold">Sesi pembayaran aman berakhir dalam:</span>
                </div>
                <div class="text-2xl font-serif font-bold text-luxury-gold" x-text="formatTime(countdown)"></div>
            </div>

            <!-- Selection Card -->
            <div class="glass-card rounded-[2.5rem] overflow-hidden reveal delay-100">
                <div class="p-10 border-b border-luxury-alabas/60 bg-white/30">
                    <h3 class="text-2xl font-serif font-bold text-luxury-forest">Metode Pengambilan</h3>
                </div>
                <div class="p-10 grid grid-cols-1 md:grid-cols-2 gap-6 bg-white/10">
                    <!-- Pickup -->
                    <label class="relative block cursor-pointer group">
                        <input type="radio" name="receiving_method_radio" value="pickup" x-model="receivingMethod" class="sr-only peer">
                        <div class="p-8 border-2 border-luxury-alabas/80 bg-white/40 rounded-[2rem] transition-all duration-500 peer-checked:border-luxury-forest peer-checked:bg-luxury-forest/5 group-hover:border-luxury-gold/30">
                            <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-luxury-gold mb-6 group-hover:scale-110 transition-transform shadow-sm mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                    <path d="M20 20a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1" />
                                    <path d="M3 7l2-4h14l2 4" />
                                    <path d="M9 12v6" />
                                    <path d="M15 12v6" />
                                </svg>
                            </div>
                            <div class="font-serif text-xl font-bold text-luxury-forest">Ambil Sendiri</div>
                            <div class="text-[10px] text-luxury-gold font-black uppercase tracking-widest mt-2">Gratis</div>
                        </div>
                    </label>

                    <!-- Delivery -->
                    <label class="relative block" :class="canDelivery ? 'cursor-pointer group' : 'opacity-40'">
                        <input type="radio" name="receiving_method_radio" value="delivery" x-model="receivingMethod" :disabled="!canDelivery" class="sr-only peer">
                        <div class="p-8 border-2 border-luxury-alabas/80 bg-white/40 rounded-[2rem] transition-all duration-500 peer-checked:border-luxury-forest peer-checked:bg-luxury-forest/5 group-hover:border-luxury-gold/30">
                            <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-luxury-emerald mb-6 group-hover:scale-110 transition-transform shadow-sm mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                    <circle cx="6" cy="18" r="2.5"/>
                                    <circle cx="18" cy="18" r="2.5"/>
                                    <path d="M6 18h4.5l2-4.5h5l1.5 4.5"/>
                                    <path d="M15 6.5h2L18.5 11"/>
                                    <path d="M10 13.5h4"/>
                                    <rect x="4" y="8" width="5" height="5" rx="1"/>
                                </svg>
                            </div>
                            <div class="font-serif text-xl font-bold text-luxury-forest">Kirim ke Lokasi</div>
                            <div class="text-[10px] text-luxury-emerald font-black uppercase tracking-widest mt-2" x-text="canDelivery ? 'Rp ' + deliveryFee.toLocaleString('id-ID') : 'Tidak Tersedia'"></div>
                        </div>
                    </label>
                </div>

                <!-- Time Slot Selection -->
                <div x-show="receivingMethod === 'delivery'" 
                     x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 -translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="px-10 pb-10 border-t border-luxury-alabas/60 pt-10 bg-white/10">
                    <div class="max-w-xl mx-auto text-center">
                        <span class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em] mb-6 block text-center">Pilih Waktu Pengantaran</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($booking->deliverySlots as $slot)
                                @if($slot->is_full)
                                    <!-- Penuh / Disabled Button -->
                                    <button type="button" class="p-4 bg-gray-100/50 border border-gray-200 text-gray-400 rounded-2xl text-sm font-bold opacity-60 cursor-not-allowed flex flex-col justify-center items-center w-full" disabled>
                                        <span>{{ $slot->label }}</span>
                                        <span class="text-[8px] font-black uppercase tracking-wider text-red-500 mt-1">(Penuh)</span>
                                    </button>
                                @else
                                    <!-- Clickable Option Button -->
                                    <button type="button" 
                                            @click="deliveryTimeSlot = '{{ $slot->label }}'"
                                            :class="deliveryTimeSlot === '{{ $slot->label }}' ? 'bg-[#174413] text-white border-[#174413] shadow-md scale-[1.02]' : 'bg-white/70 border-luxury-alabas text-luxury-forest hover:bg-white hover:border-luxury-gold/50'"
                                            class="p-4 border-2 rounded-2xl text-sm font-bold transition-all duration-300 active:scale-95 flex flex-col justify-center items-center">
                                        <span>{{ $slot->label }}</span>
                                        <span class="text-[8px] font-black uppercase tracking-wider mt-1" :class="deliveryTimeSlot === '{{ $slot->label }}' ? 'text-luxury-gold' : 'text-luxury-gold/70'">Tersedia</span>
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Card -->
            <div class="glass-card rounded-[2.5rem] overflow-hidden reveal delay-200">
                <div class="p-10 border-b border-luxury-alabas/60 bg-white/30">
                    <h3 class="text-2xl font-serif font-bold text-luxury-forest">Metode Pembayaran</h3>
                </div>
                <div class="p-10 space-y-4 bg-white/10">
                    @foreach($paymentMethods as $method)
                    <label class="relative block cursor-pointer group">
                        <input type="radio" name="payment_method_radio" value="{{ $method->id }}" x-model="paymentMethod" class="sr-only">
                        <div :class="paymentMethod === '{{ $method->id }}' ? 'border-luxury-forest bg-luxury-forest/5' : 'border-luxury-alabas bg-white/40'"
                             class="flex items-center justify-between p-6 border rounded-[1.5rem] transition-all duration-500 group-hover:border-luxury-gold/30">
                            <div class="flex items-center gap-6">
                                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-luxury-forest shadow-sm border border-luxury-alabas/60 mx-auto md:mx-0">
                                    @if($method->id === 'qris')
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 stroke-[1.5]">
                                        <rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16V3m0 18v-5M16 21h5M8 21v-3m0 3H3m5-13h3m2 0h1m2 0h2m-6 3h2m1 0h1m-1 2V8"/>
                                    </svg>
                                    @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 stroke-[1.5]">
                                        <rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
                                    </svg>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-serif text-lg font-bold text-luxury-forest text-center md:text-left">{{ $method->name }}</div>
                                    <div class="text-[10px] text-luxury-slate font-black uppercase tracking-widest mt-1 text-center md:text-left">{{ $method->description }}</div>
                                </div>
                            </div>
                            <!-- Custom Radio Circle with AlpineJS reactivity -->
                            <div :class="paymentMethod === '{{ $method->id }}' ? 'border-luxury-forest' : 'border-luxury-alabas'"
                                 class="w-6 h-6 border-2 rounded-full flex items-center justify-center bg-white transition-colors duration-300">
                                <div :class="paymentMethod === '{{ $method->id }}' ? 'opacity-100' : 'opacity-0'"
                                     class="w-3 h-3 bg-[#174413] rounded-full transition-opacity duration-300"></div>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Column - Summary -->
        <div class="space-y-8">
            <div class="glass-card rounded-[2.5rem] p-10 sticky top-32 reveal delay-300">
                <h3 class="text-2xl font-serif font-bold text-luxury-forest mb-8">Detail Pesanan</h3>
                
                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        @if(!empty($booking->storeLogo))
                            <img src="{{ $booking->storeLogo }}" alt="{{ $booking->storeName }}" class="w-10 h-10 rounded-xl object-cover border border-luxury-alabas/85 shadow-sm shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-xl bg-luxury-gold/10 border border-luxury-gold/20 flex items-center justify-center text-luxury-gold shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                    <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/>
                                    <path d="M7 2v20"/>
                                    <path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <div class="text-sm font-bold text-luxury-forest leading-tight">{{ $booking->storeName }}</div>
                            <div class="text-[11px] text-luxury-slate mt-1 italic leading-tight">{{ $booking->address }}</div>
                        </div>
                    </div>

                    <div class="h-px bg-luxury-alabas/60"></div>

                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-sm font-bold text-luxury-forest">{{ $booking->dealItem }}</div>
                            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-widest mt-1">Jumlah: {{ $booking->quantity }}</div>
                        </div>
                        <div class="text-sm font-bold text-luxury-forest">Rp {{ number_format($booking->price, 0, ',', '.') }}</div>
                    </div>

                    <div class="h-px bg-luxury-alabas/60"></div>

                    <div class="space-y-3">
                        <div class="flex justify-between text-xs font-medium text-luxury-slate">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($booking->price * $booking->quantity, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs font-medium text-luxury-slate" x-show="receivingMethod === 'delivery'">
                            <span>Biaya Pengiriman</span>
                            <span x-text="'Rp ' + deliveryFee.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="pt-4 mt-4 border-t border-luxury-alabas/60 flex justify-between items-center flex-row flex-nowrap gap-4">
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-luxury-gold shrink-0">Total Pembayaran</span>
                            <span class="text-2xl sm:text-3xl font-serif font-black text-luxury-forest leading-none whitespace-nowrap text-right" x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <button @click="handleConfirmPayment()" 
                            class="w-full bg-luxury-forest text-white py-6 rounded-[1.5rem] font-black uppercase tracking-[0.3em] text-[10px] hover:bg-luxury-gold transition-all duration-500 luxury-shadow mt-10 active:scale-95 flex items-center justify-center gap-3 group">
                        Konfirmasi & Bayar
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 group-hover:translate-x-1 transition-transform">
                            <line x1="5" x2="19" y1="12" y2="12"/><polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Fase Sukses (Struk Digital) -->
    <div x-show="paymentComplete" class="max-w-lg mx-auto py-0" style="display: none;" 
         x-transition:enter="transition ease-out duration-1000"
         x-transition:enter-start="opacity-0 translate-y-8 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100">
        
        <div class="glass-panel rounded-[2.5rem] shadow-2xl border border-white/40 overflow-hidden relative">
            <!-- Top Elegant Accent -->
            <div class="h-1.5 w-full bg-gradient-to-r from-luxury-forest via-luxury-gold to-luxury-emerald"></div>
            
            <div class="p-6 sm:p-8 text-center">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 luxury-shadow border border-luxury-alabas">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7 text-luxury-forest animate-in zoom-in duration-700 mx-auto">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                
                <h2 class="text-2xl font-serif font-bold text-luxury-forest mb-2">Pemesanan Berhasil</h2>
                <p class="text-luxury-slate text-xs font-medium mb-6 tracking-wide" x-text="receivingMethod === 'delivery' ? 'Makanan pesanan Anda sedang dipersiapkan dan akan segera diantarkan.' : 'Makanan Anda sudah aman dan siap diambil langsung di lokasi toko.'"></p>

                <div class="bg-white/40 rounded-2xl border border-luxury-alabas p-5 mb-6 text-left relative overflow-hidden">
                    <!-- Invoice Decoration -->
                    <div class="absolute top-0 right-0 p-4 opacity-[0.02] pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-20 h-20 text-luxury-forest -rotate-12">
                            <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 3.5 2 5.5a7 7 0 0 1-10 12.5ZM19 2v4"/>
                        </svg>
                    </div>

                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-luxury-alabas/50">
                        <div>
                            <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-0.5">ID Transaksi</span>
                            <span class="font-mono text-xs font-bold text-luxury-forest tracking-tighter" x-text="realOrderId || '{{ $booking->id }}'"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-0.5">Status</span>
                            <span class="text-[9px] font-black text-luxury-emerald uppercase tracking-widest bg-luxury-emerald/10 px-2 py-0.5 rounded">Lunas</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-1">Metode Pengambilan</span>
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-luxury-forest" :class="receivingMethod === 'delivery' ? 'hidden' : ''">
                                    <path d="M20 20a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1" />
                                    <path d="M3 7l2-4h14l2 4" />
                                    <path d="M9 12v6" />
                                    <path d="M15 12v6" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-luxury-forest" :class="receivingMethod === 'delivery' ? '' : 'hidden'">
                                    <circle cx="6" cy="18" r="2.5"/>
                                    <circle cx="18" cy="18" r="2.5"/>
                                    <path d="M6 18h4.5l2-4.5h5l1.5 4.5"/>
                                    <path d="M15 6.5h2L18.5 11"/>
                                    <path d="M10 13.5h4"/>
                                    <rect x="4" y="8" width="5" height="5" rx="1"/>
                                </svg>
                                <span class="text-xs font-bold text-luxury-forest uppercase tracking-widest" x-text="receivingMethod === 'delivery' ? 'Kirim ke Lokasi' : 'Ambil Sendiri'"></span>
                            </div>
                        </div>
                        <div>
                            <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-1">Pembayaran</span>
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-luxury-forest">
                                    <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/>
                                </svg>
                                <span class="text-xs font-bold text-luxury-forest uppercase" x-text="paymentMethod"></span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 p-3.5 bg-white rounded-xl border border-luxury-alabas/50 shadow-sm">
                        <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-1" x-text="receivingMethod === 'delivery' ? 'Alamat Pengiriman' : 'Lokasi Toko'"></span>
                        <div class="text-xs font-bold text-luxury-forest mb-0.5" x-text="receivingMethod === 'delivery' ? '{{ Auth::user()->name }}' : '{{ $booking->storeName }}'"></div>
                        <div class="text-[11px] text-luxury-slate leading-relaxed font-medium italic opacity-85" x-text="receivingMethod === 'delivery' ? '{{ Auth::user()->profile?->address ?? Auth::user()->address ?? 'Jl. Telekomunikasi No. 1, Bandung' }}' : '{{ $booking->address }}'"></div>
                    </div>

                    <div class="flex justify-between items-end">
                        <div>
                            <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-0.5" x-text="receivingMethod === 'delivery' ? 'Perkiraan Tiba' : 'Jadwal Pengambilan'"></span>
                            <div class="text-xs font-black text-luxury-forest uppercase tracking-wider" x-text="receivingMethod === 'delivery' ? deliveryTimeSlot : '{{ $booking->pickupTime }}'"></div>
                        </div>
                        <div class="text-right" x-show="receivingMethod === 'pickup'">
                            <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-0.5">Kode Klaim</span>
                            <div class="font-mono text-xl font-black text-luxury-forest tracking-tighter bg-luxury-gold/10 px-2.5 py-0.5 rounded border border-luxury-gold/25 inline-block" x-text="realPickupCode || pickupCode"></div>
                        </div>
                    </div>

                    <!-- Total Row -->
                    <div class="mt-4 pt-4 border-t-2 border-dashed border-luxury-alabas/50">
                        <div class="flex justify-between items-center flex-row flex-nowrap gap-4">
                            <span class="text-[9px] font-black text-luxury-gold uppercase tracking-[0.3em] shrink-0">Total Pembayaran</span>
                            <div class="text-xl font-serif font-black text-luxury-forest whitespace-nowrap text-right" x-text="'Rp ' + total.toLocaleString('id-ID')"></div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('consumer.orders.active') }}"
                       class="flex-[2] flex items-center justify-center gap-2 bg-luxury-forest text-white py-3.5 px-6 rounded-xl font-black uppercase tracking-[0.2em] text-[10px] shadow-lg hover:bg-luxury-gold transition-all duration-500 active:scale-95 group">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-luxury-gold transition-transform group-hover:rotate-12">
                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
                        </svg>
                        Pantau Pesanan
                    </a>
                    <button @click="window.print()" class="flex-1 flex items-center justify-center gap-2 bg-white text-luxury-slate py-3.5 px-6 rounded-xl border border-luxury-alabas font-black uppercase tracking-[0.2em] text-[10px] hover:bg-luxury-ivory transition-all duration-500">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                            <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14" rx="1" ry="1"/>
                        </svg>
                        Cetak Struk
                    </button>
                </div>
            </div>
        </div>
        <p class="text-center text-luxury-slate/40 text-[9px] font-black uppercase tracking-[0.4em] mt-6">Membantu mengurangi sampah makanan demi masa depan yang lebih baik.</p>
    </div>

    <form id="checkout-form" action="{{ route('consumer.checkout.store') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="product_id" value="{{ $booking->product_id }}">
        <input type="hidden" name="mitra_id" value="{{ $booking->mitra_id }}">
        <input type="hidden" name="quantity" value="{{ $booking->quantity }}">
        <input type="hidden" name="price" value="{{ $booking->price }}">
        <input type="hidden" name="receiving_method" :value="receivingMethod">
        <input type="hidden" name="delivery_time_slot" :value="deliveryTimeSlot">
        <input type="hidden" name="payment_method" :value="paymentMethod">
    </form>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('checkoutPage', () => ({
            paymentMethod: 'qris',
            receivingMethod: 'pickup',
            deliveryTimeSlot: '',
            deliveryFee: {{ (int)($booking->deliveryFee ?? 0) }},
            canDelivery: {{ $booking->canDelivery ? 'true' : 'false' }},
            subtotal: {{ (int)($booking->price * $booking->quantity) }},
            countdown: {{ $booking->remainingSeconds ?? 300 }},
            isProcessing: false,
            processingMessage: 'Memverifikasi pembayaran...',
            paymentComplete: false,
            realOrderId: '',
            realPickupCode: '',
            pickupCode: 'PICK-A1B2',
            paymentSteps: [],
            get methodConfig() {
                const configs = {
                    qris: {
                        name: 'QRIS',
                        colorClass: 'text-emerald-600',
                        bgClass: 'bg-emerald-500/10',
                        spinnerClass: 'border-emerald-600',
                        badgeBg: 'bg-emerald-500/20 text-emerald-700',
                        badgeDot: 'bg-emerald-500',
                        gradient: 'from-emerald-500/5 to-teal-500/10'
                    },
                    gopay: {
                        name: 'GoPay',
                        colorClass: 'text-cyan-600',
                        bgClass: 'bg-cyan-500/10',
                        spinnerClass: 'border-cyan-600',
                        badgeBg: 'bg-cyan-500/20 text-cyan-700',
                        badgeDot: 'bg-cyan-500',
                        gradient: 'from-cyan-500/5 to-sky-500/10'
                    },
                    ovo: {
                        name: 'OVO',
                        colorClass: 'text-purple-600',
                        bgClass: 'bg-purple-500/10',
                        spinnerClass: 'border-purple-600',
                        badgeBg: 'bg-purple-500/20 text-purple-700',
                        badgeDot: 'bg-purple-500',
                        gradient: 'from-purple-500/5 to-indigo-500/10'
                    },
                    dana: {
                        name: 'DANA',
                        colorClass: 'text-blue-600',
                        bgClass: 'bg-blue-500/10',
                        spinnerClass: 'border-blue-600',
                        badgeBg: 'bg-blue-500/20 text-blue-700',
                        badgeDot: 'bg-blue-500',
                        gradient: 'from-blue-500/5 to-indigo-500/10'
                    }
                };
                return configs[this.paymentMethod] || configs.qris;
            },

            get total() {
                return this.receivingMethod === 'delivery' ? this.subtotal + this.deliveryFee : this.subtotal;
            },

            init() {
                setInterval(() => {
                    if (this.countdown > 0 && !this.paymentComplete && !this.isProcessing) {
                        this.countdown--;
                        if (this.countdown <= 0) {
                            alert("Sesi reservasi Anda telah berakhir dan stok produk telah dilepaskan.");
                            window.location.href = "{{ route('consumer.cart.index') }}";
                        }
                    }
                }, 1000);
                
                if (window.lucide) {
                    lucide.createIcons();
                }
            },

            formatTime(seconds) {
                const totalSecs = Math.floor(seconds);
                const mins = Math.floor(totalSecs / 60);
                const secs = totalSecs % 60;
                return mins + ':' + (secs < 10 ? '0' : '') + secs;
            },

            async handleConfirmPayment() {
                if (this.receivingMethod === 'delivery' && !this.deliveryTimeSlot) {
                    alert('Silakan pilih waktu pengantaran terlebih dahulu.');
                    return;
                }

                // Initialize step statuses dynamically based on the payment method
                if (this.paymentMethod === 'qris') {
                    this.paymentSteps = [
                        { label: 'Inisialisasi QRIS Gateway', status: 'active' },
                        { label: 'Menghasilkan Kode QR & Pengecekan Scan', status: 'pending' },
                        { label: 'Sinkronisasi Pesanan Mitra', status: 'pending' }
                    ];
                    this.processingMessage = 'Menghubungkan ke Gateway QRIS...';
                    this.isProcessing = true;
                    
                    await new Promise(r => setTimeout(r, 1200));
                    this.paymentSteps[0].status = 'done';
                    this.paymentSteps[1].status = 'active';
                    this.processingMessage = 'Menghasilkan kode QRIS unik...';
                    
                    await new Promise(r => setTimeout(r, 1500));
                    this.paymentSteps[1].status = 'done';
                    this.paymentSteps[2].status = 'active';
                    this.processingMessage = 'Menunggu konfirmasi scan & pembayaran...';
                    
                    await new Promise(r => setTimeout(r, 1200));
                } else if (this.paymentMethod === 'gopay') {
                    this.paymentSteps = [
                        { label: 'Koneksi Wallet GoPay', status: 'active' },
                        { label: 'Konfirmasi Aplikasi HP', status: 'pending' },
                        { label: 'Sinkronisasi Pesanan Mitra', status: 'pending' }
                    ];
                    this.processingMessage = 'Menyambungkan dengan e-wallet GoPay...';
                    this.isProcessing = true;
                    
                    await new Promise(r => setTimeout(r, 1200));
                    this.paymentSteps[0].status = 'done';
                    this.paymentSteps[1].status = 'active';
                    this.processingMessage = 'Mengirim permintaan pembayaran ke HP Anda...';
                    
                    await new Promise(r => setTimeout(r, 1800));
                    this.paymentSteps[1].status = 'done';
                    this.paymentSteps[2].status = 'active';
                    this.processingMessage = 'Menunggu konfirmasi transaksi pada HP...';
                    
                    await new Promise(r => setTimeout(r, 1200));
                } else if (this.paymentMethod === 'ovo') {
                    this.paymentSteps = [
                        { label: 'Mengirim Push Notifikasi OVO', status: 'active' },
                        { label: 'Verifikasi PIN Keamanan', status: 'pending' },
                        { label: 'Sinkronisasi Pesanan Mitra', status: 'pending' }
                    ];
                    this.processingMessage = 'Menghubungkan ke layanan OVO...';
                    this.isProcessing = true;
                    
                    await new Promise(r => setTimeout(r, 1200));
                    this.paymentSteps[0].status = 'done';
                    this.paymentSteps[1].status = 'active';
                    this.processingMessage = 'Mengirimkan notifikasi pembayaran ke OVO Anda...';
                    
                    await new Promise(r => setTimeout(r, 1850));
                    this.paymentSteps[1].status = 'done';
                    this.paymentSteps[2].status = 'active';
                    this.processingMessage = 'Menunggu verifikasi PIN keamanan diselesaikan...';
                    
                    await new Promise(r => setTimeout(r, 1200));
                } else { // dana
                    this.paymentSteps = [
                        { label: 'Mengakses DANA Secure Port', status: 'active' },
                        { label: 'Validasi Akun & Pembayaran', status: 'pending' },
                        { label: 'Sinkronisasi Pesanan Mitra', status: 'pending' }
                    ];
                    this.processingMessage = 'Mengamankan koneksi dengan API DANA...';
                    this.isProcessing = true;
                    
                    await new Promise(r => setTimeout(r, 1200));
                    this.paymentSteps[0].status = 'done';
                    this.paymentSteps[1].status = 'active';
                    this.processingMessage = 'Validasi token akun dan otorisasi dana...';
                    
                    await new Promise(r => setTimeout(r, 1500));
                    this.paymentSteps[1].status = 'done';
                    this.paymentSteps[2].status = 'active';
                    this.processingMessage = 'Memproses pembayaran aman DANA...';
                    
                    await new Promise(r => setTimeout(r, 1200));
                }

                // Final step 3: Sinkronisasi Pesanan Mitra
                this.processingMessage = 'Menyelesaikan pesanan Anda...';

                try {
                    const formData = new FormData(document.getElementById('checkout-form'));
                    const response = await fetch("{{ route('consumer.checkout.store') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.paymentSteps[2].status = 'done';
                        this.processingMessage = 'Transaksi Berhasil!';
                        await new Promise(r => setTimeout(r, 800));
                        this.realOrderId = data.order_number;
                        this.realPickupCode = data.pickup_code;
                        this.isProcessing = false;
                        this.paymentComplete = true;
                        this.$nextTick(() => {
                            if (window.lucide) lucide.createIcons();
                        });
                    } else {
                        throw new Error(data.message || 'Gagal membuat pesanan');
                    }
                } catch (error) {
                    this.isProcessing = false;
                    alert('Terjadi kesalahan: ' + error.message);
                }
            },

            copyToClipboard(text) {
                navigator.clipboard.writeText(text);
                alert('Nomor berhasil disalin!');
            }
        }));
    });
</script>
@endsection
