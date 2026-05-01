@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">{{ $shell['title'] }}</h1>
            <p class="text-gray-500 mt-1">{{ $shell['subtitle'] }}</p>
        </div>
        <div class="flex gap-2">
            <button onclick="alert('Laporan PDF sedang dibuat...')" class="bg-white text-gray-700 px-4 py-2 border border-gray-200 rounded-xl shadow-sm hover:bg-gray-50 transition flex items-center gap-2 font-medium cursor-pointer">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                Export PDF
            </button>
            <button onclick="alert('Laporan Excel sedang dibuat...')" class="bg-[#174413] text-white px-4 py-2 rounded-xl shadow-sm hover:opacity-90 transition flex items-center gap-2 font-medium cursor-pointer">
                <i data-lucide="download" class="w-4 h-4"></i>
                Export Excel
            </button>
        </div>
    </div>

    <!-- Impact Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition group text-left">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-50 rounded-xl group-hover:bg-green-100 transition">
                    <i data-lucide="package" class="w-6 h-6 text-green-600"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500 font-medium">Total Makanan Terselamatkan</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['total_food_saved'] }}</div>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1 text-xs text-green-600 font-semibold">
                <i data-lucide="trending-up" class="w-3 h-3"></i>
                <span>+12% dari bulan lalu</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition group text-left">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-50 rounded-xl group-hover:bg-blue-100 transition">
                    <i data-lucide="wind" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500 font-medium">Reduksi Emisi CO2</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['co2_reduction'] }}</div>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1 text-xs text-blue-600 font-semibold">
                <i data-lucide="trending-up" class="w-3 h-3"></i>
                <span>Setara 1.250 pohon</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition group text-left">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-orange-50 rounded-xl group-hover:bg-orange-100 transition">
                    <i data-lucide="users" class="w-6 h-6 text-orange-600"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500 font-medium">Porsi Terdistribusi</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['meals_distributed'] }}</div>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1 text-xs text-orange-600 font-semibold">
                <i data-lucide="heart" class="w-3 h-3"></i>
                <span>Membantu 45 Lembaga Sosial</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition group text-left">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-purple-50 rounded-xl group-hover:bg-purple-100 transition">
                    <i data-lucide="banknote" class="w-6 h-6 text-purple-600"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500 font-medium">Estimasi Nilai Ekonomi</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['impact_value'] }}</div>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1 text-xs text-purple-600 font-semibold">
                <i data-lucide="shield-check" class="w-3 h-3"></i>
                <span>Efisiensi Rantai Makanan</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Chart Section -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Tren Penyelamatan Makanan</h2>
                        <p class="text-sm text-gray-500">Data akumulatif 5 bulan terakhir (Kg)</p>
                    </div>
                    <select class="text-sm border-gray-200 rounded-lg focus:ring-green-500 focus:border-green-500">
                        <option>Tahun 2024</option>
                        <option>Tahun 2023</option>
                    </select>
                </div>
                
                <!-- Simple CSS Chart -->
                <div class="h-64 flex items-end justify-between gap-4 px-2">
                    @foreach($monthlyData as $data)
                    <div class="flex-1 flex flex-col items-center gap-2 group">
                        <div class="w-full bg-gray-50 rounded-t-lg relative flex items-end justify-center h-48 overflow-hidden">
                            <!-- Target Line (Simulated) -->
                            <div class="absolute bottom-[50%] w-full border-t border-dashed border-gray-300 z-0"></div>
                            
                            <!-- Actual Bar -->
                            <div class="w-3/4 bg-[#174413] rounded-t-lg transition-all duration-500 group-hover:bg-[#2a6b23] relative z-10" 
                                 style="height: {{ ($data['saved'] / 2500) * 100 }}%">
                                <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[10px] py-1 px-2 rounded whitespace-nowrap transition-opacity">
                                    {{ $data['saved'] }} Kg
                                </div>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-gray-600 uppercase text-center">{{ $data['month'] }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="mt-6 flex justify-center gap-6">
                    <div class="flex items-center gap-2 text-xs font-medium text-gray-500">
                        <span class="w-3 h-3 bg-[#174413] rounded-sm"></span> Penyelamatan (Kg)
                    </div>
                    <div class="flex items-center gap-2 text-xs font-medium text-gray-500">
                        <span class="w-3 h-1 border-t border-dashed border-gray-300"></span> Target (1.000 Kg)
                    </div>
                </div>
            </div>

            <!-- Distribution Details Table -->
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-900">Rincian Penyaluran Terbaru</h2>
                    <a href="#" class="text-sm font-bold text-green-600 hover:text-green-700">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50/50 text-gray-500 font-semibold text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Mitra & Lembaga</th>
                                <th class="px-6 py-4">Item Makanan</th>
                                <th class="px-6 py-4">Jumlah</th>
                                <th class="px-6 py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($distributions as $dist)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900">{{ $dist->mitra }}</span>
                                        <span class="text-xs text-gray-500 flex items-center gap-1">
                                            <i data-lucide="arrow-right" class="w-3 h-3"></i> {{ $dist->lembaga }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $dist->items }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-gray-900">{{ $dist->quantity }}</span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 w-fit mt-1">{{ $dist->type }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($dist->status === 'Diterima' || $dist->status === 'Terjual')
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold bg-green-50 text-green-700 border border-green-100">
                                            <i data-lucide="check" class="w-3 h-3"></i> {{ $dist->status }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold bg-orange-50 text-orange-700 border border-orange-100">
                                            <i data-lucide="truck" class="w-3 h-3"></i> {{ $dist->status }}
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
        <div class="space-y-6">
            <!-- Waste Reduction Progress -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm text-left">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="target" class="w-5 h-5 text-green-600"></i>
                    Target Food Waste 2024
                </h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">Pencapaian Saat Ini</span>
                            <span class="font-bold text-[#174413]">{{ $stats['waste_reduction_rate'] }}%</span>
                        </div>
                        <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#174413] rounded-full transition-all duration-1000" style="width: {{ $stats['waste_reduction_rate'] }}%"></div>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-2 italic">*Target reduksi food waste nasional adalah 30% pada 2025.</p>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-50 grid grid-cols-2 gap-4">
                        <div class="text-center p-3 bg-gray-50 rounded-xl">
                            <div class="text-xl font-bold text-gray-900">12.5t</div>
                            <div class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Total Saved</div>
                        </div>
                        <div class="text-center p-3 bg-gray-50 rounded-xl">
                            <div class="text-xl font-bold text-gray-900">5.2t</div>
                            <div class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Remaining</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Impact Summary -->
            <div class="bg-gradient-to-br from-[#174413] to-[#2a6b23] p-6 rounded-2xl shadow-lg text-white relative overflow-hidden text-left">
                <i data-lucide="leaf" class="absolute -right-4 -bottom-4 w-32 h-32 opacity-10"></i>
                <h3 class="font-bold text-lg mb-2 relative z-10">Dampak Lingkungan</h3>
                <p class="text-green-100 text-sm mb-6 relative z-10 leading-relaxed">
                    Setiap kilogram makanan yang Anda selamatkan setara dengan menghemat 2.5kg emisi karbon.
                </p>
                <div class="space-y-4 relative z-10">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white/20 rounded-lg">
                            <i data-lucide="droplet" class="w-4 h-4"></i>
                        </div>
                        <div class="text-xs">
                            <div class="font-bold">15.2M Liter</div>
                            <div class="opacity-70 text-[10px]">Air terselamatkan</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white/20 rounded-lg">
                            <i data-lucide="layout" class="w-4 h-4"></i>
                        </div>
                        <div class="text-xs">
                            <div class="font-bold">4.2 Hektar</div>
                            <div class="opacity-70 text-[10px]">Lahan pertanian efisien</div>
                        </div>
                    </div>
                </div>
                <button class="w-full mt-6 py-2 bg-white text-[#174413] rounded-xl font-bold text-sm hover:bg-green-50 transition shadow-sm">
                    Lihat Analisis Detail
                </button>
            </div>

            <!-- Top Contributors -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm text-left">
                <h3 class="font-bold text-gray-900 mb-4">Kontributor Terbesar</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between group cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center font-bold text-xs text-gray-500">1</div>
                            <div>
                                <div class="text-sm font-bold text-gray-900 group-hover:text-green-600 transition">Toko Roti Sejahtera</div>
                                <div class="text-[10px] text-gray-400">1.250 Kg Penyelamatan</div>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 group-hover:text-green-600 transition"></i>
                    </div>
                    <div class="flex items-center justify-between group cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center font-bold text-xs text-gray-500">2</div>
                            <div>
                                <div class="text-sm font-bold text-gray-900 group-hover:text-green-600 transition">Healthy Cafe</div>
                                <div class="text-[10px] text-gray-400">980 Kg Penyelamatan</div>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 group-hover:text-green-600 transition"></i>
                    </div>
                    <div class="flex items-center justify-between group cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center font-bold text-xs text-gray-500">3</div>
                            <div>
                                <div class="text-sm font-bold text-gray-900 group-hover:text-green-600 transition">Bakery Delight</div>
                                <div class="text-[10px] text-gray-400">750 Kg Penyelamatan</div>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 group-hover:text-green-600 transition"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
