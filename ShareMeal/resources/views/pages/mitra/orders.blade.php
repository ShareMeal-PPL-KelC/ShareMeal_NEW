@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="ordersData()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Daftar Pesanan Masuk</h1>
            <p class="text-gray-600 mt-1">Kelola pesanan booking pengambilan makanan dari konsumen</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex flex-wrap gap-2 p-1 bg-gray-100 rounded-2xl w-fit">
        <button @click="activeTab = 'pending'" 
                :class="activeTab === 'pending' ? 'bg-white text-[#174413] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="px-6 py-2.5 rounded-xl font-bold text-sm transition flex items-center gap-2">
            <i data-lucide="clock" class="w-4 h-4"></i>
            Menunggu (<span x-text="orders.filter(o => o.status === 'pending').length"></span>)
        </button>
        <button @click="activeTab = 'ready'" 
                :class="activeTab === 'ready' ? 'bg-white text-[#174413] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="px-6 py-2.5 rounded-xl font-bold text-sm transition flex items-center gap-2">
            <i data-lucide="package" class="w-4 h-4"></i>
            Siap (<span x-text="orders.filter(o => o.status === 'ready').length"></span>)
        </button>
        <button @click="activeTab = 'shipping'" 
                :class="activeTab === 'shipping' ? 'bg-white text-[#174413] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="px-6 py-2.5 rounded-xl font-bold text-sm transition flex items-center gap-2">
            <i data-lucide="truck" class="w-4 h-4"></i>
            Dikirim (<span x-text="orders.filter(o => o.status === 'shipping').length"></span>)
        </button>
        <button @click="activeTab = 'completed'" 
                :class="activeTab === 'completed' ? 'bg-white text-[#174413] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="px-6 py-2.5 rounded-xl font-bold text-sm transition flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            Selesai (<span x-text="orders.filter(o => o.status === 'completed').length"></span>)
        </button>
        <button @click="activeTab = 'cancelled'" 
                :class="activeTab === 'cancelled' ? 'bg-white text-[#174413] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="px-6 py-2.5 rounded-xl font-bold text-sm transition flex items-center gap-2">
            <i data-lucide="x-circle" class="w-4 h-4"></i>
            Batal (<span x-text="orders.filter(o => o.status === 'cancelled').length"></span>)
        </button>
    </div>

    <!-- Orders List -->
    <div class="space-y-6">
        <template x-for="order in orders" :key="order.id">
            <div x-show="order.status === activeTab" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-300">
                <div class="p-8 space-y-6">
                    <!-- Order Header -->
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                        <div>
                            <div class="flex items-center gap-3">
                                <h3 class="text-2xl font-black text-gray-900" x-text="'Pesanan #' + order.id"></h3>
                                <span :class="{
                                    'bg-orange-100 text-orange-700 border-orange-200': order.status === 'pending',
                                    'bg-blue-100 text-blue-700 border-blue-200': order.status === 'ready',
                                    'bg-indigo-100 text-indigo-700 border-indigo-200': order.status === 'shipping',
                                    'bg-green-100 text-green-700 border-green-200': order.status === 'completed',
                                    'bg-red-100 text-red-700 border-red-200': order.status === 'cancelled'
                                }" 
                                      class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border"
                                      x-text="getStatusLabel(order.status)">
                                </span>
                                <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border flex items-center gap-1">
                                    <i :data-lucide="order.receiving_method === 'delivery' ? 'truck' : 'store'" class="w-3 h-3"></i>
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
                        <div class="space-y-4">
                            <h4 class="text-xs font-black uppercase tracking-widest text-gray-400 flex items-center gap-2">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Kode Pengambilan
                            </h4>
                            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                <div class="text-3xl font-black text-center text-gray-900 tracking-widest" x-text="order.pickupCode"></div>
                                <div class="text-[10px] text-center text-gray-400 font-bold uppercase mt-2" x-text="'Jadwal: ' + order.pickupTime"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-black uppercase tracking-widest text-gray-400">Item Pesanan</h4>
                        <div class="space-y-2">
                            <template x-for="item in order.items">
                                <div class="flex items-center justify-between text-sm font-medium">
                                    <div class="text-gray-700">
                                        <span class="text-gray-900 font-bold" x-text="item.name"></span>
                                        <span class="text-gray-400 ml-1" x-text="'× ' + item.quantity"></span>
                                    </div>
                                    <div class="text-gray-900 font-black" x-text="'Rp ' + (item.price * item.quantity).toLocaleString('id-ID')"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Action -->
                    <div class="pt-6 border-t border-gray-50 flex flex-wrap gap-3">
                        <template x-if="order.status === 'pending'">
                            <div class="flex flex-1 gap-3">
                                <button @click="updateStatus(order.id, 'ready')" class="flex-1 bg-blue-600 text-white py-4 rounded-2xl font-black shadow-xl shadow-blue-100 hover:bg-blue-700 transition flex items-center justify-center gap-3">
                                    <i data-lucide="package" class="w-5 h-5"></i>
                                    Pesanan Siap
                                </button>
                                <button @click="updateStatus(order.id, 'cancelled')" class="px-6 bg-red-50 text-red-600 rounded-2xl font-bold hover:bg-red-100 transition">
                                    Batalkan
                                </button>
                            </div>
                        </template>
                        
                        <template x-if="order.status === 'ready'">
                            <div class="flex flex-1 gap-3">
                                <button x-show="order.receiving_method === 'delivery'" @click="updateStatus(order.id, 'shipping')" class="flex-1 bg-indigo-600 text-white py-4 rounded-2xl font-black shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition flex items-center justify-center gap-3">
                                    <i data-lucide="truck" class="w-5 h-5"></i>
                                    Kirim Sekarang
                                </button>
                                <button x-show="order.receiving_method === 'pickup'" @click="updateStatus(order.id, 'completed')" class="flex-1 bg-[#174413] text-white py-4 rounded-2xl font-black shadow-xl shadow-green-100 hover:bg-[#256020] transition flex items-center justify-center gap-3">
                                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                                    Konfirmasi Diambil
                                </button>
                                <button @click="updateStatus(order.id, 'cancelled')" class="px-6 bg-red-50 text-red-600 rounded-2xl font-bold hover:bg-red-100 transition">
                                    Batalkan
                                </button>
                            </div>
                        </template>

                        <template x-if="order.status === 'shipping'">
                            <button @click="updateStatus(order.id, 'completed')" class="w-full bg-[#174413] text-white py-4 rounded-2xl font-black shadow-xl shadow-green-100 hover:bg-[#256020] transition flex items-center justify-center gap-3">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                                Konfirmasi Sampai & Selesai
                            </button>
                        </template>

                        <template x-if="order.status === 'completed'">
                            <div class="w-full text-center text-green-600 font-bold text-sm flex items-center justify-center gap-2">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                Pesanan Selesai pada <span x-text="order.completedTime"></span>
                            </div>
                        </template>

                        <template x-if="order.status === 'cancelled'">
                            <div class="w-full text-center text-red-600 font-bold text-sm flex items-center justify-center gap-2">
                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                                Pesanan Dibatalkan
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <!-- Empty State -->
        <div x-show="orders.filter(o => o.status === activeTab).length === 0" class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
            <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                <i :data-lucide="activeTab === 'pending' ? 'clock' : 'check-circle'" class="w-10 h-10 text-gray-300"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900 mb-2" x-text="activeTab === 'pending' ? 'Tidak Ada Pesanan Menunggu' : 'Belum Ada Pesanan Selesai'"></h3>
            <p class="text-gray-500 font-medium">Data pesanan akan muncul secara otomatis di sini.</p>
        </div>
    </div>
