@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto space-y-8" x-data="{ 
    countdown: {{ $remainingSeconds }},
    subtotal: {{ $subtotal }},
    successMessage: '',
    errorMessage: '',
    confirmShow: false,
    confirmTitle: '',
    confirmMsg: '',
    confirmItemId: null,
    items: {
        @foreach($cartItems as $item)
        '{{ $item->id }}': {
            id: {{ $item->id }},
            quantity: {{ $item->quantity }},
            originalQuantity: {{ $item->quantity }},
            price: {{ ($item->product->status === 'flash-sale' && $item->product->discount_price > 0) ? $item->product->discount_price : $item->product->price }},
            stock: {{ $item->product->stock }},
            isLoading: false
        },
        @endforeach
    },
    init() {
        if (this.countdown > 0) {
            setInterval(() => {
                this.countdown--;
                if (this.countdown <= 0) {
                    window.location.reload();
                }
            }, 1000);
        }
    },
    formatTime(seconds) {
        const totalSecs = Math.floor(seconds);
        if (totalSecs <= 0) return '0:00';
        const mins = Math.floor(totalSecs / 60);
        const secs = totalSecs % 60;
        return mins + ':' + (secs < 10 ? '0' : '') + secs;
    },
    formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    },
    async changeQty(itemId, change) {
        let item = this.items[itemId];
        if (item.isLoading) return;
        
        let newVal = item.quantity + change;
        if (newVal < 1) {
            return;
        }
        
        let maxVal = item.stock + item.quantity;
        if (newVal > maxVal) {
            this.showToast('error', 'Jumlah kuantitas tidak boleh melebihi stok yang tersedia (' + maxVal + ' pcs).');
            return;
        }
        
        await this.submitQty(itemId, newVal);
    },
    async onInputBlur(itemId) {
        let item = this.items[itemId];
        let val = parseInt(item.quantity);
        let maxVal = item.stock + item.originalQuantity;
        
        if (isNaN(val) || val < 1) {
            item.quantity = item.originalQuantity;
            return;
        }
        
        if (val > maxVal) {
            this.showToast('error', 'Jumlah kuantitas tidak boleh melebihi stok yang tersedia (' + maxVal + ' pcs).');
            item.quantity = maxVal;
            val = maxVal;
        }
        
        if (val === item.originalQuantity) {
            return;
        }
        
        await this.submitQty(itemId, val);
    },
    async submitQty(itemId, qty) {
        let item = this.items[itemId];
        item.isLoading = true;
        this.successMessage = '';
        this.errorMessage = '';
        
        try {
            let response = await fetch('/consumer/cart/update/' + itemId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ quantity: qty })
            });
            
            let data = await response.json();
            
            if (response.ok && data.success) {
                if (data.deleted) {
                    window.location.reload();
                    return;
                }
                item.quantity = data.quantity;
                item.originalQuantity = data.quantity;
                item.stock = data.product_stock;
                this.subtotal = data.cart_subtotal;
                if (data.remaining_seconds) {
                    this.countdown = data.remaining_seconds;
                }
                this.showToast('success', data.message || 'Kuantitas keranjang berhasil diperbarui.');
            } else {
                this.showToast('error', data.message || 'Gagal memperbarui kuantitas.');
                item.quantity = item.originalQuantity;
            }
        } catch (error) {
            console.error(error);
            this.showToast('error', 'Terjadi kesalahan sistem.');
            item.quantity = item.originalQuantity;
        } finally {
            item.isLoading = false;
        }
    },
    confirmDelete(itemId) {
        this.confirmItemId = itemId;
        this.confirmTitle = 'Hapus Makanan?';
        this.confirmMsg = 'Apakah Anda yakin ingin menghapus makanan ini dari keranjang belanja Anda?';
        this.confirmShow = true;
    },
    async executeDelete() {
        this.confirmShow = false;
        let itemId = this.confirmItemId;
        if (!itemId) return;
        let item = this.items[itemId];
        item.isLoading = true;
        
        try {
            let response = await fetch('/consumer/cart/remove/' + itemId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            let data = await response.json();
            if (response.ok && data.success) {
                window.location.reload();
            } else {
                this.showToast('error', data.message || 'Gagal menghapus produk.');
                item.isLoading = false;
            }
        } catch (error) {
            console.error(error);
            this.showToast('error', 'Terjadi kesalahan sistem.');
            item.isLoading = false;
        }
    },
    showToast(type, msg) {
        if (type === 'success') {
            this.successMessage = msg;
            setTimeout(() => { this.successMessage = ''; }, 3000);
        } else {
            this.errorMessage = msg;
            setTimeout(() => { this.errorMessage = ''; }, 4000);
        }
    }
}">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8 reveal">
        <div>
            <h1 class="text-4xl font-serif font-black text-luxury-forest leading-tight">Keranjang Belanja</h1>
            <p class="text-sm font-medium text-luxury-slate mt-1.5">Selesaikan pemesanan Anda sebelum batas waktu reservasi berakhir.</p>
        </div>
        <a href="{{ route('consumer.search') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/80 border border-luxury-alabas/85 text-[10px] font-black uppercase tracking-widest text-luxury-forest hover:bg-[#174413] hover:text-white transition-all duration-300 shadow-sm group">
            <i data-lucide="arrow-left" class="w-4 h-4 transition-transform group-hover:-translate-x-1 stroke-[2.5]"></i>
            Kembali Belanja
        </a>
    </div>

    <!-- Reactive Toast Messages -->
    <div x-show="successMessage" x-transition.duration.300ms class="bg-emerald-50/80 backdrop-blur-md border border-emerald-200/60 text-emerald-800 px-6 py-4 rounded-2xl flex items-center gap-3 reveal shadow-sm" style="display: none;">
        <i data-lucide="check-circle" class="w-5 h-5 text-[#10B981]"></i>
        <span class="text-sm font-semibold" x-text="successMessage"></span>
    </div>

    <div x-show="errorMessage" x-transition.duration.300ms class="bg-red-50/80 backdrop-blur-md border border-red-200/60 text-red-800 px-6 py-4 rounded-2xl flex items-center gap-3 reveal shadow-sm" style="display: none;">
        <i data-lucide="x-circle" class="w-5 h-5 text-red-500"></i>
        <span class="text-sm font-semibold" x-text="errorMessage"></span>
    </div>

    <!-- PHP Flash Messages (Fallback) -->
    @if(session('success'))
    <div class="bg-emerald-50/80 backdrop-blur-md border border-emerald-200/60 text-emerald-800 px-6 py-4 rounded-2xl flex items-center gap-3 reveal">
        <i data-lucide="check-circle" class="w-5 h-5 text-[#10B981]"></i>
        <span class="text-sm font-semibold">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50/80 backdrop-blur-md border border-red-200/60 text-red-800 px-6 py-4 rounded-2xl flex items-center gap-3 reveal">
        <i data-lucide="x-circle" class="w-5 h-5 text-red-500"></i>
        <span class="text-sm font-semibold">{{ session('error') }}</span>
    </div>
    @endif

    @if(session('error_different_store'))
    <div class="bg-amber-55 border border-amber-200 text-amber-900 px-6 py-5 rounded-[1.5rem] space-y-4 reveal" x-data="{ open: true }" x-show="open">
        <div class="flex items-start gap-3.5">
            <i data-lucide="alert-triangle" class="w-6 h-6 text-amber-600 mt-0.5 shrink-0"></i>
            <div>
                <h4 class="font-bold text-base leading-snug">Keranjang Berisi Makanan dari Toko Lain</h4>
                <p class="text-sm font-medium text-amber-800 mt-1 leading-relaxed">{{ session('error_different_store') }}</p>
            </div>
        </div>
    </div>
    @endif

    @if($cartItems->isNotEmpty())
        <!-- Expiration Timer Card -->
        <div class="rounded-2xl bg-luxury-gold/5 border border-luxury-gold/20 px-8 py-4 flex items-center justify-between reveal">
            <div class="flex items-center gap-3">
                <div class="w-2.5 h-2.5 bg-luxury-gold rounded-full animate-pulse"></div>
                <span class="text-xs font-black uppercase tracking-widest text-luxury-gold">Sesi Kunci Stok Berakhir Dalam:</span>
            </div>
            <div class="text-2xl font-serif font-black text-luxury-gold" x-text="formatTime(countdown)"></div>
        </div>

        <!-- Cart Items List -->
        <div class="glass-card rounded-[2.5rem] overflow-hidden reveal delay-100">
            <div class="p-8 border-b border-luxury-alabas/60 bg-white/30 flex items-center justify-between">
                <h2 class="text-xl font-serif font-bold text-luxury-forest flex items-center gap-2">
                    <i data-lucide="shopping-bag" class="w-5 h-5 text-luxury-gold"></i>
                    Detail Makanan yang Direservasi
                </h2>
                <span class="text-xs font-black uppercase tracking-widest text-luxury-gold bg-luxury-gold/10 px-3 py-1 rounded-full">
                    {{ $cartItems->first()->product->user->displayName }}
                </span>
            </div>
            <div class="divide-y divide-luxury-alabas/40 bg-white/10">
                @php $subtotal = 0; @endphp
                @foreach($cartItems as $item)
                    @php 
                        $price = ($item->product->status === 'flash-sale' && $item->product->discount_price > 0) ? $item->product->discount_price : $item->product->price;
                        $itemSubtotal = $price * $item->quantity;
                        $subtotal += $itemSubtotal;
                    @endphp
                    <div class="p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-6 hover:bg-white/40 transition-all duration-500 group" :class="{'opacity-60': items['{{ $item->id }}'].isLoading}">
                        <div class="flex items-center gap-4">
                            <img src="{{ $item->product->image }}" class="w-20 h-20 rounded-2xl object-cover border border-white/20 shadow-sm shrink-0">
                            <div class="min-w-0">
                                <h3 class="font-serif text-xl font-bold text-luxury-forest truncate">{{ $item->product->name }}</h3>
                                <p class="text-xs font-semibold text-luxury-slate mt-1">
                                    Rp {{ number_format($price, 0, ',', '.') }} / pcs
                                </p>
                                <p class="text-[10px] font-bold text-[#10B981] mt-0.5" x-text="'Jumlah Stok: ' + (items['{{ $item->id }}'].stock + items['{{ $item->id }}'].quantity) + ' pcs'">
                                    Jumlah Stok: {{ $item->product->stock + $item->quantity }} pcs
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-8">
                            <div class="text-left sm:text-right">
                                <div class="text-xs font-bold text-luxury-slate uppercase tracking-widest mb-1.5">Kuantitas</div>
                                <div class="flex items-center gap-3 bg-white/60 border border-luxury-alabas/80 rounded-xl p-1 shadow-sm relative">
                                    
                                    <!-- Decrement Button -->
                                    <button type="button" 
                                            @click="changeQty('{{ $item->id }}', -1)"
                                            :disabled="items['{{ $item->id }}'].isLoading || items['{{ $item->id }}'].quantity <= 1"
                                            :class="{'opacity-40 cursor-not-allowed': items['{{ $item->id }}'].isLoading || items['{{ $item->id }}'].quantity <= 1}"
                                            class="w-8 h-8 rounded-lg bg-white border border-luxury-alabas flex items-center justify-center text-luxury-forest hover:bg-luxury-gold hover:text-white transition-all duration-300 cursor-pointer active:scale-95">
                                        <i data-lucide="minus" class="w-3.5 h-3.5"></i>
                                    </button>
                                    
                                    <!-- Quantity Input -->
                                    <input type="number" 
                                           id="quantity-input-{{ $item->id }}"
                                           x-model.number="items['{{ $item->id }}'].quantity"
                                           @blur="onInputBlur('{{ $item->id }}')"
                                           @keydown.enter="onInputBlur('{{ $item->id }}')"
                                           :disabled="items['{{ $item->id }}'].isLoading"
                                           min="1" 
                                           max="{{ $item->product->stock + $item->quantity }}"
                                           class="w-12 text-center text-sm font-black text-luxury-forest bg-transparent border-0 focus:ring-0 p-0 focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    
                                    <!-- Increment Button -->
                                    <button type="button" 
                                            @click="changeQty('{{ $item->id }}', 1)"
                                            :disabled="items['{{ $item->id }}'].isLoading || items['{{ $item->id }}'].stock <= 0"
                                            :class="{'opacity-40 cursor-not-allowed': items['{{ $item->id }}'].isLoading || items['{{ $item->id }}'].stock <= 0}"
                                            class="w-8 h-8 rounded-lg bg-white border border-luxury-alabas flex items-center justify-center text-luxury-forest hover:bg-luxury-gold hover:text-white transition-all duration-300 cursor-pointer active:scale-95">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="text-left sm:text-right shrink-0">
                                <div class="text-xs font-bold text-luxury-slate uppercase tracking-widest">Subtotal</div>
                                <div class="text-xl font-serif font-black text-luxury-forest mt-0.5" x-text="'Rp ' + formatNumber(items['{{ $item->id }}'].price * items['{{ $item->id }}'].quantity)">
                                    Rp {{ number_format($itemSubtotal, 0, ',', '.') }}
                                </div>
                            </div>
                            
                            <!-- Delete Button (AJAX-ified) -->
                            <button type="button" 
                                    @click="confirmDelete('{{ $item->id }}')"
                                    :disabled="items['{{ $item->id }}'].isLoading"
                                    class="w-10 h-10 rounded-xl bg-red-50 border border-red-100 flex items-center justify-center text-red-500 hover:bg-red-500 hover:text-white transition-all duration-300 cursor-pointer active:scale-95"
                                    :class="{'opacity-50 cursor-not-allowed': items['{{ $item->id }}'].isLoading}">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Footer Summary -->
            <div class="p-8 bg-white/30 border-t border-luxury-alabas/60 flex flex-col sm:flex-row justify-between items-center gap-6">
                <div>
                    <div class="text-xs font-bold text-luxury-slate uppercase tracking-widest">Total Harga Reservasi</div>
                    <div class="text-3xl font-serif font-black text-luxury-forest mt-1" x-text="'Rp ' + formatNumber(subtotal)">
                        Rp {{ number_format($subtotal, 0, ',', '.') }}
                    </div>
                </div>
                <a href="{{ route('consumer.checkout') }}" class="px-8 py-4 rounded-2xl bg-luxury-forest text-white border border-luxury-forest text-xs font-black uppercase tracking-[0.2em] hover:bg-luxury-gold hover:border-luxury-gold transition-all duration-500 shadow-md shadow-emerald-950/15 flex items-center gap-2 cursor-pointer active:scale-95">
                    Lanjutkan ke Checkout
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    @else
        <!-- Empty Cart State -->
        <div class="glass-card rounded-[2.5rem] p-20 text-center max-w-2xl mx-auto reveal">
            <div class="w-20 h-20 bg-luxury-ivory border border-luxury-alabas/55 rounded-3xl flex items-center justify-center mx-auto mb-6 text-luxury-slate/40 shadow-inner">
                <i data-lucide="shopping-bag" class="w-10 h-10 stroke-[1.5]"></i>
            </div>
            <h3 class="font-serif text-2xl font-bold text-luxury-forest mb-2.5">Keranjang Belanja Kosong</h3>
            <p class="text-sm font-medium text-luxury-slate max-w-sm mx-auto leading-relaxed">
                Anda belum menambahkan makanan surplus untuk direservasi. Silakan cari makanan lezat di sekitar Anda sekarang!
            </p>
            <div class="mt-8">
                <a href="{{ route('consumer.search') }}" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-luxury-forest text-white border border-luxury-forest text-xs font-black uppercase tracking-widest hover:bg-transparent hover:text-luxury-forest transition-all duration-300 shadow-md shadow-emerald-950/10">
                    <i data-lucide="search" class="w-4 h-4 stroke-[2.5]"></i>
                    Cari Makanan Sekarang
                </a>
            </div>
        </div>
    @endif

    <!-- Premium Confirmation Dialog -->
    <div x-show="confirmShow" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" 
         x-cloak>
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-[#0e290b]/60 backdrop-blur-md" @click="confirmShow = false"
             x-show="confirmShow"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <!-- Modal Content -->
        <div x-show="confirmShow"
             x-transition:enter="ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-y-12 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-12 scale-95"
             class="relative bg-white rounded-[2.5rem] shadow-2xl border border-gray-100 p-8 sm:p-10 w-full max-w-md z-50 text-left animate-in fade-in zoom-in duration-300">
            
            <!-- Alert Icon -->
            <div class="w-16 h-16 rounded-full flex items-center justify-center mb-6 bg-red-50 text-red-500 border border-red-100">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8">
                    <circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/>
                </svg>
            </div>

            <h3 class="text-2xl font-black text-gray-900 mb-2" x-text="confirmTitle"></h3>
            <p class="text-gray-550 text-sm font-medium leading-relaxed mb-8" x-text="confirmMsg"></p>

            <div class="flex gap-4">
                <button @click="confirmShow = false" 
                        class="flex-1 py-4 border border-gray-200 hover:bg-gray-50 text-gray-500 rounded-2xl font-black uppercase tracking-wider text-[10px] transition duration-300">
                    Batal
                </button>
                <button @click="executeDelete()" 
                        class="flex-1 py-4 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-black uppercase tracking-wider text-[10px] shadow-lg shadow-red-500/20 transition duration-300">
                    Hapus
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
