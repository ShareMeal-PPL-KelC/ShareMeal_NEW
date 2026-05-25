@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="{
    paymentMethod: 'qris',
    receivingMethod: 'pickup',
    deliveryTimeSlot: '',
    deliveryFee: {{ $booking->deliveryFee }},
    canDelivery: {{ $booking->canDelivery ? 'true' : 'false' }},
    subtotal: {{ $booking->price * $booking->quantity }},
    get total() {
        return this.receivingMethod === 'delivery' ? this.subtotal + this.deliveryFee : this.subtotal;
    },
    countdown: 600,
    isProcessing: false,
    processingMessage: 'Memverifikasi pembayaran...',
    paymentComplete: false,
    pickupCode: 'PICK-{{ strtoupper(bin2hex(random_bytes(2))) }}',

    init() {
        setInterval(() => {
            if (this.countdown > 0 && !this.paymentComplete && !this.isProcessing) {
                this.countdown--;
            }
        }, 1000);
    },

    formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return mins + ':' + (secs < 10 ? '0' : '') + secs;
    },

    handleConfirmPayment() {
        if (this.receivingMethod === 'delivery' && !this.deliveryTimeSlot) {
            alert('Silakan pilih waktu pengantaran terlebih dahulu.');
            return;
        }

        this.isProcessing = true;
        
        // Simulate step 1: Verification
        setTimeout(() => {
            this.processingMessage = 'Sinkronisasi dengan mitra...';
            
            // Simulate step 2: Finalizing
            setTimeout(() => {
                this.processingMessage = 'Menyiapkan struk digital...';
                
                setTimeout(() => {
                    this.isProcessing = false;
                    this.paymentComplete = true;
                    // Trigger Lucide icons refresh if necessary
                    if (window.lucide) window.lucide.createIcons();
                }, 1000);
            }, 1500);
        }, 1500);
    },

    copyToClipboard(text) {
        navigator.clipboard.writeText(text);
        alert('Nomor berhasil disalin!');
    }
}">
    <!-- Loading Overlay -->
    <div x-show="isProcessing" 
         class="fixed inset-0 z-[100] flex items-center justify-center bg-white/90 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-cloak>
        <div class="text-center space-y-6 max-w-xs px-4">
            <div class="relative w-24 h-24 mx-auto">
                <div class="absolute inset-0 border-4 border-gray-100 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-[#174413] rounded-full border-t-transparent animate-spin"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-8 h-8 text-[#174413]"></i>
                </div>
            </div>
            <div class="space-y-2">
                <h3 class="text-xl font-black text-gray-900" x-text="processingMessage"></h3>
                <p class="text-sm text-gray-500 font-medium">Mohon tunggu sebentar, jangan tutup halaman ini.</p>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div x-show="!paymentComplete">
        <a href="{{ route('consumer.search') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors hover:bg-gray-100 hover:text-gray-900 h-10 px-4 py-2 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Kembali
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Checkout Pembayaran</h1>
        <p class="text-gray-600 mt-1">Selesaikan pembayaran untuk konfirmasi pesanan</p>
    </div>

    <!-- Fase Checkout -->
    <div x-show="!paymentComplete" class="grid lg:grid-cols-3 gap-6">
        <!-- Left Column - Payment Methods -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Timer Card -->
            <div class="rounded-xl border border-orange-200 bg-orange-50 shadow-sm">
                <div class="p-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-orange-600"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span class="font-semibold text-orange-900">Selesaikan pembayaran dalam:</span>
                    </div>
                    <div class="text-2xl font-bold text-orange-600" x-text="formatTime(countdown)"></div>
                </div>
            </div>

            <!-- Selection Card -->
            <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="p-6 pb-4 border-b border-gray-50">
                    <h3 class="text-lg font-bold">Pilih Metode Pengambilan</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Pickup -->
                    <div class="relative flex items-center p-4 border rounded-xl cursor-pointer transition-all"
                         :class="receivingMethod === 'pickup' ? 'border-green-600 bg-green-50/30' : 'border-gray-100 hover:bg-gray-50'"
                         @click="receivingMethod = 'pickup'">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600 shrink-0">
                            <i data-lucide="store" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1 ml-4">
                            <div class="font-bold text-gray-900 text-sm">Ambil di Tempat</div>
                            <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Gratis</div>
                        </div>
                        <input type="radio" name="receiving_method_radio" value="pickup" x-model="receivingMethod" class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-600">
                    </div>

                    <!-- Delivery -->
                    <div class="relative flex items-center p-4 border rounded-xl transition-all"
                         :class="[!canDelivery ? 'opacity-50 cursor-not-allowed bg-gray-50' : 'cursor-pointer', 
                                 receivingMethod === 'delivery' ? 'border-green-600 bg-green-50/30' : 'border-gray-100 hover:bg-gray-50']"
                         @click="if(canDelivery) receivingMethod = 'delivery'">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 shrink-0">
                            <i data-lucide="truck" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1 ml-4">
                            <div class="font-bold text-gray-900 text-sm">Kirim ke Alamat</div>
                            <div class="text-[10px] text-blue-600 font-bold uppercase tracking-wider" x-text="canDelivery ? 'Rp ' + deliveryFee.toLocaleString('id-ID') : 'Tidak Tersedia'"></div>
                        </div>
                        <input type="radio" name="receiving_method_radio" value="delivery" x-model="receivingMethod" :disabled="!canDelivery" class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-600">
                    </div>
                </div>

                <!-- Time Slot Selection -->
                <div x-show="receivingMethod === 'delivery'" x-transition class="pt-8 border-t border-gray-100">
                    <div class="px-8 max-w-lg mx-auto">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 text-center">
                            Pilih Waktu Pengantaran
                        </label>
                        <div class="relative">
                            <select x-model="deliveryTimeSlot" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 outline-none focus:ring-2 focus:ring-[#174413] transition text-sm font-bold text-gray-700 appearance-none">
                                <option value="">-- Klik untuk memilih waktu --</option>
                                @foreach($booking->deliverySlots as $slot)
                                    <option value="{{ $slot->label }}" {{ $slot->is_full ? 'disabled' : '' }}>
                                        {{ $slot->label }} {{ $slot->is_full ? '(Penuh)' : '(' . $slot->remaining . ' slot tersedia)' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-6 pointer-events-none text-gray-400">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-4 italic text-center">Pesanan akan dikirimkan pada rentang waktu yang Anda pilih.</p>
                    </div>
                </div>
            </div>

            <!-- Selection Card -->
            <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="p-6 pb-4 border-b border-gray-50">
                    <h3 class="text-lg font-bold">Pilih Metode Pembayaran</h3>
                </div>
                <div class="p-6 space-y-3">
                    @foreach($paymentMethods as $method)
                    <div class="flex items-center space-x-3 p-4 border border-gray-100 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors"
                         :class="paymentMethod === '{{ $method->id }}' ? 'border-green-600 bg-green-50/30' : ''"
                         @click="paymentMethod = '{{ $method->id }}'">
                        <input type="radio" name="payment_method_radio" value="{{ $method->id }}" x-model="paymentMethod" class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-600">
                        <div class="flex items-center gap-4 flex-1 ml-2">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600 shrink-0">
                                @if($method->id === 'qris')
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><rect x="7" y="7" width="3" height="9"></rect><rect x="14" y="7" width="3" height="5"></rect></svg>
                                @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="font-bold text-gray-900">{{ $method->name }}</div>
                                <div class="text-sm text-gray-500 font-medium">{{ $method->description }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Payment Instructions Card -->
            <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="p-6 pb-4 border-b border-gray-50">
                    <h3 class="text-lg font-bold">Instruksi Pembayaran</h3>
                </div>
                <div class="p-6">
                    <!-- QRIS -->
                    <div x-show="paymentMethod === 'qris'" class="space-y-6">
                        <div class="bg-gray-50 p-8 border-2 border-dashed border-gray-200 rounded-2xl flex justify-center">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=SHAREMEAL-PAY-{{ $booking->id }}" alt="QRIS" class="w-48 h-48 rounded-xl shadow-sm">
                        </div>
                        <div class="text-center space-y-2">
                            <p class="font-bold text-gray-900">Scan QR Code dengan aplikasi pembayaran:</p>
                            <p class="text-sm text-gray-500 font-medium">GoPay, OVO, DANA, LinkAja, ShopeePay, atau Mobile Banking</p>
                        </div>
                        <div class="h-px bg-gray-100 w-full my-6"></div>
                        <ol class="list-decimal list-inside space-y-3 text-sm text-gray-700 font-medium ml-4">
                            <li>Buka aplikasi e-wallet atau mobile banking Anda</li>
                            <li>Pilih menu Scan QR / QRIS</li>
                            <li>Arahkan kamera ke QR Code di atas</li>
                            <li>Periksa detail pembayaran</li>
                            <li>Konfirmasi pembayaran</li>
                        </ol>
                    </div>

                    <!-- E-Wallets -->
                    <div x-show="['gopay', 'ovo', 'dana'].includes(paymentMethod)" class="space-y-6" style="display: none;">
                        <div class="bg-gray-50 p-6 rounded-2xl text-center border border-gray-100">
                            <p class="text-sm text-gray-500 font-bold uppercase tracking-widest mb-3">Nomor Tujuan <span x-text="paymentMethod"></span></p>
                            <div class="flex items-center justify-center gap-3">
                                <p class="text-3xl font-mono font-black text-[#174413]">0812-3456-7890</p>
                                <button @click="copyToClipboard('081234567890')" class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition shadow-sm text-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                </button>
                            </div>
                        </div>
                        <div class="h-px bg-gray-100 w-full my-6"></div>
                        <ol class="list-decimal list-inside space-y-3 text-sm text-gray-700 font-medium ml-4">
                            <li>Buka aplikasi e-wallet Anda</li>
                            <li>Pilih menu Transfer / Kirim Uang</li>
                            <li>Masukkan nomor tujuan di atas</li>
                            <li>Masukkan nominal: <strong class="text-gray-900">Rp {{ number_format($booking->price, 0, ',', '.') }}</strong></li>
                            <li>Periksa detail dan konfirmasi pembayaran</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <button @click="handleConfirmPayment()" class="w-full flex items-center justify-center gap-2 bg-[#174413] text-white py-4 rounded-2xl font-black shadow-xl shadow-green-100 hover:bg-[#256020] transition hover:scale-[1.01]">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                Saya Sudah Bayar
            </button>
            <p class="text-xs font-bold text-center text-gray-400 uppercase tracking-widest mt-4">Klik tombol di atas setelah menyelesaikan pembayaran</p>
        </div>

        <!-- Right Column - Order Summary -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm sticky top-6">
                <div class="p-6 pb-4 border-b border-gray-50">
                    <h3 class="text-lg font-bold">Ringkasan Pesanan</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-green-600"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                            <span class="font-bold text-gray-900">{{ $booking->storeName }}</span>
                        </div>
                        <p class="text-sm text-gray-500 font-medium">{{ $booking->address }}</p>
                    </div>

                    <div class="h-px bg-gray-100 w-full"></div>

                    <div>
                        <h4 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-3">Item Pesanan</h4>
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-bold text-sm text-gray-900">{{ $booking->dealItem }}</p>
                                <p class="text-xs text-gray-500 font-medium mt-1">Qty: {{ $booking->quantity }}</p>
                            </div>
                            <p class="font-bold text-sm text-gray-900">Rp {{ number_format($booking->price, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="h-px bg-gray-100 w-full"></div>

                    <div>
                        <h4 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-3">Detail Pembayaran</h4>
                        <div class="space-y-2 text-sm font-medium">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span class="text-gray-900">Rp {{ number_format($booking->price * $booking->quantity, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Biaya Admin</span>
                                <span class="text-gray-900">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-gray-600" x-show="receivingMethod === 'delivery'">
                                <span>Ongkos Kirim</span>
                                <span class="text-gray-900" x-text="'Rp ' + deliveryFee.toLocaleString('id-ID')"></span>
                            </div>
                            <div class="pt-3 mt-3 border-t border-gray-100 flex justify-between text-lg font-black">
                                <span class="text-gray-900">Total</span>
                                <span class="text-green-600" x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                    </div>

                    <div class="h-px bg-gray-100 w-full"></div>

                    <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                        <h4 class="text-xs font-black uppercase tracking-widest text-gray-400" x-text="receivingMethod === 'delivery' ? 'Info Pengiriman' : 'Info Pengambilan'"></h4>
                        <div class="flex items-start gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-gray-400 mt-0.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <div>
                                <p class="text-xs font-bold text-gray-900" x-text="receivingMethod === 'delivery' ? 'Estimasi Sampai' : 'Jadwal Ambil'"></p>
                                <p class="text-xs text-gray-500 font-medium">{{ $booking->pickupTime }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fase Sukses (Struk Digital) -->
    <div x-show="paymentComplete" class="max-w-2xl mx-auto" style="display: none;" 
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        <div class="rounded-3xl border border-gray-100 bg-white shadow-2xl overflow-hidden relative">
            <!-- Decorative Background Element -->
            <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-green-500 via-green-600 to-green-700"></div>
            
            <div class="p-10 text-center">
                <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6 ring-8 ring-green-50/50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-green-600 animate-[bounce_2s_infinite]"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <h2 class="text-3xl font-black text-gray-900 mb-2">Pembayaran Berhasil!</h2>
                <p class="text-gray-500 font-medium mb-8" x-text="receivingMethod === 'delivery' ? 'Pesanan Anda sedang diproses oleh mitra.' : 'Pesanan Anda siap diambil sesuai jadwal.'"></p>

                <div class="bg-gray-50 border border-gray-100 rounded-3xl mb-8 overflow-hidden">
                    <div class="bg-white border-b border-gray-100 px-6 py-4 flex justify-between items-center">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Nomor Transaksi</span>
                        <span class="text-xs font-mono font-bold text-gray-900">{{ $booking->id }}</span>
                    </div>
                    <div class="p-8 space-y-5 text-left">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1" x-text="receivingMethod === 'delivery' ? 'Metode' : 'Metode'"></span>
                                <div class="flex items-center gap-1.5 font-bold text-gray-900 text-sm">
                                    <template x-if="receivingMethod === 'delivery'">
                                        <i data-lucide="truck" class="w-3.5 h-3.5 text-blue-600"></i>
                                    </template>
                                    <template x-if="receivingMethod === 'pickup'">
                                        <i data-lucide="store" class="w-3.5 h-3.5 text-green-600"></i>
                                    </template>
                                    <span x-text="receivingMethod === 'delivery' ? 'Delivery' : 'Self Pickup'"></span>
                                </div>
                            </div>
                            <div>
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Pembayaran</span>
                                <div class="font-bold text-gray-900 text-sm uppercase" x-text="paymentMethod"></div>
                            </div>
                        </div>

                        <div class="h-px bg-gray-200/50 w-full"></div>

                        <div>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2" x-text="receivingMethod === 'delivery' ? 'Alamat Pengantaran' : 'Lokasi Pengambilan'"></span>
                            <div class="font-bold text-gray-900 text-sm mb-0.5">{{ $booking->storeName }}</div>
                            <div class="text-xs text-gray-500 font-medium leading-relaxed">{{ $booking->address }}</div>
                        </div>

                        <div class="h-px bg-gray-200/50 w-full"></div>

                        <div class="flex justify-between items-end">
                            <div>
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1" x-text="receivingMethod === 'delivery' ? 'Estimasi Sampai' : 'Jadwal Ambil'"></span>
                                <div class="font-bold text-gray-900 text-sm" x-text="receivingMethod === 'delivery' ? deliveryTimeSlot : '{{ $booking->pickupTime }}'"></div>
                            </div>
                            <div class="text-right" x-show="receivingMethod === 'pickup'">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Kode Ambil</span>
                                <div class="font-mono font-black text-green-700 text-xl tracking-tighter" x-text="pickupCode"></div>
                            </div>
                        </div>

                        <div class="pt-4 border-t-2 border-dashed border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-gray-900">Total Pembayaran</span>
                                <div class="font-black text-green-600 text-2xl" x-text="'Rp ' + total.toLocaleString('id-ID')"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button @click="const form = document.getElementById('checkout-form'); 
                            const inputCode = document.createElement('input'); inputCode.type = 'hidden'; inputCode.name = 'pickup_code'; inputCode.value = pickupCode; form.appendChild(inputCode);
                            const inputSlot = document.createElement('input'); inputSlot.type = 'hidden'; inputSlot.name = 'delivery_time_slot_final'; inputSlot.value = deliveryTimeSlot; form.appendChild(inputSlot);
                            form.submit();"
                            class="flex-1 flex items-center justify-center gap-3 bg-[#174413] text-white py-4 px-10 rounded-2xl font-black shadow-xl shadow-green-100 hover:bg-[#256020] transition active:scale-95">
                        <i data-lucide="history" class="w-5 h-5"></i>
                        Lihat Riwayat Pesanan
                    </button>
                    <button @click="window.print()" class="flex items-center justify-center gap-2 bg-gray-100 text-gray-700 py-4 px-6 rounded-2xl font-black hover:bg-gray-200 transition">
                        <i data-lucide="printer" class="w-5 h-5"></i>
                        Cetak Struk
                    </button>
                </div>
            </div>
        </div>
        <p class="text-center text-gray-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-8">Terima kasih telah membantu mengurangi food waste!</p>
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
@endsection
