@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="donationsPage()">
    <div class="mb-12 reveal">
        <h1 class="text-5xl font-serif font-bold text-luxury-forest leading-tight">Kelola Penerimaan Donasi</h1>
        <p class="text-luxury-slate font-medium mt-2 tracking-wide">Pantau secara real-time dan klaim donasi makanan surplus berkualitas untuk disalurkan.</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-750 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm animate-in fade-in duration-350">
        <i data-lucide="check-circle" class="w-5 h-5"></i>
        <span class="font-bold text-sm">{{ session('success') }}</span>
    </div>
    @endif
    
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-750 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm animate-in fade-in duration-350">
        <i data-lucide="alert-circle" class="w-5 h-5"></i>
        <span class="font-bold text-sm">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Info Banner -->
    <div class="bg-purple-55 border border-purple-100 rounded-[2rem] p-6 mb-10 shadow-sm animate-in fade-in duration-300">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600 flex-shrink-0">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
            <div class="text-sm text-purple-900 leading-relaxed">
                <strong>Prinsip First-Come, First-Served:</strong> Donasi surplus tersedia bersifat terbuka untuk diklaim secara adil oleh seluruh lembaga terverifikasi di platform. Harap pastikan kapasitas penyimpanan dan logistik armada Anda memadai sebelum melakukan klaim donasi.
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <!-- Tabs List -->
        <div class="flex space-x-2 border-b border-luxury-alabas/60 mb-10 bg-white/20 p-2 rounded-2xl">
            <button @click="activeTab = 'available'"
                    :class="{'bg-green-55 text-green-700 border-green-200 shadow-sm': activeTab === 'available', 'text-gray-500 hover:text-gray-900 hover:bg-white/50': activeTab !== 'available'}" 
                    class="px-6 py-3 font-bold text-xs flex items-center gap-2 border border-transparent rounded-xl transition-all duration-300 uppercase tracking-widest">
                <i data-lucide="package" class="w-4 h-4"></i>
                Tersedia (<span x-text="availableDonations().length"></span>)
            </button>
            <button @click="activeTab = 'claimed'"
                    :class="{'bg-purple-55 text-purple-700 border-purple-200 shadow-sm': activeTab === 'claimed', 'text-gray-500 hover:text-gray-900 hover:bg-white/50': activeTab !== 'claimed'}" 
                    class="px-6 py-3 font-bold text-xs flex items-center gap-2 border border-transparent rounded-xl transition-all duration-300 uppercase tracking-widest">
                <i data-lucide="truck" class="w-4 h-4"></i>
                Diproses (<span x-text="claimedDonations().length"></span>)
            </button>
            <button @click="activeTab = 'completed'"
                    :class="{'bg-indigo-50 text-indigo-700 border-indigo-200 shadow-sm': activeTab === 'completed', 'text-gray-500 hover:text-gray-900 hover:bg-white/50': activeTab !== 'completed'}" 
                    class="px-6 py-3 font-bold text-xs flex items-center gap-2 border border-transparent rounded-xl transition-all duration-300 uppercase tracking-widest">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                Riwayat (<span x-text="completedDonations().length"></span>)
            </button>
        </div>

        <!-- Available Tab Content -->
        <div x-show="activeTab === 'available'" class="space-y-6">
            <template x-if="availableDonations().length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 animate-in fade-in duration-500">
                    <template x-for="donation in availableDonations()" :key="donation.id">
                        <div class="glass-card glass-card-hover rounded-[2.5rem] overflow-hidden transition-all duration-500 flex flex-col justify-between group">
                            <div class="p-8 flex-1 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3">
                                                <h3 class="font-serif text-2xl font-bold text-luxury-forest group-hover:text-luxury-gold transition-colors" x-text="donation.store.name"></h3>
                                                <span class="inline-flex items-center gap-1 rounded-full bg-green-50 border border-green-200 px-3 py-1 text-[10px] font-black text-green-700 uppercase tracking-wider">
                                                    <i data-lucide="package" class="w-3 h-3"></i> Tersedia
                                                </span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-3 mt-3 text-[10px] font-bold text-luxury-slate uppercase tracking-wider">
                                                <span class="flex items-center gap-1">📍 <span x-text="donation.store.address"></span></span>
                                                <span>• <span x-text="donation.distance"></span></span>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-mono font-black text-luxury-slate tracking-widest" x-text="'#' + donation.id"></span>
                                    </div>

                                    <div class="border-t border-luxury-alabas/50 mt-6 pt-6">
                                        <h4 class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.25em] mb-3">Item Donasi</h4>
                                        <div class="space-y-2">
                                            <template x-for="(item, index) in donation.items" :key="index">
                                                <div class="flex items-center justify-between text-xs bg-white/40 p-4 rounded-xl border border-luxury-alabas hover:bg-white/80 hover:shadow-sm transition-all duration-300">
                                                    <span class="text-luxury-forest font-bold" x-text="item.name"></span>
                                                    <span class="font-black text-luxury-gold uppercase tracking-wider" x-text="item.quantity + ' ' + (item.unit || 'unit')"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div class="border-t border-luxury-alabas/50 mt-6 pt-6 flex items-center gap-2 text-xs font-black text-orange-655 uppercase tracking-wider">
                                    <i data-lucide="clock" class="w-4 h-4 animate-pulse"></i>
                                    <span>Tersedia sampai: <span x-text="donation.available_until"></span></span>
                                </div>
                                
                                <button @click="openClaimModal(donation)" class="w-full mt-6 bg-purple-600 text-white py-4 px-6 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] shadow-xl hover:bg-purple-750 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2">
                                    <i data-lucide="heart" class="w-4 h-4 text-white animate-pulse"></i>
                                    Klaim Donasi
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="availableDonations().length === 0">
                <div class="bg-white/40 rounded-[2.5rem] border border-dashed border-gray-200 p-16 text-center shadow-sm">
                    <i data-lucide="package-open" class="w-16 h-16 text-gray-300 mx-auto mb-4 animate-bounce"></i>
                    <h3 class="text-xl font-serif font-bold text-gray-900 mb-2">Tidak Ada Donasi Tersedia</h3>
                    <p class="text-gray-500 max-w-sm mx-auto text-sm">Donasi surplus baru akan muncul di sini segera setelah mitra menyediakannya.</p>
                </div>
            </template>
        </div>

        <!-- Claimed Tab Content -->
        <div x-show="activeTab === 'claimed'" class="space-y-6" x-cloak>
            <template x-if="claimedDonations().length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 animate-in fade-in duration-500">
                    <template x-for="donation in claimedDonations()" :key="donation.id">
                        <div class="glass-card rounded-[2.5rem] overflow-hidden transition-all duration-500 flex flex-col justify-between group">
                            <div class="p-8 flex-1 flex flex-col justify-between bg-white/10">
                                <div>
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3">
                                                <h3 class="font-serif text-2xl font-bold text-luxury-forest group-hover:text-luxury-gold transition-colors" x-text="donation.store.name"></h3>
                                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 border border-blue-200 px-3 py-1 text-[10px] font-black text-blue-700 uppercase tracking-wider">
                                                    <i data-lucide="truck" class="w-3 h-3"></i> Diproses
                                                </span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-3 mt-3 text-[10px] font-bold text-luxury-slate uppercase tracking-wider">
                                                <span class="flex items-center gap-1">📍 <span x-text="donation.store.address"></span></span>
                                                <span>• <span x-text="donation.distance"></span></span>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-mono font-black text-luxury-slate tracking-widest" x-text="'#' + donation.id"></span>
                                    </div>

                                    <div class="border-t border-luxury-alabas/50 mt-6 pt-6">
                                        <h4 class="text-[10px] font-black text-luxury-gold uppercase tracking-[0.25em] mb-3">Item Donasi</h4>
                                        <div class="space-y-2">
                                            <template x-for="(item, index) in donation.items" :key="index">
                                                <div class="flex items-center justify-between text-xs bg-white/40 p-4 rounded-xl border border-luxury-alabas">
                                                    <span class="text-luxury-forest font-bold" x-text="item.name"></span>
                                                    <span class="font-black text-luxury-gold uppercase tracking-wider" x-text="item.quantity + ' ' + (item.unit || 'unit')"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="border-t border-luxury-alabas/50 mt-6 pt-6">
                                        <div class="bg-blue-50/70 border border-blue-100 rounded-2xl p-5">
                                            <div class="flex items-center gap-3 mb-3 text-blue-900 font-bold text-sm">
                                                <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
                                                <span>Tracking Penjemputan</span>
                                            </div>
                                            <div class="space-y-3">
                                                <p class="text-xs text-blue-800 font-medium">
                                                    Diklaim pada: <span class="font-bold" x-text="donation.claimed_at"></span>
                                                </p>
                                                <div class="bg-white rounded-2xl p-4 border border-blue-100 shadow-sm">
                                                    <span class="text-[9px] font-black text-blue-400 uppercase tracking-widest block mb-1">Jadwal Penjemputan Resmi</span>
                                                    <div class="flex items-center gap-2 text-blue-900 font-black text-base">
                                                        <i data-lucide="calendar" class="w-5 h-5 text-blue-600"></i>
                                                        <span x-text="donation.pickup_time || 'Segera'"></span>
                                                    </div>
                                                </div>
                                                <p class="text-[10px] text-blue-600 italic font-medium leading-relaxed">Mohon pastikan armada penjemputan Anda tiba tepat waktu sesuai dengan jadwal yang disepakati.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col sm:flex-row gap-4 mt-6 pt-6 border-t border-luxury-alabas/50">
                                    <a :href="'https://maps.google.com/?q=' + encodeURIComponent(donation.store.address)" target="_blank" class="flex-1 flex items-center justify-center gap-2 border-2 border-blue-100 text-blue-700 px-4 py-4 rounded-xl hover:bg-blue-50 transition-all font-bold text-xs uppercase tracking-wider">
                                        <i data-lucide="navigation" class="w-4 h-4"></i>
                                        Rute Resto
                                    </a>
                                    <a :href="'https://wa.me/' + donation.store.phone" class="flex-1 flex items-center justify-center gap-2 border-2 border-gray-100 text-gray-700 px-4 py-4 rounded-xl hover:bg-gray-50 transition-all font-bold text-xs uppercase tracking-wider">
                                        <i data-lucide="message-square" class="w-4 h-4"></i>
                                        Hubungi WA
                                    </a>
                                    <form :action="'{{ route('lembaga.donations.complete', 'DONATION_ID') }}'.replace('DONATION_ID', donation.id)" method="POST" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center justify-center gap-2 bg-green-600 text-white px-4 py-4 rounded-xl hover:bg-green-700 transition-all font-black text-xs uppercase tracking-wider shadow-xl shadow-green-100 active:scale-95">
                                            <i data-lucide="check-circle" class="w-4 h-4 text-white animate-pulse"></i>
                                            Konfirmasi
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="claimedDonations().length === 0">
                <div class="bg-white/40 rounded-[2.5rem] border border-dashed border-gray-200 p-16 text-center shadow-sm">
                    <i data-lucide="truck" class="w-16 h-16 text-gray-300 mx-auto mb-4 animate-pulse"></i>
                    <h3 class="text-xl font-serif font-bold text-gray-900 mb-2">Tidak Ada Donasi Diproses</h3>
                    <p class="text-gray-500 max-w-sm mx-auto text-sm">Semua donasi yang sudah Anda klaim dan sedang dalam proses perjalanan akan muncul di sini.</p>
                </div>
            </template>
        </div>

        <!-- Completed Tab Content -->
        <div x-show="activeTab === 'completed'" class="space-y-6" x-cloak>
            <template x-if="completedDonations().length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 animate-in fade-in duration-500">
                    <template x-for="donation in completedDonations()" :key="donation.id">
                        <div class="glass-card rounded-[2.5rem] overflow-hidden transition-all duration-500 flex flex-col justify-between group">
                            <div class="p-8 flex-1 flex flex-col justify-between bg-white/10 opacity-95">
                                <div>
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3">
                                                <h3 class="font-serif text-2xl font-bold text-luxury-forest group-hover:text-luxury-gold transition-colors" x-text="donation.store.name"></h3>
                                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-50 border border-gray-200 px-3 py-1 text-[10px] font-black text-gray-600 uppercase tracking-wider">
                                                    <i data-lucide="check-circle" class="w-3 h-3"></i> Selesai
                                                </span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-3 mt-3 text-[10px] font-bold text-luxury-slate uppercase tracking-wider">
                                                <span class="flex items-center gap-1">📍 <span x-text="donation.store.address"></span></span>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-mono font-black text-luxury-slate tracking-widest" x-text="'#' + donation.id"></span>
                                    </div>

                                    <div class="border-t border-luxury-alabas/50 mt-6 pt-6">
                                        <div class="bg-green-50 border border-green-100 rounded-2xl p-5 shadow-sm">
                                            <div class="flex items-center gap-3 mb-3 text-green-700 font-bold text-sm">
                                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                                                <span>Donasi Tersalurkan</span>
                                            </div>
                                            <div class="text-xs text-green-800 space-y-2 leading-relaxed">
                                                <p class="flex justify-between"><span>Diklaim pada:</span> <span class="font-bold" x-text="donation.claimed_at"></span></p>
                                                <p class="flex justify-between"><span>Jadwal Penjemputan:</span> <span class="font-bold" x-text="donation.pickup_time || '-'"></span></p>
                                                <p class="flex justify-between"><span>Diterima pada:</span> <span class="font-bold" x-text="donation.delivered_at || donation.claimed_at"></span></p>
                                            </div>
                                            <button @click="openReportModal(donation)" class="mt-5 w-full flex items-center justify-center gap-2 bg-red-50 text-red-600 border border-red-100 py-3.5 px-4 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-red-100 transition active:scale-95">
                                                <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                                                Laporkan Masalah Makanan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="completedDonations().length === 0">
                <div class="bg-white/40 rounded-[2.5rem] border border-dashed border-gray-200 p-16 text-center shadow-sm">
                    <i data-lucide="history" class="w-16 h-16 text-gray-300 mx-auto mb-4 animate-spin-slow"></i>
                    <h3 class="text-xl font-serif font-bold text-gray-900 mb-2">Belum Ada Riwayat</h3>
                    <p class="text-gray-500 max-w-sm mx-auto text-sm">Riwayat donasi surplus yang berhasil disalurkan oleh lembaga Anda akan terkumpul rapi di sini.</p>
                </div>
            </template>
        </div>
    </div>

    <!-- Claim Modal -->
    <div x-show="showClaimModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
         x-cloak>
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-purple-950/40 backdrop-blur-md" @click="showClaimModal = false"></div>

        <!-- Modal Content -->
        <div class="bg-white rounded-[3.5rem] w-full max-w-lg overflow-hidden shadow-2xl relative border border-purple-100 p-10 animate-in zoom-in-95 duration-300" 
             x-show="showClaimModal"
             x-transition:enter="transition ease-out duration-300 delay-100"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="flex items-center justify-between pb-6 border-b border-gray-50 mb-8">
                <div>
                    <h3 class="text-2xl font-serif font-bold text-gray-900 leading-tight">Konfirmasi Klaim Donasi</h3>
                    <p class="text-xs text-gray-500 font-medium mt-1">Lengkapi detail penjemputan makanan Anda</p>
                </div>
                <button @click="showClaimModal = false" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:bg-gray-100 transition-all">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form :action="'{{ route('lembaga.donations.claim', 'DONATION_ID') }}'.replace('DONATION_ID', selectedDonation?.id)" method="POST" class="space-y-8">
                @csrf
                <div class="flex items-center gap-4 p-5 bg-purple-50 rounded-2xl border border-purple-100/50">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600 shrink-0 shadow-sm">
                        <i data-lucide="package" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-purple-400 uppercase tracking-widest mb-0.5">Menerima Donasi Dari</div>
                        <div class="font-bold text-purple-900 text-lg leading-tight" x-text="selectedDonation?.store.name"></div>
                        <div class="text-xs text-purple-700 font-medium mt-1" x-text="selectedDonation?.items[0].name + ' (' + selectedDonation?.items[0].quantity + ' ' + selectedDonation?.items[0].unit + ')'"></div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pilih Jadwal Penjemputan</label>
                        <span class="text-[10px] bg-orange-100 text-orange-700 px-3 py-1 rounded-full font-bold uppercase tracking-wider" x-text="selectedDonation?.pickup_time_window"></span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                         <template x-for="slot in availableSlots" :key="slot">
                             <label class="relative group cursor-pointer">
                                 <input type="radio" name="pickup_time" :value="slot" class="sr-only peer" required>
                                 <div class="p-4 text-center rounded-2xl border-2 border-gray-100 peer-checked:border-purple-600 peer-checked:bg-purple-50 group-hover:border-purple-100 transition-all font-bold text-sm text-gray-600 peer-checked:text-purple-700 shadow-sm active:scale-95">
                                     <span x-text="slot"></span>
                                 </div>
                             </label>
                         </template>
                    </div>
                    <p class="text-[11px] text-gray-400 flex items-center gap-1.5 mt-2">
                        <i data-lucide="info" class="w-4 h-4 text-gray-450 shrink-0"></i>
                        Pilihlah opsi slot waktu yang tersedia sesuai jam operasional mitra toko.
                    </p>
                </div>

                <div class="p-4 bg-orange-50 rounded-2xl border border-orange-100/50 flex gap-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-orange-650 shrink-0 mt-0.5"></i>
                    <p class="text-[11px] text-orange-850 font-medium leading-relaxed">Dengan mengonfirmasi klaim, lembaga Anda berkomitmen untuk menjemput donasi tepat waktu guna menjaga kualitas kesegaran makanan.</p>
                </div>

                <div class="flex gap-4 pt-6 border-t border-gray-100 justify-end">
                    <button type="button" @click="showClaimModal = false" class="py-4 px-6 text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 hover:text-gray-900 transition active:scale-95">
                        Batal
                    </button>
                    <button type="submit" class="bg-purple-600 text-white py-4 px-8 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] shadow-xl hover:bg-purple-750 transition active:scale-95 flex items-center justify-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Konfirmasi Klaim
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Modal -->
    <div x-show="showReportModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
         x-cloak>
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-red-900/40 backdrop-blur-md" @click="showReportModal = false"></div>

        <!-- Modal Content -->
        <div x-show="showReportModal"
             x-transition:enter="ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-y-12 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="relative bg-white w-full max-w-2xl rounded-[3.5rem] overflow-hidden shadow-2xl border border-gray-100 p-12">
            
            <style>
                @keyframes stroke {
                    100% {
                        stroke-dashoffset: 0;
                    }
                }
            </style>

            <!-- Loading State Overlay -->
            <div x-show="isReporting" 
                 class="absolute inset-0 bg-white/90 backdrop-blur-md z-[110] flex flex-col items-center justify-center p-12 text-center"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-cloak>
                <div class="w-20 h-20 relative flex items-center justify-center">
                    <div class="absolute inset-0 border-4 border-red-200 border-t-red-650 rounded-full animate-spin"></div>
                    <i data-lucide="alert-circle" class="w-8 h-8 text-red-650 animate-pulse"></i>
                </div>
                <h4 class="text-xl font-black text-gray-900 mt-8 mb-2">Mengirim Laporan Anda</h4>
                <p class="text-[10px] text-gray-500 font-black uppercase tracking-[0.2em] animate-pulse">Mohon tunggu sebentar...</p>
            </div>

            <!-- Success State Overlay -->
            <div x-show="isReportSuccess" 
                 class="absolute inset-0 bg-white/95 backdrop-blur-md z-[110] flex flex-col items-center justify-center p-12 text-center"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-cloak>
                <div class="w-24 h-24 bg-green-50 rounded-full flex items-center justify-center mb-8 border border-green-100 shadow-md relative overflow-hidden group">
                    <svg class="w-12 h-12 text-green-500 stroke-[3] scale-100" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle cx="26" cy="26" r="25" fill="none" style="stroke: #10B981; stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 2; stroke-miterlimit: 10; animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;"/>
                        <path fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" style="stroke: #10B981; stroke-dasharray: 48; stroke-dashoffset: 48; stroke-width: 4; stroke-linecap: round; stroke-linejoin: round; animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.6s forwards;"/>
                    </svg>
                </div>
                <h4 class="text-3xl font-bold text-gray-900 mb-4 font-serif">Laporan Terkirim!</h4>
                <p class="text-xs text-gray-500 font-semibold max-w-xs leading-relaxed">Terima kasih. Laporan masalah telah diterima dan akan segera ditinjau oleh tim admin kami.</p>
            </div>

            <div class="text-center mb-10">
                <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-red-100">
                    <i data-lucide="alert-triangle" class="w-8 h-8 text-red-650 mx-auto"></i>
                </div>
                <h3 class="text-3xl font-serif font-bold text-gray-900">Laporkan Masalah Makanan</h3>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-[0.2em] mt-2">Bantu kami menjaga kualitas makanan terbaik</p>
            </div>

            <div class="bg-red-50/50 rounded-2xl p-5 border border-red-100/50 flex gap-4 items-center mb-8">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center text-red-600 shrink-0">
                    <i data-lucide="package" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="text-[10px] font-black text-red-400 uppercase tracking-widest mb-0.5">Melaporkan Donasi Dari</div>
                    <div class="font-bold text-red-900 text-lg" x-text="selectedDonationForReport?.store.name"></div>
                    <div class="text-xs text-red-700 font-medium" x-text="selectedDonationForReport?.items[0].name"></div>
                </div>
            </div>

            <form action="{{ route('lembaga.report.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-8" @submit="submitReport($event)">
                @csrf
                <input type="hidden" name="donation_id" :value="selectedDonationForReport ? selectedDonationForReport.id : ''">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">Jenis Masalah</label>
                            <div class="relative">
                                <select name="issue_type" x-model="reportIssueType" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-5 outline-none focus:ring-2 focus:ring-red-500 transition-all font-bold text-gray-950 appearance-none">
                                    <option value="bad_quality">Kualitas Buruk / Basi</option>
                                    <option value="expired">Sudah Kedaluwarsa</option>
                                    <option value="mismatch">Tidak Sesuai Deskripsi</option>
                                    <option value="other">Lainnya</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-6 text-gray-900">
                                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">Bukti Foto</label>
                            <div class="relative border-2 border-dashed rounded-[1.5rem] p-6 text-center transition-all duration-300 bg-gray-50/50 group overflow-hidden"
                                 :class="evidenceImagePreview ? 'border-green-500 bg-green-50/5' : 'border-gray-200 hover:border-red-500'">
                                
                                <input type="file" name="evidence_image" accept="image/*" 
                                       @change="
                                           const file = $event.target.files[0];
                                           if (file) {
                                               evidenceImageName = file.name;
                                               evidenceImagePreview = URL.createObjectURL(file);
                                           } else {
                                               evidenceImageName = '';
                                               evidenceImagePreview = '';
                                           }
                                       "
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">

                                <!-- Standard Placeholder -->
                                <div x-show="!evidenceImagePreview" class="space-y-2">
                                    <i data-lucide="image" class="w-8 h-8 text-gray-400 mx-auto group-hover:scale-110 transition-transform"></i>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-900 block">Unggah Bukti Foto</span>
                                    <span class="text-[9px] font-medium text-gray-500 block">Format JPG, PNG, atau WEBP</span>
                                </div>

                                <!-- Image Preview Container -->
                                <div x-show="evidenceImagePreview" class="relative z-10 flex flex-col items-center gap-4" x-cloak>
                                    <div class="relative w-24 h-24 rounded-2xl overflow-hidden shadow-md border-2 border-white mx-auto">
                                        <img :src="evidenceImagePreview" class="w-full h-full object-cover">
                                    </div>
                                    <div class="text-center">
                                        <span class="text-[10px] font-bold text-green-600 block truncate max-w-[200px]" x-text="evidenceImageName"></span>
                                        <span class="text-[9px] font-medium text-gray-500 block">Siap dikirim</span>
                                    </div>
                                    <!-- Remove Button -->
                                    <button type="button" 
                                            @click.prevent.stop="
                                                evidenceImageName = '';
                                                evidenceImagePreview = '';
                                                $el.closest('.group').querySelector('input[type=file]').value = '';
                                            "
                                            class="absolute top-2 right-2 bg-red-650 text-white rounded-full p-2 hover:bg-red-700 transition-colors shadow-md z-30 flex items-center justify-center">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">Detail Kejadian</label>
                            <textarea name="description" x-model="reportDescription" rows="5" required 
                                      class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] p-6 outline-none focus:ring-2 focus:ring-red-500 transition-all font-medium text-gray-900 placeholder:text-gray-400/50 resize-none h-[225px]" 
                                      placeholder="Jelaskan detail masalah yang Anda alami secara rinci..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 flex flex-col sm:flex-row gap-6 justify-end">
                    <button type="button" @click="showReportModal = false" class="py-5 px-8 text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 hover:text-gray-900 transition-colors text-center">Batal</button>
                    <button type="submit" class="bg-red-600 text-white py-5 px-10 rounded-[1.5rem] font-black uppercase tracking-[0.2em] text-[10px] shadow-xl hover:bg-red-700 transition-all duration-500 active:scale-95 text-center flex items-center justify-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Warning Dialog Modal Overlay -->
    <div x-show="isWarningDialogOpen" 
         class="fixed inset-0 z-[120] flex items-center justify-center p-4 sm:p-6" 
         x-cloak>
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-red-950/50 backdrop-blur-md" @click="isWarningDialogOpen = false"></div>

        <!-- Modal Content -->
        <div x-show="isWarningDialogOpen"
             x-transition:enter="ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-y-12 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="relative bg-white/95 backdrop-blur-lg w-full max-w-md rounded-[2.5rem] overflow-hidden shadow-2xl border border-red-200/50 p-10 z-[130] text-center animate-in fade-in zoom-in duration-300">
            
            <!-- Icon Warning/Alert -->
            <div class="w-20 h-20 bg-red-50 text-red-600 border border-red-100 rounded-[1.75rem] flex items-center justify-center mb-6 mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 animate-bounce">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/>
                </svg>
            </div>

            <!-- Title -->
            <h3 class="text-2xl font-serif font-black text-gray-900 mb-4" x-text="warningTitle"></h3>
            
            <!-- Message -->
            <p class="text-xs font-medium text-gray-600 leading-relaxed mb-8 whitespace-pre-line text-center" x-text="warningMessage"></p>
            
            <!-- Action Button -->
            <button type="button" 
                    @click="isWarningDialogOpen = false" 
                    class="w-full py-4 bg-red-600 hover:bg-red-700 text-white font-black rounded-2xl shadow-lg transition-all duration-300 tracking-widest uppercase text-[10px] active:scale-95">
                Mengerti
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('donationsPage', () => ({
            activeTab: '{{ $activeTab }}',
            donations: @json($donations),
            showClaimModal: false,
            selectedDonation: null,
            availableSlots: [],
            
            availableDonations() {
                return this.donations.filter(d => d.status === 'available');
            },
            claimedDonations() {
                return this.donations.filter(d => d.status === 'claimed');
            },
            completedDonations() {
                return this.donations.filter(d => d.status === 'completed');
            },

            showReportModal: false,
            selectedDonationForReport: null,
            reportIssueType: 'bad_quality',
            reportDescription: '',
            evidenceImageName: '',
            evidenceImagePreview: '',
            isReporting: false,
            isReportSuccess: false,
            isWarningDialogOpen: false,
            warningTitle: '',
            warningMessage: '',

            openReportModal(donation) {
                this.selectedDonationForReport = donation;
                this.reportIssueType = 'bad_quality';
                this.reportDescription = '';
                this.evidenceImageName = '';
                this.evidenceImagePreview = '';
                this.isReporting = false;
                this.isReportSuccess = false;
                this.showReportModal = true;
                
                setTimeout(() => {
                    if (window.lucide) window.lucide.createIcons();
                }, 50);
            },

            async submitReport(e) {
                e.preventDefault();
                const form = e.target;
                this.isReporting = true;
                
                // Client-side image size check (max 2MB / 2048KB)
                const fileInput = form.querySelector('input[name=evidence_image]');
                if (fileInput && fileInput.files && fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    const maxSize = 2 * 1024 * 1024; // 2MB
                    if (file.size > maxSize) {
                        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                        this.warningTitle = 'Gambar Terlalu Besar';
                        this.warningMessage = 'Ukuran file gambar yang Anda pilih terlalu besar (' + fileSizeMB + ' MB). Maksimal ukuran gambar yang diizinkan adalah 2.00 MB. Silakan kompres gambar atau pilih gambar lain yang lebih kecil.';
                        this.isWarningDialogOpen = true;
                        this.isReporting = false;
                        return;
                    }
                }

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    await new Promise(resolve => setTimeout(resolve, 1500));

                    if (response.ok) {
                        this.isReporting = false;
                        this.isReportSuccess = true;
                        
                        setTimeout(() => {
                            this.showReportModal = false;
                            window.location.reload();
                        }, 2000);
                    } else {
                        let errorMessage = 'Gagal mengirim laporan';
                        try {
                            const errorData = await response.json();
                            if (errorData.errors && errorData.errors.evidence_image) {
                                errorMessage = errorData.errors.evidence_image.join(', ');
                            } else if (errorData.message) {
                                errorMessage = errorData.message;
                            }
                        } catch (e) {
                            if (response.status === 413) {
                                errorMessage = 'Ukuran file gambar melebihi batas upload server (maksimal 2MB).';
                            }
                        }
                        throw new Error(errorMessage);
                    }
                } catch (error) {
                    this.isReporting = false;
                    this.warningTitle = 'Gagal Mengirim Laporan';
                    this.warningMessage = error.message;
                    this.isWarningDialogOpen = true;
                }
            },

            openClaimModal(donation) {
                this.selectedDonation = donation;
                this.availableSlots = this.generateSlots(donation.pickup_start, donation.pickup_end);
                this.showClaimModal = true;
                
                setTimeout(() => {
                    if (window.lucide) window.lucide.createIcons();
                }, 50);
            },

            generateSlots(start, end) {
                if (!start || !end) return ['18:00', '18:30', '19:00', '19:30'];
                
                const slots = [];
                let [startH, startM] = start.split(':').map(Number);
                let [endH, endM] = end.split(':').map(Number);
                
                let current = new Date();
                current.setHours(startH, startM, 0);
                
                let endTime = new Date();
                endTime.setHours(endH, endM, 0);
                
                while (current < endTime) {
                    let hh = String(current.getHours()).padStart(2, '0');
                    let mm = String(current.getMinutes()).padStart(2, '0');
                    slots.push(`${hh}:${mm}`);
                    current.setMinutes(current.getMinutes() + 30);
                }
                
                return slots;
            },

            init() {
                this.$watch('activeTab', () => {
                    setTimeout(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    }, 50);
                });
            }
        }))
    });
</script>
@endsection