</div>

<script>
    function ordersData() {
        return {
            activeTab: 'pending',
            orders: @json($orders),
            
            getStatusLabel(status) {
                const labels = {
                    'pending': 'Menunggu',
                    'ready': 'Siap Diambil/Kirim',
                    'shipping': 'Dalam Perjalanan',
                    'completed': 'Selesai',
                    'cancelled': 'Dibatalkan'
                };
                return labels[status] || status;
            },

            async updateStatus(id, newStatus) {
                const confirmMsg = newStatus === 'cancelled' 
                    ? 'Apakah Anda yakin ingin membatalkan pesanan ini?' 
                    : `Ubah status pesanan ke "${this.getStatusLabel(newStatus)}"?`;

                if(confirm(confirmMsg)) {
                    try {
                        const response = await fetch(`/mitra/orders/${id}/update-status`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ status: newStatus })
                        });

                        if (response.ok) {
                            const data = await response.json();
                            const order = this.orders.find(o => o.id === id);
                            if (order) {
                                order.status = newStatus;
                                if (data.completed_time) {
                                    order.completedTime = data.completed_time;
                                }
                                alert('Status pesanan berhasil diperbarui!');
                                this.activeTab = newStatus;
                                setTimeout(() => lucide.createIcons(), 50);
                            }
                        } else {
                            alert('Gagal memperbarui status. Silakan coba lagi.');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan koneksi.');
                    }
                }
            },
            
            init() {
                this.$watch('activeTab', () => {
                    setTimeout(() => lucide.createIcons(), 50);
                });
            }
        }
    }
</script>
@endsection
