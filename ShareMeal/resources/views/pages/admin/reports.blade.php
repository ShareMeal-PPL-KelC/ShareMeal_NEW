@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen pb-20">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-green-700 font-bold text-xs uppercase tracking-[0.2em]">
                <span class="w-8 h-[2px] bg-green-700"></span>
                Impact Analytics
            </div>
            <h1 class="text-4xl font-black text-gray-900 tracking-tight leading-none">
                Laporan <span class="text-green-700">Distribusi</span>
            </h1>
            <p class="text-gray-500 font-medium">Pantau efektivitas pengurangan food waste dan dampak sosial (FR-18)</p>
        </div>
        <div class="flex gap-3">
            <button class="bg-white text-gray-700 px-6 py-4 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all flex items-center gap-2 font-bold text-sm">
                <i data-lucide="download" class="w-4 h-4"></i>
                Export PDF
            </button>
            <button class="bg-[#1a4414] text-white px-6 py-4 rounded-2xl shadow-lg shadow-green-900/20 hover:scale-105 transition-all flex items-center gap-2 font-bold text-sm">
                <i data-lucide="calendar" class="w-4 h-4"></i>
                Filter Periode
            </button>
        </div>
    </div>

    <!-- Impact Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        @php
            $impactStats = [
                ['label' => 'Food Saved', 'value' => $stats['total_food_saved'], 'icon' => 'leaf', 'color' => 'green', 'trend' => '+12%'],
                ['label' => 'CO2 Reduction', 'value' => $stats['co2_reduction'], 'icon' => 'wind', 'color' => 'blue', 'trend' => '+8%'],
                ['label' => 'Meals Distributed', 'value' => $stats['meals_distributed'], 'icon' => 'heart', 'color' => 'red', 'trend' => '+15%'],
                ['label' => 'Social Impact', 'value' => $stats['impact_value'], 'icon' => 'trending-up', 'color' => 'orange', 'trend' => '+20%'],
            ];
        @endphp

        @foreach($impactStats as $stat)
        <div class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-{{ $stat['color'] }}-50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="relative z-10 flex flex-col gap-4">
                <div class="w-12 h-12 bg-{{ $stat['color'] }}-50 rounded-2xl flex items-center justify-center">
                    <i data-lucide="{{ $stat['icon'] }}" class="w-6 h-6 text-{{ $stat['color'] }}-600"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ $stat['label'] }}</p>
                    <h3 class="text-3xl font-black text-gray-900 mt-1">{{ $stat['value'] }}</h3>
                    <div class="flex items-center gap-1 mt-2">
                        <span class="text-green-500 text-[10px] font-black">{{ $stat['trend'] }}</span>
                        <span class="text-[10px] text-gray-400 font-bold">vs bulan lalu</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Chart Section -->
        <div class="lg:col-span-2 bg-white rounded-[40px] border border-gray-100 shadow-sm p-10">
            <div class="flex justify-between items-center mb-10">
                <div>
                    <h3 class="text-2xl font-black text-gray-900">Statistik Penyelamatan Makanan</h3>
                    <p class="text-sm text-gray-500 font-medium">Perbandingan bulanan (Kg)</p>
                </div>
                <select class="bg-gray-50 border-none rounded-xl text-sm font-bold px-4 py-2">
                    <option>Tahun 2024</option>
                    <option>Tahun 2023</option>
                </select>
            </div>
            
            <div class="h-[350px] flex items-end justify-between gap-4">
                @foreach($monthlyData as $data)
                <div class="flex-1 flex flex-col items-center gap-4 group">
                    <div class="w-full relative flex flex-col items-center justify-end h-[300px]">
                        <!-- Target Bar -->
                        <div class="absolute bottom-0 w-full bg-gray-50 rounded-2xl" style="height: {{ ($data['target']/2500)*100 }}%"></div>
                        <!-- Actual Bar -->
                        <div class="relative w-2/3 bg-green-700 rounded-2xl group-hover:bg-green-600 transition-all shadow-lg shadow-green-900/10" 
                             style="height: {{ ($data['saved']/2500)*100 }}%">
                            <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[10px] font-black px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                {{ $data['saved'] }} Kg
                            </div>
                        </div>
                    </div>
                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest">{{ $data['month'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Waste Reduction Info -->
        <div class="bg-gradient-to-br from-[#1a4414] to-[#2d5a26] rounded-[40px] p-10 text-white flex flex-col justify-between">
            <div class="space-y-6">
                <div class="w-16 h-16 bg-white/10 rounded-3xl flex items-center justify-center">
                    <i data-lucide="bar-chart-3" class="w-8 h-8 text-white"></i>
                </div>
                <div>
                    <h3 class="text-3xl font-black leading-tight">Food Waste Reduction Rate</h3>
                    <p class="text-white/60 font-medium mt-2 text-sm leading-relaxed">Persentase keberhasilan dalam mengurangi limbah makanan melalui platform ShareMeal bulan ini.</p>
                </div>
            </div>

            <div class="text-center py-10 relative">
                <div class="text-8xl font-black opacity-10 absolute inset-0 flex items-center justify-center">
                    {{ $stats['waste_reduction_rate'] }}%
                </div>
                <div class="relative z-10">
                    <span class="text-7xl font-black tracking-tighter">{{ $stats['waste_reduction_rate'] }}</span>
                    <span class="text-3xl font-black text-green-400">%</span>
                </div>
            </div>

            <button class="w-full py-5 bg-white text-[#1a4414] rounded-2xl font-black text-sm hover:bg-green-50 transition-colors shadow-xl">
                LIHAT DETAIL ANALITIK
            </button>
        </div>
    </div>

    <!-- Recent Distribution Table -->
    <div class="mt-10 bg-white rounded-[40px] border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex justify-between items-center">
            <h3 class="text-xl font-black text-gray-900">Aktivitas Distribusi Terbaru</h3>
            <a href="#" class="text-sm font-bold text-green-700 hover:underline">Lihat Semua History</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Mitra Pengirim</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Lembaga Penerima</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Detail Item</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($distributions as $dist)
                    <tr class="hover:bg-gray-50/30 transition-colors group">
                        <td class="px-8 py-6">
                            <div class="font-bold text-gray-900">{{ $dist->mitra }}</div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">Verified Mitra</div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="font-bold text-gray-700">{{ $dist->lembaga }}</div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-bold text-gray-900">{{ $dist->items }}</span>
                                <span class="text-[10px] text-green-600 font-black px-2 py-0.5 bg-green-50 rounded-full w-max">{{ $dist->quantity }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $dist->status === 'Diterima' ? 'bg-green-500 text-white' : 'bg-orange-500 text-white' }}">
                                {{ $dist->status }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-sm font-bold text-gray-500">{{ $dist->date }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
