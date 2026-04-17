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
    <div class="flex gap-2 p-1 bg-gray-100 rounded-2xl w-fit">
        <button @click="activeTab = 'pending'" 
                :class="activeTab === 'pending' ? 'bg-white text-[#174413] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="px-6 py-2.5 rounded-xl font-bold text-sm transition flex items-center gap-2">
            <i data-lucide="clock" class="w-4 h-4"></i>
            Menunggu (<span x-text="orders.filter(o => o.status === 'pending').length"></span>)
        </button>
        <button @click="activeTab = 'completed'" 
                :class="activeTab === 'completed' ? 'bg-white text-[#174413] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="px-6 py-2.5 rounded-xl font-bold text-sm transition flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            Selesai (<span x-text="orders.filter(o => o.status === 'completed').length"></span>)
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
                                <span :class="order.status === 'completed' ? 'bg-green-100 text-green-700 border-green-200' : 'bg-orange-100 text-orange-700 border-orange-200'" 
                                      class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border"
                                      x-text="order.status === 'completed' ? 'Selesai' : 'Menunggu Diambil'">
                                </span>
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
                    <div class="pt-6 border-t border-gray-50">
                        <template x-if="order.status === 'pending'">
                            <button @click="confirmPickup(order.id)" class="w-full bg-[#174413] text-white py-4 rounded-2xl font-black shadow-xl shadow-green-100 hover:bg-[#256020] transition flex items-center justify-center gap-3">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                                Konfirmasi Sudah Diambil
                            </button>
                        </template>
                        <template x-if="order.status === 'completed'">
                            <div class="text-center text-green-600 font-bold text-sm flex items-center justify-center gap-2">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                Selesai pada <span x-text="order.completedTime"></span>
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
            
            confirmPickup(id) {
                if(confirm('Konfirmasi bahwa pesanan ini sudah diambil?')) {
                    const order = this.orders.find(o => o.id === id);
                    if (order) {
                        order.status = 'completed';
                        order.completedTime = new Date().toLocaleString('id-ID', { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' });
                        alert('Pesanan dikonfirmasi sebagai sudah diambil!');
                        setTimeout(() => lucide.createIcons(), 50);
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
