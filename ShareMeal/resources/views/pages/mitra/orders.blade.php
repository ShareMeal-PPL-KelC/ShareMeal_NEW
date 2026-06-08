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
        <button @click="activeTab = 'processing'" 
                :class="activeTab === 'processing' ? 'bg-white text-[#174413] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="px-6 py-2.5 rounded-xl font-bold text-sm transition flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
            </svg>
            Diproses (<span x-text="orders.filter(o => o.status === 'processing').length"></span>)
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
                                <h3 class="text-2xl font-black text-gray-900" x-text="'Pesanan ' + order.orderId + ' (#' + order.id + ')'"></h3>
                                <span :class="{
                                    'bg-orange-100 text-orange-700 border-orange-200': order.status === 'pending',
                                    'bg-amber-100 text-amber-700 border-amber-200': order.status === 'processing',
                                    'bg-blue-100 text-blue-700 border-blue-200': order.status === 'ready',
                                    'bg-indigo-100 text-indigo-700 border-indigo-200': order.status === 'shipping',
                                    'bg-green-100 text-green-700 border-green-200': order.status === 'completed',
                                    'bg-red-100 text-red-700 border-red-200': order.status === 'cancelled'
                                }" 
                                      class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border"
                                      x-text="getStatusLabel(order)">
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
                                <button @click="updateStatus(order.id, 'processing')" class="flex-1 bg-amber-600 text-white py-4 rounded-2xl font-black shadow-xl shadow-amber-100 hover:bg-amber-700 transition flex items-center justify-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                        <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                                    </svg>
                                    Terima & Proses Pesanan
                                </button>
                                <button @click="updateStatus(order.id, 'cancelled')" class="px-6 bg-red-50 text-red-600 rounded-2xl font-bold hover:bg-red-100 transition">
                                    Batalkan
                                </button>
                            </div>
                        </template>

                        <template x-if="order.status === 'processing'">
                            <div class="flex flex-1 gap-3">
                                <button @click="updateStatus(order.id, 'ready')" class="flex-1 bg-blue-600 text-white py-4 rounded-2xl font-black shadow-xl shadow-blue-100 hover:bg-blue-700 transition flex items-center justify-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                        <path d="M21 16V8a2 2 0 0 0-2-2h-5l-4-4H3a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2z"/>
                                    </svg>
                                    <span x-text="order.receiving_method === 'delivery' ? 'Pesanan Siap' : 'Pesanan Siap Diambil'"></span>
                                </button>
                                <button @click="updateStatus(order.id, 'cancelled')" class="px-6 bg-red-50 text-red-600 rounded-2xl font-bold hover:bg-red-100 transition">
                                    Batalkan
                                </button>
                            </div>
                        </template>
                        
                        <template x-if="order.status === 'ready'">
                            <div class="flex flex-1 gap-3">
                                <button x-show="order.receiving_method === 'delivery'" @click="updateStatus(order.id, 'shipping')" class="flex-1 bg-indigo-600 text-white py-4 rounded-2xl font-black shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition flex items-center justify-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                        <rect x="1" y="3" width="15" height="13" rx="2" ry="2" />
                                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8" />
                                        <circle cx="5.5" cy="18.5" r="2.5" />
                                        <circle cx="18.5" cy="18.5" r="2.5" />
                                    </svg>
                                    Kirim Sekarang (Dalam Perjalanan)
                                </button>
                                <button x-show="order.receiving_method !== 'delivery'" @click="updateStatus(order.id, 'completed')" class="flex-1 bg-[#174413] text-white py-4 rounded-2xl font-black shadow-xl shadow-green-100 hover:bg-[#256020] transition flex items-center justify-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                        <polyline points="22 4 12 14.01 9 11.01" />
                                    </svg>
                                    Konfirmasi Diambil (Selesai)
                                </button>
                            </div>
                        </template>

                        <template x-if="order.status === 'shipping'">
                            <button @click="updateStatus(order.id, 'completed')" class="w-full bg-[#174413] text-white py-4 rounded-2xl font-black shadow-xl shadow-green-100 hover:bg-[#256020] transition flex items-center justify-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                    <polyline points="22 4 12 14.01 9 11.01" />
                                </svg>
                                Konfirmasi Sampai & Selesai
                            </button>
                        </template>

                        <template x-if="order.status === 'completed'">
                            <div class="w-full text-center text-green-600 font-bold text-sm flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                    <polyline points="22 4 12 14.01 9 11.01" />
                                </svg>
                                Pesanan Selesai pada <span x-text="order.completedTime"></span>
                            </div>
                        </template>

                        <template x-if="order.status === 'cancelled'">
                            <div class="w-full text-center text-red-600 font-bold text-sm flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                    <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                                </svg>
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
                <!-- Clock Icon for Pending -->
                <svg x-show="activeTab === 'pending'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 text-gray-300">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
                <!-- Processing Icon for Processing -->
                <svg x-show="activeTab === 'processing'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 text-gray-300">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                </svg>
                <!-- Check Circle Icon for others -->
                <svg x-show="activeTab !== 'pending' && activeTab !== 'processing'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 text-gray-300">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
            </div>
            <h3 class="text-xl font-black text-gray-900 mb-2" 
                x-text="activeTab === 'pending' ? 'Tidak Ada Pesanan Menunggu' : 
                        (activeTab === 'processing' ? 'Tidak Ada Pesanan Sedang Dibuat' : 
                        (activeTab === 'completed' ? 'Belum Ada Pesanan Selesai' : 'Tidak Ada Data Pesanan'))"></h3>
            <p class="text-gray-500 font-medium">Data pesanan akan muncul secara otomatis di sini.</p>
        </div>
    </div>
    <!-- Beautiful Confirmation Dialog -->
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
             class="relative bg-white rounded-[2.5rem] shadow-2xl border border-gray-100 p-8 sm:p-10 w-full max-w-md z-50 text-center">
            
            <!-- Warning / Alert Icon -->
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 bg-amber-50 text-amber-500 border border-amber-100">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8">
                    <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
                </svg>
            </div>

            <h3 class="text-2xl font-black text-gray-900 mb-3" x-text="confirmTitle"></h3>
            <p class="text-gray-550 text-sm font-medium leading-relaxed mb-8" x-text="confirmMsg"></p>

            <div class="flex gap-4">
                <button @click="confirmShow = false" 
                        class="flex-1 py-4 border border-gray-200 hover:bg-gray-50 text-gray-500 rounded-2xl font-black uppercase tracking-wider text-[10px] transition duration-300">
                    Batal
                </button>
                <button @click="executeConfirm()" 
                        class="flex-1 py-4 bg-[#174413] hover:bg-[#256020] text-white rounded-2xl font-black uppercase tracking-wider text-[10px] shadow-lg shadow-green-100 transition duration-300">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>

    <!-- Cancellation Dialog with Reason Form -->
    <div x-show="cancelShow" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" 
         x-cloak>
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-[#0e290b]/60 backdrop-blur-md" @click="cancelShow = false"
             x-show="cancelShow"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <!-- Modal Content -->
        <div x-show="cancelShow"
             x-transition:enter="ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-y-12 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-12 scale-95"
             class="relative bg-white rounded-[2.5rem] shadow-2xl border border-gray-100 p-8 sm:p-10 w-full max-w-md z-50 text-left animate-in fade-in zoom-in duration-300">
            
            <!-- Alert Icon -->
            <div class="w-16 h-16 rounded-full flex items-center justify-center mb-6 bg-red-50 text-red-500 border border-red-100">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8">
                    <circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/>
                </svg>
            </div>

            <h3 class="text-2xl font-black text-gray-900 mb-2">Batalkan Pesanan?</h3>
            <p class="text-gray-500 text-xs font-semibold leading-relaxed mb-6">Mohon berikan alasan pembatalan untuk pesanan ini. Informasi ini akan langsung dikirimkan ke pelanggan.</p>

            <div class="space-y-4 mb-8">
                <div>
                    <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-2">Alasan Pembatalan</label>
                    <textarea x-model="cancelReason" 
                              rows="3" 
                              placeholder="Contoh: Stok makanan habis, toko tutup lebih awal, dll."
                              class="w-full bg-gray-50/55 border border-gray-200 rounded-2xl p-4 text-xs font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-550/20 focus:border-red-500 transition-all placeholder:text-gray-300 resize-none"></textarea>
                </div>
            </div>

            <div class="flex gap-4">
                <button @click="cancelShow = false" 
                        class="flex-1 py-4 border border-gray-200 hover:bg-gray-50 text-gray-500 rounded-2xl font-black uppercase tracking-wider text-[10px] transition duration-300">
                    Kembali
                </button>
                <button @click="executeCancel()" 
                        class="flex-1 py-4 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-black uppercase tracking-wider text-[10px] shadow-lg shadow-red-500/20 transition duration-300">
                    Batalkan Pesanan
                </button>
            </div>
        </div>
    </div>

    <!-- Premium Toast Notification -->
    <div class="fixed top-6 right-6 z-[120] pointer-events-none" x-cloak>
        <div x-show="toastShow"
             x-transition:enter="transform ease-out duration-500 transition-all"
             x-transition:enter-start="translate-y-[-2rem] opacity-0 scale-95"
             x-transition:enter-end="translate-y-0 opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="flex items-center gap-3.5 px-6 py-4 rounded-2xl shadow-2xl border pointer-events-auto max-w-sm"
             :class="{
                 'bg-emerald-50 border-emerald-100 text-emerald-800 shadow-emerald-100/30': toastType === 'success',
                 'bg-red-50 border-red-100 text-red-800 shadow-red-100/30': toastType === 'error',
                 'bg-blue-50 border-blue-100 text-blue-800 shadow-blue-100/30': toastType === 'info'
             }">
            
            <!-- Icon -->
            <div class="flex-shrink-0">
                <!-- Success Checked Icon -->
                <svg x-show="toastType === 'success'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-emerald-600">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <!-- Error X Icon -->
                <svg x-show="toastType === 'error'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-red-650">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                <!-- Info Icon -->
                <svg x-show="toastType === 'info'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-blue-650">
                    <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="16" y2="12"/><line x1="12" x2="12.01" y1="8" y2="8"/>
                </svg>
            </div>
            
            <!-- Message -->
            <div class="text-xs font-black uppercase tracking-wider" x-text="toastMsg"></div>
        </div>
    </div>
