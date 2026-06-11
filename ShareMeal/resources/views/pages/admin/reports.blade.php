@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="{
    selectedYear: '2026',
    isImpactDialogOpen: false,
    isDistributionsDialogOpen: false,
    isContributorDialogOpen: false,
    selectedContributor: {},
    isExporting: false,
    exportType: '',
    
    chartData: {
        '2025': [
            { month: 'Jan', saved: 920, target: 1000 },
            { month: 'Feb', saved: 1100, target: 1000 },
            { month: 'Mar', saved: 1350, target: 1000 },
            { month: 'Apr', saved: 1600, target: 1000 },
            { month: 'Mei', saved: 1950, target: 1000 }
        ],
        '2026': [
            { month: 'Jan', saved: 1150, target: 1000 },
            { month: 'Feb', saved: 1400, target: 1000 },
            { month: 'Mar', saved: 1850, target: 1000 },
            { month: 'Apr', saved: 2200, target: 1000 },
            { month: 'Mei', saved: 2500, target: 1000 }
        ]
    },
    
    get activeChart() {
        return this.chartData[this.selectedYear];
    },
    
    openContributor(name, rank, amount, type) {
        this.selectedContributor = { name, rank, amount, type };
        this.isContributorDialogOpen = true;
    },
    
    async triggerExport(type, url) {
        this.exportType = type;
        this.isExporting = true;
        
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Gagal mengekspor laporan');
            
            const blob = await response.blob();
            const downloadUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = downloadUrl;
            a.download = type === 'PDF' ? 'Laporan_Distribusi_ShareMeal.pdf' : 'Laporan_Distribusi_ShareMeal.xls';
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(downloadUrl);
        } catch (error) {
            alert(error.message);
        } finally {
            setTimeout(() => {
                this.isExporting = false;
            }, 1000);
        }
    }
}">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12 reveal">
        <div>
            <h1 class="text-5xl font-serif font-bold text-luxury-forest leading-tight">{{ $shell['title'] }}</h1>
            <p class="text-luxury-slate font-medium mt-2 tracking-wide">{{ $shell['subtitle'] }}</p>
        </div>
        <div class="flex gap-4">
            <button type="button" @click="triggerExport('PDF', '{{ route('admin.reports.export-pdf') }}')" class="bg-white/80 text-luxury-forest px-6 py-3.5 border border-luxury-alabas/85 rounded-2xl shadow-sm hover:bg-white transition flex items-center gap-2 font-bold text-xs uppercase tracking-wider cursor-pointer active:scale-95">
                <i data-lucide="file-text" class="w-4 h-4 text-luxury-gold"></i>
                Export PDF
            </button>
            <button type="button" @click="triggerExport('Excel', '{{ route('admin.reports.export-excel') }}')" class="bg-[#174413] text-white px-6 py-3.5 rounded-2xl shadow-xl shadow-green-100 hover:opacity-90 transition flex items-center gap-2 font-bold text-xs uppercase tracking-wider cursor-pointer active:scale-95">
                <i data-lucide="download" class="w-4 h-4 text-white"></i>
                Export Excel
            </button>
        </div>
    </div>

    <!-- Impact Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group transition-all duration-500 reveal text-left">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-50 text-green-600 border border-green-100 rounded-xl group-hover:scale-110 group-hover:bg-green-100 transition-all duration-300">
                    <i data-lucide="package" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider">Total Makanan Terselamatkan</div>
                    <div class="text-2xl font-serif font-black text-luxury-forest mt-1">{{ $stats['total_food_saved'] }}</div>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1.5 text-xs text-green-650 font-bold uppercase tracking-wider">
                <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                <span>+12% dari bulan lalu</span>
            </div>
        </div>

        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group transition-all duration-500 reveal text-left">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-50 text-blue-600 border border-blue-100 rounded-xl group-hover:scale-110 group-hover:bg-blue-100 transition-all duration-300">
                    <i data-lucide="wind" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider">Reduksi Emisi CO2</div>
                    <div class="text-2xl font-serif font-black text-luxury-forest mt-1">{{ $stats['co2_reduction'] }}</div>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1.5 text-xs text-blue-650 font-bold uppercase tracking-wider">
                <i data-lucide="leaf" class="w-3.5 h-3.5"></i>
                <span>Setara 1.250 pohon</span>
            </div>
        </div>

        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group transition-all duration-500 reveal text-left">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-orange-50 text-orange-600 border border-orange-100 rounded-xl group-hover:scale-110 group-hover:bg-orange-100 transition-all duration-300">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider">Porsi Terdistribusi</div>
                    <div class="text-2xl font-serif font-black text-luxury-forest mt-1">{{ $stats['meals_distributed'] }}</div>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1.5 text-xs text-orange-650 font-bold uppercase tracking-wider">
                <i data-lucide="heart" class="w-3.5 h-3.5"></i>
                <span>Membantu 45 Lembaga</span>
            </div>
        </div>

        <div class="glass-card glass-card-hover p-6 rounded-[2rem] group transition-all duration-500 reveal text-left">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-purple-50 text-purple-650 border border-purple-100 rounded-xl group-hover:scale-110 group-hover:bg-purple-100 transition-all duration-300">
                    <i data-lucide="banknote" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider">Estimasi Nilai Ekonomi</div>
                    <div class="text-2xl font-serif font-black text-luxury-forest mt-1">{{ $stats['impact_value'] }}</div>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1.5 text-xs text-purple-600 font-bold uppercase tracking-wider">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                <span>Efisiensi Rantai Makanan</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Chart Section -->
        <div class="lg:col-span-2 space-y-8">
            <div class="glass-card rounded-[2.5rem] p-8 shadow-sm reveal bg-white/20">
                <div class="flex justify-between items-center mb-8 border-b border-luxury-alabas/60 pb-6">
                    <div>
                        <h2 class="font-serif text-2xl font-bold text-luxury-forest">Tren Penyelamatan Makanan</h2>
                        <p class="text-xs text-luxury-slate font-medium mt-1">Data akumulatif 5 bulan terakhir (Kg)</p>
                    </div>
                    <select x-model="selectedYear" class="text-xs border-luxury-alabas bg-white/80 rounded-xl px-4 py-2 font-bold text-luxury-forest outline-none focus:ring-2 focus:ring-[#174413] transition-all">
                        <option value="2026">Tahun 2026</option>
                        <option value="2025">Tahun 2025</option>
                    </select>
                </div>
                
                <!-- Simple CSS Chart -->
                <div class="h-64 flex items-end justify-between gap-4 px-2">
                    <template x-for="item in activeChart" :key="item.month">
                        <div class="flex-1 flex flex-col items-center gap-2 group">
                            <div class="w-full bg-gray-50/50 rounded-t-2xl border border-luxury-alabas relative flex items-end justify-center h-48 overflow-hidden">
                                <!-- Target Line (Simulated) -->
                                <div class="absolute bottom-[50%] w-full border-t border-dashed border-gray-300 z-0"></div>
                                
                                <!-- Actual Bar -->
                                <div class="w-3/4 bg-[#174413] rounded-t-xl transition-all duration-500 group-hover:bg-luxury-gold relative z-10" 
                                     :style="'height: ' + ((item.saved / 2500) * 100) + '%'">
                                    <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[10px] py-1.5 px-2 rounded-xl whitespace-nowrap transition-opacity shadow-md">
                                        <span x-text="item.saved"></span> Kg
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] font-black text-luxury-slate uppercase tracking-wider mt-1" x-text="item.month"></span>
                        </div>
                    </template>
                </div>
                <div class="mt-6 flex justify-center gap-6">
                    <div class="flex items-center gap-2 text-xs font-bold text-luxury-slate uppercase tracking-wider">
                        <span class="w-3.5 h-3.5 bg-[#174413] rounded-md"></span> Penyelamatan (Kg)
                    </div>
                    <div class="flex items-center gap-2 text-xs font-bold text-luxury-slate uppercase tracking-wider">
                        <span class="w-3.5 h-1 border-t border-dashed border-gray-300"></span> Target (1.000 Kg)
                    </div>
                </div>
            </div>

            <!-- Distribution Details Table -->
            <div class="glass-card border border-luxury-alabas rounded-[2.5rem] shadow-sm overflow-hidden bg-white/20">
                <div class="p-8 border-b border-luxury-alabas/60 bg-white/30 flex justify-between items-center">
                    <h2 class="font-serif text-2xl font-bold text-luxury-forest">Rincian Penyaluran Terbaru</h2>
                    <button type="button" @click="isDistributionsDialogOpen = true" class="text-xs font-black uppercase tracking-widest text-green-600 hover:text-green-700 transition cursor-pointer active:scale-95">Lihat Semua</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50/50 text-luxury-slate font-black text-[10px] uppercase tracking-widest">
                            <tr>
                                <th class="px-6 py-4">Mitra & Lembaga</th>
                                <th class="px-6 py-4">Item Makanan</th>
                                <th class="px-6 py-4">Jumlah</th>
                                <th class="px-6 py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-luxury-alabas/50">
                            @foreach($distributions as $dist)
                            <tr class="hover:bg-white/45 transition-colors duration-300">
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-luxury-forest text-sm">{{ $dist->mitra }}</span>
                                        <span class="text-xs text-luxury-slate font-medium flex items-center gap-1 mt-1">
                                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-luxury-gold"></i> {{ $dist->lembaga }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-gray-700 font-medium">
                                    {{ $dist->items }}
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="font-black text-luxury-forest text-sm">{{ $dist->quantity }}</span>
                                        <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-white/80 text-luxury-slate w-fit mt-1 border border-luxury-alabas">{{ $dist->type }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    @if($dist->status === 'Diterima' || $dist->status === 'Terjual')
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-green-50 text-green-700 border border-green-100 shadow-sm">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i> {{ $dist->status }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-orange-50 text-orange-700 border border-orange-100 shadow-sm animate-pulse">
                                            <i data-lucide="truck" class="w-3.5 h-3.5"></i> {{ $dist->status }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Info Section -->
        <div class="space-y-8">
            <!-- Waste Reduction Progress -->
            <div class="glass-card p-8 rounded-[2.5rem] text-left reveal bg-white/20">
                <h3 class="font-serif text-xl font-bold text-luxury-forest mb-6 flex items-center gap-2">
                    <i data-lucide="target" class="w-5 h-5 text-green-600 animate-pulse"></i>
                    Target Food Waste 2024
                </h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-xs font-bold uppercase tracking-wider mb-2 text-luxury-slate">
                            <span>Pencapaian Saat Ini</span>
                            <span class="text-[#174413]">{{ $stats['waste_reduction_rate'] }}%</span>
                        </div>
                        <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden p-0.5 border border-luxury-alabas">
                            <div class="h-full bg-[#174413] rounded-full transition-all duration-1000" style="width: {{ $stats['waste_reduction_rate'] }}%"></div>
                        </div>
                        <p class="text-[10px] text-luxury-slate/60 mt-3 italic font-medium leading-relaxed">*Target reduksi food waste nasional adalah 30% pada tahun 2025.</p>
                    </div>
                    
                    <div class="pt-4 border-t border-luxury-alabas/60 grid grid-cols-2 gap-4">
                        <div class="text-center p-3 bg-white/40 border border-luxury-alabas rounded-2xl shadow-sm">
                            <div class="text-xl font-bold text-luxury-forest font-serif leading-none">12.5t</div>
                            <div class="text-[9px] text-luxury-slate font-black uppercase tracking-wider mt-2">Total Saved</div>
                        </div>
                        <div class="text-center p-3 bg-white/40 border border-luxury-alabas rounded-2xl shadow-sm">
                            <div class="text-xl font-bold text-luxury-forest font-serif leading-none">5.2t</div>
                            <div class="text-[9px] text-luxury-slate font-black uppercase tracking-wider mt-2">Remaining</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Impact Summary -->
            <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-[#174413] to-[#2a6b23] p-8 text-white shadow-lg text-left reveal">
                <!-- Internal Glow Blobs -->
                <div class="absolute top-[-30%] left-[-15%] w-[18rem] h-[18rem] bg-emerald-400/20 rounded-full blur-[70px] pointer-events-none"></div>
                <div class="absolute bottom-[-30%] right-[-15%] w-[20rem] h-[20rem] bg-lime-400/15 rounded-full blur-[80px] pointer-events-none"></div>

                <div class="relative z-10">
                    <h3 class="font-serif text-2xl font-bold mb-2">Dampak Lingkungan</h3>
                    <p class="text-green-100 text-xs mb-6 font-medium leading-relaxed opacity-90">
                        Setiap kilogram makanan yang Anda selamatkan setara dengan menghemat 2.5kg emisi karbon global.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 bg-white/10 p-3 rounded-2xl border border-white/5 shadow-sm">
                            <div class="p-2 bg-white/20 rounded-xl text-luxury-gold">
                                <i data-lucide="droplet" class="w-4 h-4"></i>
                            </div>
                            <div class="text-xs">
                                <div class="font-bold text-sm text-white">15.2M Liter</div>
                                <div class="opacity-70 text-[9px] font-black uppercase tracking-wider mt-0.5">Air terselamatkan</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 bg-white/10 p-3 rounded-2xl border border-white/5 shadow-sm">
                            <div class="p-2 bg-white/20 rounded-xl text-luxury-gold">
                                <i data-lucide="layout" class="w-4 h-4"></i>
                            </div>
                            <div class="text-xs">
                                <div class="font-bold text-sm text-white">4.2 Hektar</div>
                                <div class="opacity-70 text-[9px] font-black uppercase tracking-wider mt-0.5">Lahan pertanian efisien</div>
                            </div>
                        </div>
                    </div>
                    <button @click="isImpactDialogOpen = true" class="w-full mt-6 py-4 bg-white text-[#174413] rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] hover:bg-green-50 transition active:scale-95 shadow-md cursor-pointer">
                        Lihat Analisis Detail
                    </button>
                </div>
            </div>

            <!-- Top Contributors -->
            <div class="glass-card p-8 rounded-[2.5rem] text-left reveal bg-white/20">
                <h3 class="font-serif text-xl font-bold text-luxury-forest mb-6">Kontributor Terbesar</h3>
                <div class="space-y-4">
                    <div @click="openContributor('Toko Roti Sejahtera', 1, '1.250 Kg', 'Mitra Bakery')" class="flex items-center justify-between group cursor-pointer p-4 bg-white/40 border border-luxury-alabas rounded-[1.5rem] hover:bg-white hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-green-50 border border-green-100 flex items-center justify-center font-black text-xs text-green-700 shadow-sm">1</div>
                            <div>
                                <div class="text-sm font-bold text-luxury-forest group-hover:text-green-600 transition-colors">Toko Roti Sejahtera</div>
                                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-1">1.250 Kg Penyelamatan</div>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 group-hover:text-green-600 transition"></i>
                    </div>
                    <div @click="openContributor('Healthy Cafe', 2, '980 Kg', 'Mitra F&B')" class="flex items-center justify-between group cursor-pointer p-4 bg-white/40 border border-luxury-alabas rounded-[1.5rem] hover:bg-white hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center font-black text-xs text-orange-700 shadow-sm">2</div>
                            <div>
                                <div class="text-sm font-bold text-luxury-forest group-hover:text-green-600 transition-colors">Healthy Cafe</div>
                                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-1">980 Kg Penyelamatan</div>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 group-hover:text-green-600 transition"></i>
                    </div>
                    <div @click="openContributor('Bakery Delight', 3, '750 Kg', 'Mitra Patisserie')" class="flex items-center justify-between group cursor-pointer p-4 bg-white/40 border border-luxury-alabas rounded-[1.5rem] hover:bg-white hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center font-black text-xs text-purple-700 shadow-sm">3</div>
                            <div>
                                <div class="text-sm font-bold text-luxury-forest group-hover:text-green-600 transition-colors">Bakery Delight</div>
                                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-1">750 Kg Penyelamatan</div>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 group-hover:text-green-600 transition"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Impact Analysis Modal -->
    <div x-show="isImpactDialogOpen" 
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-[#0d1f0d]/60 backdrop-blur-md" @click="isImpactDialogOpen = false"></div>

        <!-- Panel -->
        <div x-show="isImpactDialogOpen"
             x-transition:enter="ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 translate-y-10 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-10 scale-95"
             class="relative w-full max-w-2xl bg-white rounded-[3rem] shadow-2xl z-10 overflow-hidden border border-white/70 p-10 md:p-12 text-left"
             @click.stop>
            
            <button type="button" @click="isImpactDialogOpen = false" 
                    class="absolute top-6 right-6 w-10 h-10 rounded-full bg-luxury-ivory hover:bg-luxury-alabas text-luxury-slate hover:text-luxury-forest transition-all flex items-center justify-center border border-luxury-alabas shadow-sm cursor-pointer active:scale-95 z-20">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <h3 class="text-3xl font-serif font-black text-luxury-forest mb-2">Analisis Dampak Lingkungan</h3>
            <p class="text-xs text-luxury-slate font-medium mb-8">Rincian metrik kontribusi penyelamatan makanan terhadap pelestarian lingkungan hidup.</p>
            
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-5 bg-emerald-50/50 border border-emerald-100 rounded-2xl">
                        <div class="text-[10px] text-emerald-800 font-black uppercase tracking-wider">Air Terselamatkan</div>
                        <div class="text-2xl font-serif font-black text-luxury-forest mt-1">15.2M Liter</div>
                        <p class="text-[10px] text-luxury-slate mt-2 leading-relaxed">Menghemat air bersih yang biasanya terbuang dalam siklus produksi makanan secara regional.</p>
                    </div>
                    <div class="p-5 bg-blue-50/50 border border-blue-100 rounded-2xl">
                        <div class="text-[10px] text-blue-800 font-black uppercase tracking-wider">Lahan Pertanian Efisien</div>
                        <div class="text-2xl font-serif font-black text-luxury-forest mt-1">4.2 Hektar</div>
                        <p class="text-[10px] text-luxury-slate mt-2 leading-relaxed">Mengoptimalkan pemanfaatan lahan agar tidak terbuang sia-sia untuk penimbunan sampah makanan.</p>
                    </div>
                </div>

                <div class="p-6 bg-orange-50/50 border border-orange-100 rounded-2xl space-y-4">
                    <h4 class="font-bold text-luxury-forest text-sm">Metrik Reduksi Gas Rumah Kaca</h4>
                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between text-[10px] font-black uppercase tracking-wider text-luxury-slate mb-1">
                                <span>Karbon Dioksida (CO2) Terhindar (Target: 50 Ton)</span>
                                <span>62.5%</span>
                            </div>
                            <div class="w-full h-2 bg-gray-150 rounded-full overflow-hidden p-0.5 border border-luxury-alabas">
                                <div class="h-full bg-[#174413] rounded-full" style="width: 62.5%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-[10px] font-black uppercase tracking-wider text-luxury-slate mb-1">
                                <span>Metana (CH4) Terhindar</span>
                                <span>45.0%</span>
                            </div>
                            <div class="w-full h-2 bg-gray-150 rounded-full overflow-hidden p-0.5 border border-luxury-alabas">
                                <div class="h-full bg-orange-500 rounded-full" style="width: 45%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Distributions List Modal -->
    <div x-show="isDistributionsDialogOpen" 
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-[#0d1f0d]/60 backdrop-blur-md" @click="isDistributionsDialogOpen = false"></div>

        <!-- Panel -->
        <div x-show="isDistributionsDialogOpen"
             x-transition:enter="ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 translate-y-10 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-10 scale-95"
             class="relative w-full max-w-3xl bg-white rounded-[3rem] shadow-2xl z-10 overflow-hidden border border-white/70 p-10 md:p-12 text-left"
             @click.stop>
            
            <button type="button" @click="isDistributionsDialogOpen = false" 
                    class="absolute top-6 right-6 w-10 h-10 rounded-full bg-luxury-ivory hover:bg-luxury-alabas text-luxury-slate hover:text-luxury-forest transition-all flex items-center justify-center border border-luxury-alabas shadow-sm cursor-pointer active:scale-95 z-20">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <h3 class="text-3xl font-serif font-black text-luxury-forest mb-2">Rincian Penyaluran Lengkap</h3>
            <p class="text-xs text-luxury-slate font-medium mb-8">Daftar lengkap transaksi penyaluran makanan terselamatkan ke lembaga sosial dan konsumen.</p>
            
            <div class="max-h-[50vh] overflow-y-auto pr-2 custom-scrollbar space-y-4">
                @foreach($distributions as $dist)
                <div class="border border-luxury-alabas rounded-[1.5rem] p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white/45 hover:bg-white hover:shadow-md transition-all duration-300 group">
                    <div>
                        <span class="font-bold text-luxury-forest text-base">{{ $dist->mitra }}</span>
                        <div class="text-xs text-luxury-slate font-medium flex items-center gap-1 mt-1">
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-luxury-gold"></i> {{ $dist->lembaga }}
                        </div>
                        <p class="text-[10px] text-gray-400 font-mono mt-2 uppercase tracking-wide">Tanggal: {{ $dist->date }}</p>
                    </div>
                    <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end shrink-0">
                        <div class="text-right">
                            <span class="font-black text-luxury-forest text-sm block">{{ $dist->quantity }}</span>
                            <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-slate-50 text-luxury-slate border border-luxury-alabas mt-1 inline-block">{{ $dist->type }}</span>
                        </div>
                        <div>
                            @if($dist->status === 'Diterima' || $dist->status === 'Terjual')
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-green-50 text-green-700 border border-green-100 shadow-sm">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> {{ $dist->status }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-orange-50 text-orange-700 border border-orange-100 shadow-sm animate-pulse">
                                    <i data-lucide="truck" class="w-3.5 h-3.5"></i> {{ $dist->status }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Contributor Detail Modal -->
    <div x-show="isContributorDialogOpen" 
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-[#0d1f0d]/60 backdrop-blur-md" @click="isContributorDialogOpen = false"></div>

        <!-- Panel -->
        <div x-show="isContributorDialogOpen"
             x-transition:enter="ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 translate-y-10 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-10 scale-95"
             class="relative w-full max-w-md bg-white rounded-[3rem] shadow-2xl z-10 overflow-hidden border border-white/70 p-10 md:p-12 text-center"
             @click.stop>
            
            <button type="button" @click="isContributorDialogOpen = false" 
                    class="absolute top-6 right-6 w-10 h-10 rounded-full bg-luxury-ivory hover:bg-luxury-alabas text-luxury-slate hover:text-luxury-forest transition-all flex items-center justify-center border border-luxury-alabas shadow-sm cursor-pointer active:scale-95 z-20">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <!-- Rank Badge -->
            <div class="w-20 h-20 rounded-[2rem] bg-amber-50 border-2 border-amber-250 text-luxury-gold flex items-center justify-center mx-auto shadow-md mb-6">
                <i data-lucide="trophy" class="w-10 h-10 stroke-[2]"></i>
            </div>

            <span class="inline-block px-3 py-1 bg-emerald-50 border border-emerald-200/50 text-[10px] font-black text-emerald-700 rounded-full uppercase tracking-wider mb-2">Peringkat #<span x-text="selectedContributor.rank"></span> Kontributor</span>
            
            <h3 class="text-3xl font-serif font-black text-luxury-forest leading-tight mb-2" x-text="selectedContributor.name"></h3>
            <p class="text-xs text-luxury-slate font-medium uppercase tracking-widest" x-text="selectedContributor.type"></p>
            
            <div class="mt-8 p-5 bg-luxury-ivory/50 border border-luxury-alabas rounded-2xl flex justify-between items-center text-left">
                <div>
                    <span class="text-[10px] text-luxury-slate font-black uppercase tracking-wider block">Total Penyelamatan</span>
                    <span class="text-xl font-serif font-black text-luxury-forest mt-1 block" x-text="selectedContributor.amount"></span>
                </div>
                <div class="w-11 h-11 bg-white border border-luxury-alabas rounded-2xl flex items-center justify-center text-luxury-gold shadow-sm shrink-0">
                    <i data-lucide="leaf" class="w-5 h-5"></i>
                </div>
            </div>

            <p class="text-[11px] text-luxury-slate font-medium leading-relaxed mt-6">Kontributor ini secara konsisten menyumbangkan surplus makanannya secara terjadwal untuk didistribusikan ke panti asuhan dan lembaga sosial mitra platform ShareMeal.</p>
        </div>
    </div>

    <!-- Export Loading Modal -->
    <div x-show="isExporting" 
         class="fixed inset-0 z-[130] flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-[#0d1f0d]/60 backdrop-blur-md"></div>

        <!-- Panel -->
        <div class="relative w-full max-w-sm bg-white rounded-[3rem] shadow-2xl z-10 overflow-hidden border border-white/70 p-10 text-center">
            <div class="w-20 h-20 relative flex items-center justify-center mx-auto mb-6">
                <div class="absolute inset-0 border-4 border-luxury-alabas border-t-luxury-gold rounded-full animate-spin"></div>
                <i data-lucide="download" class="w-8 h-8 text-luxury-forest animate-pulse"></i>
            </div>
            <h4 class="text-2xl font-serif font-black text-luxury-forest mb-2">Mengekspor Laporan</h4>
            <p class="text-xs text-luxury-slate font-medium leading-relaxed">Menghasilkan dokumen <span x-text="exportType" class="font-black text-luxury-gold"></span> Anda. Harap tunggu sebentar...</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endsection
