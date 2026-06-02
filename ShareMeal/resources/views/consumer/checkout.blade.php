@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="checkoutPage">
    <!-- Loading Overlay -->
    <div x-show="isProcessing" 
         class="fixed inset-0 z-[100] flex items-center justify-center bg-white/80 backdrop-blur-md"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-cloak>
        <div class="text-center space-y-6 max-w-xs px-4">
            <div class="relative w-24 h-24 mx-auto">
                <div class="absolute inset-0 border-4 border-gray-100/50 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-[#174413] rounded-full border-t-transparent animate-spin"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-8 h-8 text-[#174413] animate-pulse"></i>
                </div>
            </div>
            <div class="space-y-2">
                <h3 class="text-xl font-black text-gray-900" x-text="processingMessage"></h3>
                <p class="text-sm text-gray-550 font-medium leading-relaxed">Mohon tunggu sebentar, jangan tutup halaman ini.</p>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div x-show="!paymentComplete" class="mb-12 reveal">
        <a href="{{ route('consumer.search') }}" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-luxury-gold hover:text-luxury-forest transition-colors mb-6 group">
            <i data-lucide="arrow-left" class="w-4 h-4 transition-transform group-hover:-translate-x-1"></i>
            Back to Curation
        </a>
        <h1 class="text-5xl font-serif font-bold text-luxury-forest leading-tight">Finalizing Curation</h1>
        <p class="text-luxury-slate font-medium mt-2 tracking-wide">Complete your contribution to a sustainable future.</p>
    </div>

    <!-- Fase Checkout -->
    <div x-show="!paymentComplete" class="grid lg:grid-cols-3 gap-12">
        <!-- Left Column - Methods & Payment -->
        <div class="lg:col-span-2 space-y-10">
            <!-- Timer Card -->
            <div class="rounded-[1.5rem] bg-luxury-gold/5 border border-luxury-gold/20 px-8 py-4 flex items-center justify-between reveal">
                <div class="flex items-center gap-4">
                    <div class="w-2 h-2 bg-luxury-gold rounded-full animate-pulse"></div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-luxury-gold">Secure Checkout Session expires in:</span>
                </div>
                <div class="text-2xl font-serif font-bold text-luxury-gold" x-text="formatTime(countdown)"></div>
            </div>

            <!-- Selection Card -->
            <div class="glass-card rounded-[2.5rem] overflow-hidden reveal delay-100">
                <div class="p-10 border-b border-luxury-alabas/60 bg-white/30">
                    <h3 class="text-2xl font-serif font-bold text-luxury-forest">Fulfillment Method</h3>
                </div>
                <div class="p-10 grid grid-cols-1 md:grid-cols-2 gap-6 bg-white/10">
                    <!-- Pickup -->
                    <label class="relative block cursor-pointer group">
                        <input type="radio" name="receiving_method_radio" value="pickup" x-model="receivingMethod" class="sr-only peer">
                        <div class="p-8 border-2 border-luxury-alabas/80 bg-white/40 rounded-[2rem] transition-all duration-500 peer-checked:border-luxury-forest peer-checked:bg-luxury-forest/5 group-hover:border-luxury-gold/30">
                            <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-luxury-gold mb-6 group-hover:scale-110 transition-transform shadow-sm">
                                <i data-lucide="store" class="w-6 h-6"></i>
                            </div>
                            <div class="font-serif text-xl font-bold text-luxury-forest">Boutique Pickup</div>
                            <div class="text-[10px] text-luxury-gold font-black uppercase tracking-widest mt-2">Complimentary</div>
                        </div>
                    </label>

                    <!-- Delivery -->
                    <label class="relative block" :class="canDelivery ? 'cursor-pointer group' : 'opacity-40'">
                        <input type="radio" name="receiving_method_radio" value="delivery" x-model="receivingMethod" :disabled="!canDelivery" class="sr-only peer">
                        <div class="p-8 border-2 border-luxury-alabas/80 bg-white/40 rounded-[2rem] transition-all duration-500 peer-checked:border-luxury-forest peer-checked:bg-luxury-forest/5 group-hover:border-luxury-gold/30">
                            <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-luxury-emerald mb-6 group-hover:scale-110 transition-transform shadow-sm">
                                <i data-lucide="truck" class="w-6 h-6"></i>
                            </div>
                            <div class="font-serif text-xl font-bold text-luxury-forest">Private Delivery</div>
                            <div class="text-[10px] text-luxury-emerald font-black uppercase tracking-widest mt-2" x-text="canDelivery ? 'Rp ' + deliveryFee.toLocaleString('id-ID') : 'Unavailable'"></div>
                        </div>
                    </label>
                </div>

                <!-- Time Slot Selection -->
                <div x-show="receivingMethod === 'delivery'" 
                     x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 -translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="px-10 pb-10 border-t border-luxury-alabas/60 pt-10 bg-white/10">
                    <div class="max-w-md mx-auto text-center">
                        <span class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em] mb-6 block text-center">Select Arrival Window</span>
                        <div class="relative">
                            <select x-model="deliveryTimeSlot" class="w-full bg-white/80 border border-luxury-alabas rounded-[1.2rem] px-8 py-5 outline-none focus:ring-2 focus:ring-luxury-forest transition-all font-bold text-luxury-forest appearance-none text-center shadow-sm">
                                <option value="">-- Choose your preferred time --</option>
                                @foreach($booking->deliverySlots as $slot)
                                    <option value="{{ $slot->label }}" {{ $slot->is_full ? 'disabled' : '' }}>
                                        {{ $slot->label }} {{ $slot->is_full ? '(Reserved)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <i data-lucide="chevron-down" class="absolute right-6 top-1/2 -translate-y-1/2 w-4 h-4 text-luxury-gold pointer-events-none"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Card -->
            <div class="glass-card rounded-[2.5rem] overflow-hidden reveal delay-200">
                <div class="p-10 border-b border-luxury-alabas/60 bg-white/30">
                    <h3 class="text-2xl font-serif font-bold text-luxury-forest">Secure Settlement</h3>
                </div>
                <div class="p-10 space-y-4 bg-white/10">
                    @foreach($paymentMethods as $method)
                    <label class="relative block cursor-pointer group">
                        <input type="radio" name="payment_method_radio" value="{{ $method->id }}" x-model="paymentMethod" class="sr-only peer">
                        <div class="flex items-center justify-between p-6 bg-white/40 border border-luxury-alabas rounded-[1.5rem] transition-all duration-500 peer-checked:border-luxury-forest peer-checked:bg-luxury-forest/5 group-hover:border-luxury-gold/30">
                            <div class="flex items-center gap-6">
                                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-luxury-forest shadow-sm border border-luxury-alabas/60">
                                    @if($method->id === 'qris')
                                    <i data-lucide="qr-code" class="w-6 h-6 stroke-[1.5]"></i>
                                    @else
                                    <i data-lucide="credit-card" class="w-6 h-6 stroke-[1.5]"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-serif text-lg font-bold text-luxury-forest">{{ $method->name }}</div>
                                    <div class="text-[10px] text-luxury-slate font-black uppercase tracking-widest mt-1">{{ $method->description }}</div>
                                </div>
                            </div>
                            <div class="w-6 h-6 border-2 border-luxury-alabas rounded-full flex items-center justify-center peer-checked:border-luxury-forest bg-white">
                                <div class="w-3 h-3 bg-luxury-forest rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
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
                <h3 class="text-2xl font-serif font-bold text-luxury-forest mb-8">Summary</h3>
                
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <i data-lucide="store" class="w-5 h-5 text-luxury-gold mt-1"></i>
                        <div>
                            <div class="text-sm font-bold text-luxury-forest">{{ $booking->storeName }}</div>
                            <div class="text-xs text-luxury-slate mt-1 italic">{{ $booking->address }}</div>
                        </div>
                    </div>

                    <div class="h-px bg-luxury-alabas/60"></div>

                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-sm font-bold text-luxury-forest">{{ $booking->dealItem }}</div>
                            <div class="text-[10px] text-luxury-slate font-black uppercase tracking-widest mt-1">Quantity: {{ $booking->quantity }}</div>
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
                            <span>Shipping</span>
                            <span x-text="'Rp ' + deliveryFee.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="pt-4 mt-4 border-t border-luxury-alabas/60 flex justify-between items-end">
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-luxury-gold mb-1">Total Investment</span>
                            <span class="text-3xl font-serif font-black text-luxury-forest leading-none" x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <button @click="handleConfirmPayment()" 
                            class="w-full bg-luxury-forest text-white py-6 rounded-[1.5rem] font-black uppercase tracking-[0.3em] text-[10px] hover:bg-luxury-gold transition-all duration-500 luxury-shadow mt-10 active:scale-95 flex items-center justify-center gap-3 group">
                        Confirm & Process
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Fase Sukses (Struk Digital) -->
    <div x-show="paymentComplete" class="max-w-2xl mx-auto py-12" style="display: none;" 
         x-transition:enter="transition ease-out duration-1000"
         x-transition:enter-start="opacity-0 translate-y-12 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100">
        
        <div class="glass-panel rounded-[3.5rem] shadow-2xl border border-white/40 overflow-hidden relative">
            <!-- Top Elegant Accent -->
            <div class="h-2 w-full bg-gradient-to-r from-luxury-forest via-luxury-gold to-luxury-emerald"></div>
            
            <div class="p-12 lg:p-16 text-center">
                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-10 luxury-shadow border border-luxury-alabas">
                    <i data-lucide="check" class="w-10 h-10 text-luxury-forest stroke-[3] animate-in zoom-in duration-700"></i>
                </div>
                
                <h2 class="text-4xl font-serif font-bold text-luxury-forest mb-4">Contribution Confirmed</h2>
                <p class="text-luxury-slate font-medium mb-12 tracking-wide" x-text="receivingMethod === 'delivery' ? 'Your curated selection is now being prepared for transit.' : 'Your selection is secured and awaits your arrival at the boutique.'"></p>

                <div class="bg-white/40 rounded-[2.5rem] border border-luxury-alabas p-10 mb-12 text-left relative overflow-hidden">
                    <!-- Invoice Decoration -->
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <i data-lucide="leaf" class="w-32 h-32 text-luxury-forest -rotate-12"></i>
                    </div>

                    <div class="flex justify-between items-center mb-10 pb-6 border-b border-luxury-alabas/50">
                        <div>
                            <span class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-2">Transaction ID</span>
                            <span class="font-mono text-xs font-bold text-luxury-forest tracking-tighter" x-text="realOrderId || '{{ $booking->id }}'"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-2">Status</span>
                            <span class="text-[10px] font-black text-luxury-emerald uppercase tracking-widest bg-luxury-emerald/10 px-3 py-1 rounded-lg">Settled</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-10 mb-10">
                        <div>
                            <span class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-3">Service</span>
                            <div class="flex items-center gap-3">
                                <i :data-lucide="receivingMethod === 'delivery' ? 'truck' : 'store'" class="w-4 h-4 text-luxury-forest"></i>
                                <span class="text-sm font-bold text-luxury-forest uppercase tracking-widest" x-text="receivingMethod === 'delivery' ? 'Delivery' : 'Boutique Pickup'"></span>
                            </div>
                        </div>
                        <div>
                            <span class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-3">Settlement</span>
                            <div class="flex items-center gap-3">
                                <i data-lucide="shield-check" class="w-4 h-4 text-luxury-forest"></i>
                                <span class="text-sm font-bold text-luxury-forest uppercase" x-text="paymentMethod"></span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-10 p-6 bg-white rounded-2xl border border-luxury-alabas/50 shadow-sm">
                        <span class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-3" x-text="receivingMethod === 'delivery' ? 'Destination' : 'Boutique Location'"></span>
                        <div class="text-sm font-bold text-luxury-forest mb-1">{{ $booking->storeName }}</div>
                        <div class="text-xs text-luxury-slate leading-relaxed font-medium italic opacity-85">{{ $booking->address }}</div>
                    </div>

                    <div class="flex justify-between items-end">
                        <div>
                            <span class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-2" x-text="receivingMethod === 'delivery' ? 'Expected Arrival' : 'Pick-up Schedule'"></span>
                            <div class="text-sm font-black text-luxury-forest uppercase tracking-widest" x-text="receivingMethod === 'delivery' ? deliveryTimeSlot : '{{ $booking->pickupTime }}'"></div>
                        </div>
                        <div class="text-right" x-show="receivingMethod === 'pickup'">
                            <span class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em] block mb-2">Claim Code</span>
                            <div class="font-mono text-3xl font-black text-luxury-forest tracking-tighter" x-text="realPickupCode || pickupCode"></div>
                        </div>
                    </div>

                    <!-- Total Row -->
                    <div class="mt-10 pt-8 border-t-2 border-dashed border-luxury-alabas/50">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.3em]">Total Contribution</span>
                            <div class="text-3xl font-serif font-black text-luxury-forest" x-text="'Rp ' + total.toLocaleString('id-ID')"></div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <a href="{{ route('consumer.history') }}"
                       class="flex-[2] flex items-center justify-center gap-4 bg-luxury-forest text-white py-5 px-10 rounded-[1.5rem] font-black uppercase tracking-[0.2em] text-[10px] shadow-xl hover:bg-luxury-gold transition-all duration-500 active:scale-95 group">
                        <i data-lucide="calendar" class="w-4 h-4 text-luxury-gold transition-transform group-hover:rotate-12"></i>
                        View My History
                    </a>
                    <button @click="window.print()" class="flex-1 flex items-center justify-center gap-3 bg-white text-luxury-slate py-5 px-8 rounded-[1.5rem] border border-luxury-alabas font-black uppercase tracking-[0.2em] text-[10px] hover:bg-luxury-ivory transition-all duration-500">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        Print
                    </button>
                </div>
            </div>
        </div>
        <p class="text-center text-luxury-slate/40 text-[10px] font-black uppercase tracking-[0.4em] mt-12">Elevating sustainability through mindful curation.</p>
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
            countdown: 600,
            isProcessing: false,
            processingMessage: 'Memverifikasi pembayaran...',
            paymentComplete: false,
            realOrderId: '',
            realPickupCode: '',
            pickupCode: 'PICK-A1B2',

            get total() {
                return this.receivingMethod === 'delivery' ? this.subtotal + this.deliveryFee : this.subtotal;
            },

            init() {
                setInterval(() => {
                    if (this.countdown > 0 && !this.paymentComplete && !this.isProcessing) {
                        this.countdown--;
                    }
                }, 1000);
                
                if (window.lucide) {
                    lucide.createIcons();
                }
            },

            formatTime(seconds) {
                const mins = Math.floor(seconds / 60);
                const secs = seconds % 60;
                return mins + ':' + (secs < 10 ? '0' : '') + secs;
            },

            async handleConfirmPayment() {
                if (this.receivingMethod === 'delivery' && !this.deliveryTimeSlot) {
                    alert('Silakan pilih waktu pengantaran terlebih dahulu.');
                    return;
                }

                this.isProcessing = true;
                this.processingMessage = 'Memverifikasi pembayaran...';
                
                await new Promise(r => setTimeout(r, 1500));
                this.processingMessage = 'Sinkronisasi dengan mitra...';
                
                await new Promise(r => setTimeout(r, 1500));
                this.processingMessage = 'Menyelesaikan pesanan...';

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
