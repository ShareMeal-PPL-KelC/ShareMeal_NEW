@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <div class="mb-12">
        <h1 class="text-5xl font-serif font-bold text-luxury-forest leading-tight">Ringkasan Bisnis</h1>
        <p class="text-luxury-slate font-medium mt-2 tracking-wide text-center md:text-left">Optimalkan inventaris surplus Anda dan tingkatkan dampak sosial Anda terhadap komunitas.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
        <div class="bg-white p-8 rounded-[2rem] luxury-shadow border border-luxury-alabas hover:bg-luxury-forest transition-all duration-500 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-luxury-ivory rounded-xl flex items-center justify-center group-hover:bg-white/10">
                    <i data-lucide="package" class="w-6 h-6 text-luxury-gold"></i>
                </div>
                <div class="text-[10px] font-black text-luxury-gold uppercase tracking-widest">Stok Aktif</div>
            </div>
            <div class="text-4xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">{{ $stats->totalProducts }} Produk</div>
            <p class="text-[10px] text-luxury-slate group-hover:text-white/60 mt-3 font-bold uppercase tracking-wider italic">Dalam inventaris aktif</p>
        </div>

        <div class="bg-white p-8 rounded-[2rem] luxury-shadow border border-luxury-alabas hover:bg-luxury-forest transition-all duration-500 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-luxury-ivory rounded-xl flex items-center justify-center group-hover:bg-white/10">
                    <i data-lucide="dollar-sign" class="w-6 h-6 text-luxury-gold"></i>
                </div>
                <div class="text-[10px] font-black text-luxury-gold uppercase tracking-widest">Pendapatan</div>
            </div>
            <div class="text-3xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">Rp {{ number_format($stats->totalRevenue / 1000, 0) }}rb</div>
            <p class="text-[10px] text-luxury-emerald group-hover:text-white mt-3 font-black uppercase tracking-wider bg-luxury-emerald/10 group-hover:bg-white/10 px-3 py-1 rounded-full inline-block">
                +12.5% bln/bln
            </p>
        </div>

        <div class="bg-white p-8 rounded-[2rem] luxury-shadow border border-luxury-alabas hover:bg-luxury-forest transition-all duration-500 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-luxury-ivory rounded-xl flex items-center justify-center group-hover:bg-white/10">
                    <i data-lucide="star" class="w-6 h-6 text-luxury-gold"></i>
                </div>
                <div class="text-[10px] font-black text-luxury-gold uppercase tracking-widest">Apresiasi</div>
            </div>
            <div class="text-4xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">{{ $stats->averageRating }} <span class="text-sm opacity-40">/ 5.0</span></div>
            <p class="text-[10px] text-luxury-slate group-hover:text-white/60 mt-3 font-bold uppercase tracking-wider italic">Dari {{ $stats->totalReviews }} ulasan</p>
        </div>

        <div class="bg-white p-8 rounded-[2rem] luxury-shadow border border-luxury-alabas hover:bg-luxury-forest transition-all duration-500 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-luxury-ivory rounded-xl flex items-center justify-center group-hover:bg-white/10">
                    <i data-lucide="leaf" class="w-6 h-6 text-luxury-gold"></i>
                </div>
                <div class="text-[10px] font-black text-luxury-gold uppercase tracking-widest">Dampak Sosial</div>
            </div>
            <div class="text-4xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">{{ $stats->foodSaved }}kg</div>
            <p class="text-[10px] text-luxury-slate group-hover:text-white/60 mt-3 font-bold uppercase tracking-wider italic">Makanan terselamatkan</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-16">
        <!-- Expiring Items Alert -->
        <div class="bg-white rounded-[2.5rem] border border-luxury-alabas luxury-shadow overflow-hidden flex flex-col">
            <div class="p-8 border-b border-luxury-alabas flex items-center justify-between bg-luxury-ivory/30">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></div>
                    <h2 class="text-xl font-serif font-bold text-luxury-forest">Inventaris Mendesak</h2>
                </div>
                <a href="{{ route('mitra.inventory') }}" class="text-[10px] font-black uppercase tracking-[0.2em] text-luxury-gold hover:text-luxury-forest transition-colors">Kelola</a>
            </div>
            <div class="p-8 space-y-4 flex-1">
                @forelse($expiringItems as $item)
                <div class="flex items-center justify-between p-6 bg-luxury-ivory/50 rounded-2xl border border-luxury-alabas hover:bg-white hover:luxury-shadow transition-all duration-300 group">
                    <div>
                        <div class="font-bold text-luxury-forest group-hover:text-luxury-gold transition-colors">{{ $item->name }}</div>
                        <div class="text-[10px] text-luxury-slate font-black uppercase tracking-widest mt-1">Stok: {{ $item->stock }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-black text-orange-600 uppercase tracking-widest mb-1">{{ $item->expires_at->locale('id')->diffForHumans() }}</div>
                        <div class="w-16 h-1 bg-luxury-alabas rounded-full overflow-hidden">
                            <div class="h-full bg-orange-400 w-2/3"></div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <i data-lucide="check-circle" class="w-10 h-10 text-luxury-emerald/30 mx-auto mb-4"></i>
                    <p class="text-luxury-slate font-serif italic text-lg">Inventaris teroptimalkan dengan baik.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Reviews -->
        <div class="bg-white rounded-[2.5rem] border border-luxury-alabas luxury-shadow overflow-hidden flex flex-col">
            <div class="p-8 border-b border-luxury-alabas flex items-center justify-between bg-luxury-ivory/30">
                <div class="flex items-center gap-3">
                    <i data-lucide="star" class="w-5 h-5 text-luxury-gold"></i>
                    <h2 class="text-xl font-serif font-bold text-luxury-forest">Apresiasi Terbaru</h2>
                </div>
                <a href="{{ route('mitra.reviews') }}" class="text-[10px] font-black uppercase tracking-[0.2em] text-luxury-gold hover:text-luxury-forest transition-colors">Lihat Semua</a>
            </div>
            <div class="p-8 space-y-6 flex-1">
                @forelse($recentReviews as $review)
                <div class="p-6 bg-white border border-luxury-alabas rounded-2xl hover:luxury-shadow transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-sm font-bold text-luxury-forest">{{ $review->customer->name }}</div>
                        <div class="flex gap-1">
                            @for($i = 1; $i <= 5; $i++)
                            <i data-lucide="star" class="w-3 h-3 {{ $i <= $review->rating ? 'text-luxury-gold fill-luxury-gold' : 'text-luxury-alabas' }}"></i>
                            @endfor
                        </div>
                    </div>
                    @if($review->comment)
                    <p class="text-sm font-serif text-luxury-forest italic leading-relaxed opacity-80 line-clamp-2">&ldquo;{{ $review->comment }}&rdquo;</p>
                    @else
                    <p class="text-xs text-luxury-slate italic">Apresiasi tanpa komentar</p>
                    @endif
                    <div class="text-[9px] text-luxury-gold font-black uppercase tracking-widest mt-4">{{ $review->created_at->locale('id')->diffForHumans() }}</div>
                </div>
                @empty
                <div class="text-center py-12">
                    <i data-lucide="message-square" class="w-10 h-10 text-luxury-alabas/30 mx-auto mb-4"></i>
                    <p class="text-luxury-slate font-serif italic text-lg">Menunggu umpan balik.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Interactive Analytics Chart -->
    <div class="bg-white rounded-[2.5rem] border border-luxury-alabas luxury-shadow overflow-hidden mb-16" x-data="analyticsChart()">
        <div class="p-10 border-b border-luxury-alabas flex flex-col sm:flex-row sm:items-center justify-between gap-6 bg-luxury-ivory/20">
            <div>
                <h2 class="text-3xl font-serif font-bold text-luxury-forest">Analisis Kinerja Mitra</h2>
                <p class="text-[10px] text-luxury-gold font-black uppercase tracking-[0.2em] mt-1">Arahkan kursor ke titik grafik untuk menampilkan kartu detail interaktif di atas grafik</p>
            </div>
            
            <!-- Controls -->
            <div class="flex flex-wrap items-center gap-4">
                <!-- Metric Selector -->
                <div class="flex bg-luxury-ivory p-1.5 rounded-xl border border-luxury-alabas">
                    <button @click="setMetric('revenue')" :class="metric === 'revenue' ? 'bg-luxury-forest text-white shadow-md' : 'text-luxury-slate hover:text-luxury-forest'" class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg transition-all duration-300">
                        Pendapatan
                    </button>
                    <button @click="setMetric('impact')" :class="metric === 'impact' ? 'bg-luxury-forest text-white shadow-md' : 'text-luxury-slate hover:text-luxury-forest'" class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg transition-all duration-300">
                        Dampak Sosial
                    </button>
                </div>

                <!-- Time Range Selector -->
                <div class="flex bg-luxury-ivory p-1.5 rounded-xl border border-luxury-alabas">
                    <button @click="setTimeframe('weekly')" :class="timeframe === 'weekly' ? 'bg-luxury-gold text-luxury-forest shadow-md' : 'text-luxury-slate hover:text-luxury-forest'" class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg transition-all duration-300">
                        Mingguan
                    </button>
                    <button @click="setTimeframe('monthly')" :class="timeframe === 'monthly' ? 'bg-luxury-gold text-luxury-forest shadow-md' : 'text-luxury-slate hover:text-luxury-forest'" class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg transition-all duration-300">
                        Bulanan
                    </button>
                </div>
            </div>
        </div>
        
        <div class="p-10 bg-white/50 relative" @mouseleave="showOverlay = false">
            <div class="h-[350px] w-full relative">
                <canvas id="mitraPerformanceChart"></canvas>
                
                <!-- Interactive HTML Overlay floating over the chart point -->
                <div x-show="showOverlay" 
                     x-transition 
                     class="absolute text-white p-4 rounded-2xl shadow-2xl border border-white/20 pointer-events-auto z-20 w-64 cursor-pointer hover:scale-105 active:scale-95 transition-all duration-300"
                     :class="metric === 'revenue' ? 'bg-[#174413]' : 'bg-[#c5a880] text-luxury-forest'"
                     :style="`left: ${overlayLeft}px; top: ${overlayTop}px; transform: translate(-50%, -115%);`"
                     @click="showModal = true"
                     x-cloak>
                     <div class="flex justify-between items-center mb-1.5 pb-1.5 border-b border-white/10">
                         <span class="text-[9px] font-black uppercase tracking-widest opacity-80" x-text="overlayTitle"></span>
                         <i data-lucide="arrow-right-circle" class="w-4 h-4"></i>
                     </div>
                     <div class="text-xl font-serif font-black" x-text="overlayValue"></div>
                     <div class="text-[10px] opacity-90 font-medium leading-relaxed mt-2 line-clamp-2" x-text="overlayDetail"></div>
                     <div class="text-[9px] font-black uppercase tracking-wider text-center mt-3 bg-white/20 py-1.5 rounded-lg">
                         Klik Untuk Detail
                     </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Modal for Chart Point Detail -->
    <div x-show="showModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
         x-cloak
         @keydown.escape.window="showModal = false">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-[#174413]/60 backdrop-blur-md" @click="showModal = false"></div>

        <!-- Modal Content -->
        <div class="relative bg-white rounded-[3rem] w-full max-w-lg p-10 shadow-2xl border border-luxury-alabas overflow-hidden transform transition-all">
            <div class="flex justify-between items-center mb-6 border-b border-luxury-alabas/40 pb-4">
                <div>
                    <h3 class="text-2xl font-serif font-bold text-luxury-forest">Analisis Capaian</h3>
                    <p class="text-[9px] text-luxury-gold font-black uppercase tracking-widest mt-1" x-text="overlayTitle"></p>
                </div>
                <button @click="showModal = false" class="w-10 h-10 flex items-center justify-center rounded-full bg-luxury-ivory text-luxury-forest hover:bg-luxury-forest hover:text-white transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <div class="space-y-6">
                <div class="bg-luxury-ivory p-6 rounded-2xl border border-luxury-alabas flex justify-between items-center">
                    <span class="text-xs font-black uppercase text-luxury-slate">Nilai Capaian</span>
                    <span class="text-3xl font-serif font-black text-luxury-forest" x-text="overlayValue"></span>
                </div>
                <p class="text-sm font-sans font-semibold text-luxury-forest leading-relaxed" x-text="overlayDetail"></p>
                
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-xs text-green-800 flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                    <span>Metrik kinerja optimal terdeteksi di platform ShareMeal.</span>
                </div>
            </div>
            
            <div class="mt-8 pt-6 border-t border-luxury-alabas/40 flex justify-end">
                <button @click="showModal = false" class="bg-luxury-forest text-white py-3 px-6 rounded-xl font-black uppercase tracking-wider text-[10px] shadow-md hover:bg-luxury-forest/90 active:scale-95 transition-all">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded-[3rem] border border-luxury-alabas luxury-shadow overflow-hidden mb-12">
        <div class="p-10 border-b border-luxury-alabas flex items-center justify-between bg-luxury-ivory/20">
            <div>
                <h2 class="text-3xl font-serif font-bold text-luxury-forest">Transaksi Terbaru</h2>
                <p class="text-[10px] text-luxury-gold font-black uppercase tracking-[0.2em] mt-1">Pantau pesanan terbaru dari komunitas Anda</p>
            </div>
            <a href="{{ route('mitra.orders') }}" class="px-8 py-4 rounded-2xl bg-white border border-luxury-alabas text-[10px] font-black uppercase tracking-[0.2em] text-luxury-forest hover:bg-luxury-forest hover:text-white transition-all duration-500 luxury-shadow">
                Log Lengkap
            </a>
        </div>
        <div class="divide-y divide-luxury-alabas">
            @forelse($recentOrders as $order)
            <div class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-8 hover:bg-luxury-ivory/30 transition-all duration-500 group">
                <div class="flex items-center gap-8 flex-1">
                    <div class="w-16 h-16 bg-luxury-forest/5 rounded-2xl flex items-center justify-center text-luxury-forest luxury-shadow border border-luxury-alabas transition-transform group-hover:scale-110">
                        <i data-lucide="shopping-bag" class="w-7 h-7 stroke-[1.5]"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-4">
                            <div class="font-serif text-2xl font-bold text-luxury-forest group-hover:text-luxury-gold transition-colors duration-500">{{ $order->customer->name }}</div>
                            <span class="font-mono text-[10px] text-luxury-slate opacity-50 uppercase tracking-tighter">#{{ $order->id }}</span>
                        </div>
                        <div class="text-sm text-luxury-slate font-medium mt-2 italic opacity-80">{{ $order->items_string }}</div>
                        <div class="text-[9px] text-luxury-gold font-black uppercase tracking-widest mt-3">{{ $order->created_at ? $order->created_at->locale('id')->diffForHumans() : '-' }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-10">
                    <div class="text-right">
                        <div class="text-2xl font-serif font-black text-luxury-forest">Rp {{ number_format($order->amount, 0, ',', '.') }}</div>
                        <div class="mt-2">
                            @php
                                $statusLabel = $order->status;
                                $isCompleted = $order->status === 'completed';
                                if ($order->status === 'ready') {
                                    $statusLabel = $order->receiving_method === 'delivery' ? 'Siap Diantar' : 'Siap Diambil';
                                } else {
                                    $statusLabel = [
                                        'pending' => 'Menunggu',
                                        'processing' => 'Diproses',
                                        'shipping' => 'Dikirim',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Batal'
                                    ][$order->status] ?? $order->status;
                                }
                            @endphp
                            <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-lg {{ $isCompleted ? 'bg-luxury-emerald/10 text-luxury-emerald' : 'bg-orange-50 text-orange-600' }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('mitra.orders') }}" class="w-14 h-14 bg-luxury-ivory rounded-2xl flex items-center justify-center text-luxury-forest hover:bg-luxury-forest hover:text-white transition-all duration-500 luxury-shadow group/btn active:scale-90">
                        <i data-lucide="chevron-right" class="w-6 h-6 transition-transform group-hover/btn:translate-x-1"></i>
                    </a>
                </div>
            </div>
            @empty
            <div class="p-20 text-center">
                <div class="w-20 h-20 bg-luxury-ivory rounded-full flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="inbox" class="w-8 h-8 text-luxury-alabas"></i>
                </div>
                <p class="text-luxury-slate font-serif text-xl italic">Menunggu transaksi pertama.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const initChart = () => {
        Alpine.data('analyticsChart', () => ({
            metric: 'revenue',
            timeframe: 'weekly',
            chartInstance: null,
            showOverlay: false,
            overlayTitle: '',
            overlayValue: '',
            overlayDetail: '',
            overlayLeft: 0,
            overlayTop: 0,
            showModal: false,

            // Simulated real-time performance data with narratives
            chartData: {
                revenue: {
                    weekly: {
                        labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                        data: [120000, 185000, 95000, 240000, 180000, 350000, 290000],
                        label: 'Pendapatan (Rp)',
                        color: '#174413', // Deep forest green
                        highlights: [
                            { label: 'Senin', value: 'Rp 120.000', detail: 'Awal pekan yang stabil dengan rata-rata belanja Rp 40.000 per transaksi.' },
                            { label: 'Selasa', value: 'Rp 185.000', detail: 'Peningkatan penjualan makanan surplus menu makan siang kantor.' },
                            { label: 'Rabu', value: 'Rp 95.000', detail: 'Penjualan terendah pekan ini karena stok surplus resto habis terjual lebih awal.' },
                            { label: 'Kamis', value: 'Rp 240.000', detail: 'Peningkatan permintaan sore hari untuk hidangan roti surplus & kue kering.' },
                            { label: 'Jumat', value: 'Rp 180.000', detail: 'Pembelian stabil dari pelanggan tetap menjelang akhir pekan.' },
                            { label: 'Sabtu', value: 'Rp 350.000', detail: 'Puncak penjualan tertinggi pekan ini! Menu sarapan & kopi surplus sangat diminati.' },
                            { label: 'Minggu', value: 'Rp 290.000', detail: 'Penjualan tinggi didominasi oleh komunitas olahraga pagi di sekitar outlet.' }
                        ]
                    },
                    monthly: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                        data: [1450000, 1850000, 1600000, 2900000, 2200000, 3850000],
                        label: 'Pendapatan (Rp)',
                        color: '#174413',
                        highlights: [
                            { label: 'Januari', value: 'Rp 1.450.000', detail: 'Pembukaan tahun baru dengan tingkat klaim makanan surplus yang stabil.' },
                            { label: 'Februari', value: 'Rp 1.850.000', detail: 'Kenaikan penjualan berkat promosi paket hemat penyelamat makanan.' },
                            { label: 'Maret', value: 'Rp 1.600.000', detail: 'Kinerja stabil dengan penyesuaian menu takjil surplus saat menjelang Ramadhan.' },
                            { label: 'April', value: 'Rp 2.900.000', detail: 'Lonjakan tertinggi! Banyak konsumen membeli paket porsi sahur & buka puasa surplus.' },
                            { label: 'Mei', value: 'Rp 2.200.000', detail: 'Kinerja pasca-Lebaran tetap produktif dan stabil didukung pelanggan loyal.' },
                            { label: 'Juni', value: 'Rp 3.850.000', detail: 'Rekor pendapatan bulanan baru didorong oleh kemitraan corporate gathering.' }
                        ]
                    }
                },
                impact: {
                    weekly: {
                        labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                        data: [15, 8, 19, 12, 25, 14, 30],
                        label: 'Makanan Terselamatkan (kg)',
                        color: '#c5a880', // Gold
                        highlights: [
                            { label: 'Senin', value: '15 kg', detail: 'Menyediakan sekitar 30 porsi makanan gratis bagi yayasan penerima manfaat.' },
                            { label: 'Selasa', value: '8 kg', detail: 'Menyelamatkan setara dengan 16 piring makanan segar siap konsumsi.' },
                            { label: 'Rabu', value: '19 kg', detail: 'Membantu menekan emisi gas metana sebanyak 38 kg CO2 ekivalen.' },
                            { label: 'Kamis', value: '12 kg', detail: 'Penyaluran donasi skala menengah sukses untuk Panti Asuhan lokal.' },
                            { label: 'Jumat', value: '25 kg', detail: 'Penyaluran berkah sedekah Jumat berupa nasi kotak bergizi seimbang.' },
                            { label: 'Sabtu', value: '14 kg', detail: 'Penyelamatan makanan berjalan lancar untuk kelompok pemukiman marginal.' },
                            { label: 'Minggu', value: '30 kg', detail: 'Puncak dampak sosial! 60 porsi makanan surplus didistribusikan habis.' }
                        ]
                    },
                    monthly: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                        data: [120, 95, 180, 140, 230, 190],
                        label: 'Makanan Terselamatkan (kg)',
                        color: '#c5a880',
                        highlights: [
                            { label: 'Januari', value: '120 kg', detail: 'Menyelamatkan makanan senilai total Rp 3.000.000 dari pembuangan sampah.' },
                            { label: 'Februari', value: '95 kg', detail: 'Mencegah pembuangan makanan setara dengan 190 porsi layak saji.' },
                            { label: 'Maret', value: '180 kg', detail: 'Membantu 5 panti jompo setempat mendapatkan suplai makanan surplus higienis.' },
                            { label: 'April', value: '140 kg', detail: 'Penyaluran donasi berjalan stabil untuk sahur & buka puasa.' },
                            { label: 'Mei', value: '230 kg', detail: 'Kolaborasi sukses dengan 3 aliansi lembaga sosial baru di sekitar restoran.' },
                            { label: 'Juni', value: '190 kg', detail: 'Pencapaian dampak pertengahan tahun yang luar biasa bagi ketahanan pangan lokal.' }
                        ]
                    }
                }
            },

            init() {
                this.$nextTick(() => {
                    const ctx = document.getElementById('mitraPerformanceChart').getContext('2d');
                    const config = this.getChartConfig();
                    this.chartInstance = new Chart(ctx, config);
                });
            },

            setMetric(metric) {
                this.metric = metric;
                this.showOverlay = false;
                this.updateChart();
            },

            setTimeframe(timeframe) {
                this.timeframe = timeframe;
                this.showOverlay = false;
                this.updateChart();
            },

            setActiveHighlight(index, x, y) {
                const current = this.chartData[this.metric][this.timeframe];
                if (current.highlights && current.highlights[index]) {
                    this.overlayTitle = current.highlights[index].label;
                    this.overlayValue = current.highlights[index].value;
                    this.overlayDetail = current.highlights[index].detail;
                    this.overlayLeft = x;
                    this.overlayTop = y;
                    this.showOverlay = true;
                    
                    // Trigger Lucide to render icons inside overlay if needed
                    setTimeout(() => {
                        if (window.lucide) window.lucide.createIcons();
                    }, 10);
                }
            },

            getChartConfig() {
                const current = this.chartData[this.metric][this.timeframe];
                return {
                    type: 'line',
                    data: {
                        labels: current.labels,
                        datasets: [{
                            label: current.label,
                            data: current.data,
                            borderColor: current.color,
                            backgroundColor: current.color + '18', // transparent fill
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3,
                            pointBackgroundColor: current.color,
                            pointHoverRadius: 9,
                            pointRadius: 5,
                            pointHoverBackgroundColor: current.color,
                            pointHoverBorderColor: '#ffffff',
                            pointHoverBorderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        onHover: (event, activeElements) => {
                            if (activeElements && activeElements.length > 0) {
                                const index = activeElements[0].index;
                                const element = activeElements[0].element;
                                this.setActiveHighlight(index, element.x, element.y);
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: false // Disable default tooltips to show custom HTML overlay
                            }
                        },
                        scales: {
                            y: {
                                grid: {
                                    color: 'rgba(23, 68, 19, 0.05)'
                                },
                                ticks: {
                                    font: { size: 10, weight: 'bold' },
                                    color: '#8c9597',
                                    callback: (value) => {
                                        if (this.metric === 'revenue') {
                                            if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                                            if (value >= 1000) return 'Rp ' + (value / 1000) + 'rb';
                                            return 'Rp ' + value;
                                        }
                                        return value + ' kg';
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: { size: 10, weight: 'bold' },
                                    color: '#8c9597'
                                }
                            }
                        }
                    }
                };
            },

            updateChart() {
                if (!this.chartInstance) return;
                const current = this.chartData[this.metric][this.timeframe];
                
                // Update datasets
                this.chartInstance.data.labels = current.labels;
                this.chartInstance.data.datasets[0].label = current.label;
                this.chartInstance.data.datasets[0].data = current.data;
                this.chartInstance.data.datasets[0].borderColor = current.color;
                this.chartInstance.data.datasets[0].backgroundColor = current.color + '18';
                this.chartInstance.data.datasets[0].pointBackgroundColor = current.color;
                this.chartInstance.data.datasets[0].pointHoverBackgroundColor = current.color;
                
                this.chartInstance.update();
            }
        }));
    };

    if (window.Alpine) {
        initChart();
    } else {
        document.addEventListener('alpine:init', initChart);
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endsection
