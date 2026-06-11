@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="historyPage()">
    <div class="mb-12">
        <h1 class="text-5xl font-serif font-bold text-luxury-forest leading-tight">Riwayat Transaksi</h1>
        <p class="text-luxury-slate font-medium mt-2 tracking-wide font-sans">Kelola dan pantau seluruh transaksi penjualan makanan surplus Anda yang telah selesai maupun dibatalkan.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
        <div class="bg-white p-8 rounded-[2rem] luxury-shadow border border-luxury-alabas hover:bg-luxury-forest transition-all duration-500 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-luxury-ivory rounded-xl flex items-center justify-center group-hover:bg-white/10">
                    <i data-lucide="shopping-bag" class="w-6 h-6 text-luxury-gold"></i>
                </div>
                <div class="text-[10px] font-black text-luxury-gold uppercase tracking-widest">Total Transaksi</div>
            </div>
            <div class="text-4xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">{{ $stats->total_orders }} Pesanan</div>
            <p class="text-[10px] text-luxury-slate group-hover:text-white/60 mt-3 font-bold uppercase tracking-wider italic">Selesai & Batal</p>
        </div>

        <div class="bg-white p-8 rounded-[2rem] luxury-shadow border border-luxury-alabas hover:bg-luxury-forest transition-all duration-500 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-luxury-ivory rounded-xl flex items-center justify-center group-hover:bg-white/10">
                    <i data-lucide="check-circle" class="w-6 h-6 text-luxury-emerald"></i>
                </div>
                <div class="text-[10px] font-black text-luxury-gold uppercase tracking-widest">Selesai</div>
            </div>
            <div class="text-4xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">{{ $stats->completed_orders }} Pesanan</div>
            <p class="text-[10px] text-luxury-slate group-hover:text-white/60 mt-3 font-bold uppercase tracking-wider italic">Berhasil diserahterimakan</p>
        </div>

        <div class="bg-white p-8 rounded-[2rem] luxury-shadow border border-luxury-alabas hover:bg-luxury-forest transition-all duration-500 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-luxury-ivory rounded-xl flex items-center justify-center group-hover:bg-white/10">
                    <i data-lucide="x-circle" class="w-6 h-6 text-red-500"></i>
                </div>
                <div class="text-[10px] font-black text-luxury-gold uppercase tracking-widest">Batal</div>
            </div>
            <div class="text-4xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">{{ $stats->cancelled_orders }} Pesanan</div>
            <p class="text-[10px] text-luxury-slate group-hover:text-white/60 mt-3 font-bold uppercase tracking-wider italic">Dibatalkan mitra/konsumen</p>
        </div>

        <div class="bg-white p-8 rounded-[2rem] luxury-shadow border border-luxury-alabas hover:bg-luxury-forest transition-all duration-500 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-luxury-ivory rounded-xl flex items-center justify-center group-hover:bg-white/10">
                    <i data-lucide="dollar-sign" class="w-6 h-6 text-luxury-gold"></i>
                </div>
                <div class="text-[10px] font-black text-luxury-gold uppercase tracking-widest">Omzet Selesai</div>
            </div>
            <div class="text-3xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">Rp {{ number_format($stats->total_revenue, 0, ',', '.') }}</div>
            <p class="text-[10px] text-luxury-slate group-hover:text-white/60 mt-3 font-bold uppercase tracking-wider italic">Akumulasi pendapatan riwayat</p>
        </div>
    </div>

    <!-- Tabs List -->
    <div class="flex space-x-2 border-b border-luxury-alabas/60 mb-10 bg-white/20 p-2 rounded-2xl">
        <button @click="activeTab = 'completed'"
                :class="{'bg-[#174413] text-white shadow-md': activeTab === 'completed', 'text-gray-500 hover:text-gray-900 hover:bg-white/50': activeTab !== 'completed'}" 
                class="px-6 py-3 font-bold text-xs flex items-center gap-2 border border-transparent rounded-xl transition-all duration-300 uppercase tracking-widest">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            SELESAI (<span x-text="orders.filter(o => o.status === 'completed').length"></span>)
        </button>
        <button @click="activeTab = 'cancelled'"
                :class="{'bg-red-600 text-white shadow-md': activeTab === 'cancelled', 'text-gray-500 hover:text-gray-900 hover:bg-white/50': activeTab !== 'cancelled'}" 
                class="px-6 py-3 font-bold text-xs flex items-center gap-2 border border-transparent rounded-xl transition-all duration-300 uppercase tracking-widest">
            <i data-lucide="x-circle" class="w-4 h-4"></i>
            BATAL (<span x-text="orders.filter(o => o.status === 'cancelled').length"></span>)
        </button>
    </div>

    <!-- Orders History List -->
    <div class="space-y-6">
        <template x-for="order in filteredOrders()" :key="order.id">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-300">
                <div class="p-8 space-y-6">
                    <!-- Order Header -->
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                        <div>
                            <div class="flex items-center gap-3">
                                <h3 class="text-2xl font-black text-gray-900" x-text="'Pesanan ' + order.orderId + ' (#' + order.id + ')'"></h3>
                                <span :class="{
                                    'bg-green-100 text-green-700 border-green-200': order.status === 'completed',
                                    'bg-red-100 text-red-700 border-red-200': order.status === 'cancelled'
                                }" 
                                      class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border"
                                      x-text="order.status === 'completed' ? 'Selesai' : 'Batal'">
                                </span>
                                <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border flex items-center gap-1">
                                    <!-- Delivery (truck) Icon -->
                                    <svg x-show="order.receiving_method === 'delivery'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3 text-gray-500">
                                        <rect x="1" y="3" width="15" height="13" rx="2" ry="2" />
                                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8" />
                                        <circle cx="5.5" cy="18.5" r="2.5" />
                                        <circle cx="18.5" cy="18.5" r="2.5" />
                                    </svg>
                                    <!-- Pickup (store) Icon -->
                                    <svg x-show="order.receiving_method !== 'delivery'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3 text-gray-500">
                                        <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7" />
                                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                                        <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4" />
                                        <path d="M2 7h20" />
                                    </svg>
                                    <span x-text="order.receiving_method === 'delivery' ? 'Delivery' : 'Pickup'"></span>
                                </span>
                                <template x-if="order.status === 'completed' && order.rating > 0">
                                    <div class="flex items-center gap-1 bg-yellow-50 px-2 py-1 rounded-lg border border-yellow-100">
                                        <i data-lucide="star" class="w-3 h-3 text-yellow-500 fill-yellow-500"></i>
                                        <span class="text-xs font-black text-yellow-700" x-text="order.rating"></span>
                                    </div>
                                </template>
                            </div>
                            <p class="text-sm text-gray-400 font-medium mt-2" x-text="'Waktu Pesan: ' + order.orderTime"></p>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-black text-green-600 leading-none" x-text="'Rp ' + parseInt(order.total).toLocaleString('id-ID')"></div>
                        </div>
                    </div>

                    <!-- Info Grid -->
                    <div class="grid md:grid-cols-2 gap-6 border-y border-gray-50 py-6">
                        <div class="space-y-4">
                            <h4 class="text-xs font-black uppercase tracking-widest text-gray-400 flex items-center gap-2">
                                <i data-lucide="user" class="w-3.5 h-3.5"></i> Informasi Pembeli
                            </h4>
                            <div class="space-y-1">
                                <div class="font-bold text-gray-900" x-text="order.customer.name"></div>
                                <div class="text-sm text-gray-600 flex items-center gap-2">
                                    <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                                    <span x-text="order.customer.phone"></span>
                                </div>
                                <div class="text-sm text-gray-600" x-text="order.customer.email"></div>
                            </div>
                        </div>

                        <!-- Right Column: Pickup (with Code) -->
                        <div class="space-y-4" x-show="order.receiving_method !== 'delivery'">
                            <h4 class="text-xs font-black uppercase tracking-widest text-gray-400 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-gray-400">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                                </svg>
                                Kode Pengambilan
                            </h4>
                            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                <div class="text-3xl font-black text-center text-gray-900 tracking-widest" x-text="order.pickupCode"></div>
                                <div class="text-[10px] text-center text-gray-400 font-bold uppercase mt-2" x-text="'Jadwal Ambil: ' + order.pickupTime"></div>
                            </div>
                        </div>

                        <!-- Right Column: Delivery (with Address & Time slot) -->
                        <div class="space-y-4" x-show="order.receiving_method === 'delivery'">
                            <h4 class="text-xs font-black uppercase tracking-widest text-gray-400 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-gray-400">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                                </svg>
                                Lokasi Pengantaran
                            </h4>
                            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                <div class="text-sm font-bold text-gray-900 leading-relaxed" x-text="order.customer && order.customer.profile && order.customer.profile.address ? order.customer.profile.address : (order.customer ? (order.customer.address || 'Alamat tidak ditentukan') : '-')"></div>
                                <div class="text-[10px] text-gray-400 font-black uppercase tracking-wider mt-2.5" x-text="'Jadwal Kirim: ' + (order.delivery_time_slot || '-')"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-black uppercase tracking-widest text-gray-400 flex items-center gap-2">
                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i> Detail Pesanan
                        </h4>
                        <div class="space-y-3">
                            <template x-for="item in order.items" :key="item.id">
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white border border-gray-100 rounded-xl flex items-center justify-center text-gray-500 font-bold overflow-hidden shadow-sm">
                                            <template x-if="item.product && item.product.image">
                                                <img :src="item.product.image.startsWith('http') ? item.product.image : '/storage/' + item.product.image" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!item.product || !item.product.image">
                                                <i data-lucide="package" class="w-5 h-5 text-gray-400"></i>
                                            </template>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900" x-text="item.product ? item.product.name : 'Produk Tidak Ditemukan'"></div>
                                            <div class="text-xs text-gray-400 mt-1" x-text="'Harga Satuan: Rp ' + parseInt(item.price).toLocaleString('id-ID')"></div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-black text-gray-900" x-text="item.quantity + ' pcs'"></div>
                                        <div class="text-xs text-gray-400 mt-1" x-text="'Total: Rp ' + (parseInt(item.price) * item.quantity).toLocaleString('id-ID')"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Review Section (If Completed and has review) -->
                    <template x-if="order.status === 'completed' && order.review_relation">
                        <div class="p-6 bg-yellow-50/50 border border-yellow-100 rounded-2xl space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-black uppercase tracking-wider text-yellow-800 flex items-center gap-1.5">
                                    <i data-lucide="star" class="w-4 h-4 text-yellow-500 fill-yellow-500"></i> Ulasan Pembeli
                                </div>
                                <span class="text-[10px] text-yellow-600 font-bold" x-text="order.review_relation.created_at"></span>
                            </div>
                            <p class="text-sm text-yellow-900 font-serif italic" x-text="'&ldquo;' + order.review_relation.comment + '&rdquo;'"></p>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- Empty State -->
        <div x-show="filteredOrders().length === 0" class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
            <div class="w-20 h-20 bg-luxury-ivory rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="inbox" class="w-8 h-8 text-luxury-alabas"></i>
            </div>
            <h3 class="text-xl font-serif font-bold text-gray-900 mb-2">Belum Ada Transaksi</h3>
            <p class="text-gray-500 max-w-sm mx-auto text-sm font-sans" x-text="activeTab === 'completed' ? 'Transaksi selesai Anda akan muncul di sini.' : 'Transaksi dibatalkan akan muncul di sini.'"></p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('historyPage', () => ({
            activeTab: 'completed',
            orders: @json($orders).map(order => {
                // Map helper strings for JS
                let orderTime = new Date(order.created_at).toLocaleString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                let pickupTime = order.pickup_time ? new Date(order.pickup_time).toLocaleString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit'
                }) : '-';
                
                return {
                    id: order.id,
                    orderId: order.orderId,
                    status: order.status,
                    receiving_method: order.receiving_method,
                    pickupCode: order.pickup_code || '-',
                    pickupTime: pickupTime,
                    delivery_time_slot: order.delivery_time_slot,
                    total: order.total_amount,
                    orderTime: orderTime,
                    customer: order.customer,
                    items: order.items,
                    rating: order.rating || 0,
                    review_relation: order.review_relation
                };
            }),

            filteredOrders() {
                return this.orders.filter(o => o.status === this.activeTab);
            },

            init() {
                this.$watch('activeTab', () => {
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                });
                
                this.$nextTick(() => {
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                });
            }
        }));
    });
</script>
@endsection
