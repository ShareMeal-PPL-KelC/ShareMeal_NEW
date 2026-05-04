@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="{
    paymentMethod: 'qris',
    countdown: 600,
    paymentComplete: false,
    pickupCode: 'PICK-{{ strtoupper(bin2hex(random_bytes(2))) }}', 

    init() {
        setInterval(() => {
            if (this.countdown > 0 && !this.paymentComplete) {
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
        this.paymentComplete = true;
    },

    copyToClipboard(text) {
        navigator.clipboard.writeText(text);
        alert('Nomor berhasil disalin!');
    }
}">
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
                            <div class="pt-3 mt-3 border-t border-gray-100 flex justify-between text-lg font-black">
                                <span class="text-gray-900">Total</span>
                                <span class="text-green-600">Rp {{ number_format($booking->price * $booking->quantity, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="h-px bg-gray-100 w-full"></div>

                    <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                        <h4 class="text-xs font-black uppercase tracking-widest text-gray-400">Info Pengambilan</h4>
                        <div class="flex items-start gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-gray-400 mt-0.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <div>
                                <p class="text-xs font-bold text-gray-900">Jadwal Ambil</p>
                                <p class="text-xs text-gray-500 font-medium">{{ $booking->pickupTime }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fase Sukses (Struk Digital) -->
    <div x-show="paymentComplete" class="max-w-2xl mx-auto" style="display: none;" x-transition>
        <div class="rounded-3xl border border-gray-100 bg-white shadow-xl overflow-hidden">
            <div class="p-12 text-center">
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 text-green-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <h2 class="text-3xl font-black text-gray-900 mb-2">Pembayaran Berhasil!</h2>
                <p class="text-gray-500 font-medium mb-8">Terima kasih, pesanan Anda sedang diproses oleh penjual.</p>

                <div class="bg-gray-50 border border-gray-100 p-8 rounded-2xl mb-8 max-w-sm mx-auto shadow-inner">
                    <div class="space-y-4 text-left">
                        <div>
                            <span class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-1">Lokasi Pengambilan</span>
                            <div class="font-bold text-gray-900 text-sm mb-0.5">{{ $booking->storeName }}</div>
                            <div class="text-xs text-gray-500 font-medium">{{ $booking->address }}</div>
                        </div>
                        <div class="h-px bg-gray-200 w-full"></div>
                        <div>
                            <span class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-1">Kode Pengambilan</span>
                            <div class="font-mono font-black text-[#174413] text-2xl tracking-wider" x-text="pickupCode"></div>
                        </div>
                        <div class="h-px bg-gray-200 w-full"></div>
                        <div>
                            <span class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-1">Total Bayar</span>
                            <div class="font-black text-green-600 text-xl">Rp {{ number_format($booking->price * $booking->quantity, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button @click="const form = document.getElementById('checkout-form'); const input = document.createElement('input'); input.type = 'hidden'; input.name = 'pickup_code'; input.value = pickupCode; form.appendChild(input); form.submit();"
                            class="flex items-center justify-center gap-2 bg-[#174413] text-white py-4 px-8 rounded-2xl font-black shadow-xl shadow-green-100 hover:bg-[#256020] transition">
                        Lihat Riwayat
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form id="checkout-form" action="{{ route('consumer.checkout.store') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="product_id" value="{{ $booking->product_id }}">
        <input type="hidden" name="mitra_id" value="{{ $booking->mitra_id }}">
        <input type="hidden" name="quantity" value="{{ $booking->quantity }}">
        <input type="hidden" name="price" value="{{ $booking->price }}">
        <input type="hidden" name="payment_method" :value="paymentMethod">
    </form>
</div>
@endsection
