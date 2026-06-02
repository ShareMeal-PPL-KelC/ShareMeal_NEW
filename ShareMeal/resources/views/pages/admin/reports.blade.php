@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12 reveal">
        <div>
            <h1 class="text-5xl font-serif font-bold text-luxury-forest leading-tight">{{ $shell['title'] }}</h1>
            <p class="text-luxury-slate font-medium mt-2 tracking-wide">{{ $shell['subtitle'] }}</p>
        </div>
        <div class="flex gap-4">
            <button onclick="alert('Laporan PDF sedang dibuat...')" class="bg-white/80 text-luxury-forest px-6 py-3.5 border border-luxury-alabas/85 rounded-2xl shadow-sm hover:bg-white transition flex items-center gap-2 font-bold text-xs uppercase tracking-wider cursor-pointer active:scale-95">
                <i data-lucide="file-text" class="w-4 h-4 text-luxury-gold"></i>
                Export PDF
            </button>
            <button onclick="alert('Laporan Excel sedang dibuat...')" class="bg-[#174413] text-white px-6 py-3.5 rounded-2xl shadow-xl shadow-green-100 hover:opacity-90 transition flex items-center gap-2 font-bold text-xs uppercase tracking-wider cursor-pointer active:scale-95">
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
                    <select class="text-xs border-luxury-alabas bg-white/80 rounded-xl px-4 py-2 font-bold text-luxury-forest outline-none focus:ring-2 focus:ring-[#174413] transition-all">
                        <option>Tahun 2024</option>
                        <option>Tahun 2023</option>
                    </select>
                </div>
                
                <!-- Simple CSS Chart -->
                <div class="h-64 flex items-end justify-between gap-4 px-2">
                    @foreach($monthlyData as $data)
                    <div class="flex-1 flex flex-col items-center gap-2 group">
                        <div class="w-full bg-gray-50/50 rounded-t-2xl border border-luxury-alabas relative flex items-end justify-center h-48 overflow-hidden">
                            <!-- Target Line (Simulated) -->
                            <div class="absolute bottom-[50%] w-full border-t border-dashed border-gray-300 z-0"></div>
                            
                            <!-- Actual Bar -->
                            <div class="w-3/4 bg-[#174413] rounded-t-xl transition-all duration-500 group-hover:bg-luxury-gold relative z-10" 
                                 style="height: {{ ($data['saved'] / 2500) * 100 }}%">
                                <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[10px] py-1.5 px-2 rounded-xl whitespace-nowrap transition-opacity shadow-md">
                                    {{ $data['saved'] }} Kg
                                </div>
                            </div>
                        </div>
                        <span class="text-[10px] font-black text-luxury-slate uppercase tracking-wider mt-1">{{ $data['month'] }}</span>
                    </div>
                    @endforeach
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
                    <a href="#" class="text-xs font-black uppercase tracking-widest text-green-600 hover:text-green-700 transition">Lihat Semua</a>
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
                    <button class="w-full mt-6 py-4 bg-white text-[#174413] rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] hover:bg-green-50 transition active:scale-95 shadow-md">
                        Lihat Analisis Detail
                    </button>
                </div>
            </div>

            <!-- Top Contributors -->
            <div class="glass-card p-8 rounded-[2.5rem] text-left reveal bg-white/20">
                <h3 class="font-serif text-xl font-bold text-luxury-forest mb-6">Kontributor Terbesar</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between group cursor-pointer p-4 bg-white/40 border border-luxury-alabas rounded-[1.5rem] hover:bg-white hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-green-50 border border-green-100 flex items-center justify-center font-black text-xs text-green-700 shadow-sm">1</div>
                            <div>
                                <div class="text-sm font-bold text-luxury-forest group-hover:text-green-600 transition-colors">Toko Roti Sejahtera</div>
                                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-1">1.250 Kg Penyelamatan</div>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 group-hover:text-green-600 transition"></i>
                    </div>
                    <div class="flex items-center justify-between group cursor-pointer p-4 bg-white/40 border border-luxury-alabas rounded-[1.5rem] hover:bg-white hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center font-black text-xs text-orange-700 shadow-sm">2</div>
                            <div>
                                <div class="text-sm font-bold text-luxury-forest group-hover:text-green-600 transition-colors">Healthy Cafe</div>
                                <div class="text-[10px] text-luxury-slate font-black uppercase tracking-wider mt-1">980 Kg Penyelamatan</div>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 group-hover:text-green-600 transition"></i>
                    </div>
                    <div class="flex items-center justify-between group cursor-pointer p-4 bg-white/40 border border-luxury-alabas rounded-[1.5rem] hover:bg-white hover:shadow-md transition-all duration-300">
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
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endsection