</div>

<script>
    function ordersData() {
        return {
            activeTab: 'pending',
            orders: @json($orders),
            
            // Custom dialog and notification states
            toastShow: false,
            toastMsg: '',
            toastType: 'success', // 'success', 'error', 'info'
            
            confirmShow: false,
            confirmTitle: '',
            confirmMsg: '',
            confirmCallback: null,
            
            cancelShow: false,
            cancelOrderId: null,
            cancelReason: '',
            
            showToast(message, type = 'success') {
                this.toastMsg = message;
                this.toastType = type;
                this.toastShow = true;
                setTimeout(() => {
                    this.toastShow = false;
                }, 3000);
            },
            
            triggerConfirm(title, message, callback) {
                this.confirmTitle = title;
                this.confirmMsg = message;
                this.confirmCallback = callback;
                this.confirmShow = true;
            },
            
            executeConfirm() {
                this.confirmShow = false;
                if (this.confirmCallback) {
                    this.confirmCallback();
                }
            },
            
            getStatusLabel(order) {
                if (order.status === 'ready') {
                    return order.receiving_method === 'delivery' ? 'Siap Diantar' : 'Siap Diambil';
                }
                const labels = {
                    'pending': 'Menunggu Konfirmasi',
                    'processing': 'Sedang Dibuat',
                    'shipping': 'Sedang Dikirim',
                    'completed': 'Selesai',
                    'cancelled': 'Dibatalkan'
                };
                return labels[order.status] || order.status;
            },

            updateStatus(id, newStatus) {
                const order = this.orders.find(o => o.id === id);
                if (!order) return;

                if (newStatus === 'cancelled') {
                    this.cancelOrderId = id;
                    this.cancelReason = '';
                    this.cancelShow = true;
                    return;
                }

                const title = 'Konfirmasi Status';
                const mockOrder = { status: newStatus, receiving_method: order.receiving_method };
                const label = this.getStatusLabel(mockOrder);
                const message = `Apakah Anda yakin ingin mengubah status pesanan ini menjadi "${label}"?`;

                this.triggerConfirm(title, message, () => {
                    this.ordersUpdateAction(id, newStatus, null);
                });
            },

            executeCancel() {
                const id = this.cancelOrderId;
                const reason = this.cancelReason.trim();
                if (reason === "") {
                    alert("Alasan pembatalan tidak boleh kosong!");
                    return;
                }

                this.cancelShow = false;
                this.ordersUpdateAction(id, 'cancelled', reason);
            },

            async ordersUpdateAction(id, newStatus, reason) {
                try {
                    const response = await fetch(`/mitra/orders/${id}/update-status`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ 
                            status: newStatus,
                            cancel_reason: reason
                        })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        const order = this.orders.find(o => o.id === id);
                        if (order) {
                            order.status = newStatus;
                            if (reason) {
                                order.cancel_reason = reason;
                            }
                            if (data.completed_time) {
                                order.completedTime = data.completed_time;
                            }
                            this.showToast('Status pesanan berhasil diperbarui!', 'success');
                            this.activeTab = newStatus;
                            setTimeout(() => lucide.createIcons(), 50);
                        }
                    } else {
                        this.showToast('Gagal memperbarui status. Silakan coba lagi.', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showToast('Terjadi kesalahan koneksi.', 'error');
                }
            },
            
            init() {
                setTimeout(() => lucide.createIcons(), 50);
                this.$watch('activeTab', () => {
                    setTimeout(() => lucide.createIcons(), 50);
                });
            }
        }
    }
</script>
@endsection
