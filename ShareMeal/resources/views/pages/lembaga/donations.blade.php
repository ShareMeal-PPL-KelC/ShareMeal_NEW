@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="donationsPage()">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Kelola Donasi</h1>
        <p class="text-gray-600 mt-1">Klaim & tracking donasi makanan</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5"></i>
        {{ session('success') }}
    </div>
    @endif
    
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl flex items-center gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5"></i>
        {{ session('error') }}
    </div>
    @endif

    <!-- Info Banner -->
    <div class="bg-purple-50 border border-purple-200 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5"></i>
            <div class="text-sm text-purple-800">
                <strong>Sistem First-Come, First-Served:</strong> Donasi tersedia dapat diklaim oleh lembaga terverifikasi dengan prinsip siapa cepat dia dapat. Pastikan Anda siap menerima donasi sebelum melakukan klaim.
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <!-- Tabs List -->
        <div class="flex space-x-1 border-b border-gray-200">
            <button @click="activeTab = 'available'"
                    :class="{'border-b-2 border-green-600 text-green-600': activeTab === 'available', 'text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'available'}" 
                    class="px-4 py-2 font-medium text-sm flex items-center gap-2 border-b-2 border-transparent transition-colors">
                <i data-lucide="package" class="w-4 h-4"></i>
                Tersedia (<span x-text="availableDonations().length"></span>)
            </button>
            <button @click="activeTab = 'claimed'"
                    :class="{'border-b-2 border-green-600 text-green-600': activeTab === 'claimed', 'text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'claimed'}" 
                    class="px-4 py-2 font-medium text-sm flex items-center gap-2 border-b-2 border-transparent transition-colors">
                <i data-lucide="truck" class="w-4 h-4"></i>
                Diproses (<span x-text="claimedDonations().length"></span>)
            </button>
            <button @click="activeTab = 'completed'"
                    :class="{'border-b-2 border-green-600 text-green-600': activeTab === 'completed', 'text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'completed'}" 
                    class="px-4 py-2 font-medium text-sm flex items-center gap-2 border-b-2 border-transparent transition-colors">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                Riwayat (<span x-text="completedDonations().length"></span>)
            </button>
        </div>

        <!-- Available Tab Content -->
        <div x-show="activeTab === 'available'" class="space-y-4">
            <template x-if="availableDonations().length > 0">
                <template x-for="donation in availableDonations()" :key="donation.id">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-xl font-bold text-gray-900" x-text="donation.store.name"></h3>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                            <i data-lucide="package" class="w-3 h-3"></i>
                                            Tersedia
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap gap-3 mt-2 text-sm text-gray-600">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                                            <span x-text="donation.store.address"></span>
                                        </span>
                                        <span>• <span x-text="donation.distance"></span></span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-400 font-bold" x-text="'#' + donation.id"></div>
                                </div>
                            </div>

                            <div class="border-t border-gray-50 mt-4 pt-4">
                                <h4 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-2">Item Donasi</h4>
                                <div class="space-y-2">
                                    <template x-for="(item, index) in donation.items" :key="index">
                                        <div class="flex items-center justify-between text-sm bg-gray-50 p-3 rounded-xl border border-transparent hover:border-gray-100 transition">
                                            <span class="text-gray-900 font-medium" x-text="item.name"></span>
                                            <span class="font-bold text-gray-600" x-text="item.quantity + ' ' + (item.unit || 'unit')"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="border-t border-gray-50 mt-4 pt-4 flex items-center gap-2 text-sm">
                                <i data-lucide="clock" class="w-4 h-4 text-orange-600"></i>
                                <span class="text-orange-600 font-bold">
                                    Tersedia sampai: <span x-text="donation.available_until"></span>
                                </span>
                            </div>
                            
                            <form :action="'{{ route('lembaga.donations.claim', 'DONATION_ID') }}'.replace('DONATION_ID', donation.id)" method="POST" class="mt-4">
                                @csrf
                                <button type="submit" class="w-full flex items-center justify-center gap-2 bg-purple-600 text-white px-6 py-3 rounded-xl hover:bg-purple-700 transition-all font-bold shadow-lg shadow-purple-100">
                                    <i data-lucide="heart" class="w-4 h-4 text-white"></i>
                                    Klaim Donasi
                                </button>
                            </form>
                        </div>
                    </div>
                </template>
            </template>
            <template x-if="availableDonations().length === 0">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="package" class="w-10 h-10 text-gray-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Tidak Ada Donasi Tersedia</h3>
                    <p class="text-gray-500 max-w-sm mx-auto">Donasi baru akan muncul di sini saat mitra menyediakan surplus makanan</p>
                </div>
            </template>
        </div>

        <!-- Claimed Tab Content -->
        <div x-show="activeTab === 'claimed'" class="space-y-4" x-cloak>
            <template x-if="claimedDonations().length > 0">
                <template x-for="donation in claimedDonations()" :key="donation.id">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-xl font-bold text-gray-900" x-text="donation.store.name"></h3>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            Diproses
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap gap-3 mt-2 text-sm text-gray-600">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                                            <span x-text="donation.store.address"></span>
                                        </span>
                                        <span>• <span x-text="donation.distance"></span></span>
                                    </div>
                                </div>
                                <div class="text-right text-xs text-gray-400 font-bold" x-text="'#' + donation.id"></div>
                            </div>

                            <div class="border-t border-gray-50 mt-4 pt-4">
                                <h4 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-2">Item Donasi</h4>
                                <div class="space-y-2">
                                    <template x-for="(item, index) in donation.items" :key="index">
                                        <div class="flex items-center justify-between text-sm bg-gray-50 p-3 rounded-xl">
                                            <span class="text-gray-900 font-medium" x-text="item.name"></span>
                                            <span class="font-bold text-gray-600" x-text="item.quantity + ' ' + (item.unit || 'unit')"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="border-t border-gray-50 mt-4 pt-4">
                                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                                    <div class="flex items-center gap-3 mb-2">
                                        <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                                        <span class="font-bold text-blue-900">Status Tracking</span>
                                    </div>
                                    <p class="text-sm text-blue-800">
                                        Diklaim pada: <span x-text="donation.claimed_at"></span>
                                    </p>
                                    <p class="text-[11px] text-blue-600 mt-2 font-medium">Silakan hubungi mitra untuk koordinasi pengambilan donasi.</p>
                                </div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-3 mt-4">
                                <a :href="'https://maps.google.com/?q=' + encodeURIComponent(donation.store.address)" target="_blank" class="flex-1 flex items-center justify-center gap-2 border-2 border-blue-100 text-blue-700 px-4 py-3 rounded-xl hover:bg-blue-50 transition-all font-bold">
                                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                                    Lokasi Resto
                                </a>
                                <a :href="'https://wa.me/' + donation.store.phone" class="flex-1 flex items-center justify-center gap-2 border-2 border-gray-100 text-gray-700 px-4 py-3 rounded-xl hover:bg-gray-50 transition-all font-bold">
                                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                                    Hubungi Mitra
                                </a>
                                <form :action="'{{ route('lembaga.donations.complete', 'DONATION_ID') }}'.replace('DONATION_ID', donation.id)" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center justify-center gap-2 bg-green-600 text-white px-4 py-3 rounded-xl hover:bg-green-700 transition-all font-bold shadow-lg shadow-green-100">
                                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                                        Konfirmasi Diterima
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </template>
            </template>
            <template x-if="claimedDonations().length === 0">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <i data-lucide="truck" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Tidak Ada Donasi Diproses</h3>
                    <p class="text-gray-500">Donasi yang sudah diklaim akan muncul di sini</p>
                </div>
            </template>
        </div>

        <!-- Completed Tab Content -->
        <div x-show="activeTab === 'completed'" class="space-y-4" x-cloak>
            <template x-if="completedDonations().length > 0">
                <template x-for="donation in completedDonations()" :key="donation.id">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden opacity-90">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-xl font-bold text-gray-900" x-text="donation.store.name"></h3>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                                            <i data-lucide="check-circle" class="w-3 h-3"></i>
                                            Selesai
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap gap-3 mt-2 text-sm text-gray-600">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                                            <span x-text="donation.store.address"></span>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right text-xs text-gray-400 font-bold" x-text="'#' + donation.id"></div>
                            </div>

                            <div class="border-t border-gray-50 mt-4 pt-4">
                                <div class="bg-green-50 border border-green-100 rounded-xl p-4">
                                    <div class="flex items-center gap-3 mb-2 text-green-700">
                                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                                        <span class="font-bold">Donasi Sudah Diterima</span>
                                    </div>
                                    <div class="text-sm text-green-800 space-y-1">
                                        <p>Diklaim: <span x-text="donation.claimed_at"></span></p>
                                        <p>Diterima: <span x-text="donation.delivered_at || donation.claimed_at"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </template>
            <template x-if="completedDonations().length === 0">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <i data-lucide="history" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Belum Ada Riwayat</h3>
                    <p class="text-gray-500">Riwayat donasi yang sudah diterima akan muncul di sini</p>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('donationsPage', () => ({
            activeTab: '{{ $activeTab }}',
            donations: @json($donations),
            
            availableDonations() {
                return this.donations.filter(d => d.status === 'available');
            },
            claimedDonations() {
                return this.donations.filter(d => d.status === 'claimed');
            },
            completedDonations() {
                return this.donations.filter(d => d.status === 'completed');
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
