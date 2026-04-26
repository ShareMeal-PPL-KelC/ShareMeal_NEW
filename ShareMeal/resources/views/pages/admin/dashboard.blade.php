@extends('layouts.dashboard')

@section('content')
<div class="space-y-8 font-sans">
    <!-- 1. Statistik Atas (6 Cards) - Hardcoded Figma -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        @php
            $cardConfigs = [
                ['label' => 'Total User', 'value' => '1250', 'icon' => 'users', 'color' => 'blue'],
                ['label' => 'Pending', 'value' => '15', 'icon' => 'clock', 'color' => 'orange'],
                ['label' => 'Mitra Aktif', 'value' => '142', 'icon' => 'store', 'color' => 'green'],
                ['label' => 'Lembaga Aktif', 'value' => '38', 'icon' => 'heart', 'color' => 'purple'],
                ['label' => 'Transaksi', 'value' => '5420', 'icon' => 'shopping-cart', 'color' => 'indigo'],
                ['label' => 'Makanan Saved', 'value' => '12.5k', 'icon' => 'package', 'color' => 'teal'],
            ];
        @endphp

        @foreach($cardConfigs as $card)
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-{{ $card['color'] }}-50 rounded-lg text-{{ $card['color'] }}-600">
                    <i data-lucide="{{ $card['icon'] }}" class="w-4 h-4"></i>
                </div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $card['label'] }}</span>
            </div>
            <div class="text-xl font-extrabold text-gray-900">{{ $card['value'] }}</div>
        </div>
        @endforeach
    </div>

    <!-- 2. Alert Box (Figma Style) -->
    <div class="bg-orange-50 border border-orange-100 rounded-2xl p-5 flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-orange-100 rounded-xl text-orange-600">
                <i data-lucide="alert-circle" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="font-bold text-orange-900">15 Pendaftaran Menunggu Verifikasi (FR-18)</h4>
                <p class="text-sm text-orange-700/80 mt-0.5 text-balance">Terdapat 15 mitra dan lembaga sosial yang baru saja mendaftar dan membutuhkan peninjauan dokumen segera.</p>
            </div>
        </div>
        <a href="{{ route('admin.verification') }}" class="w-full md:w-auto px-6 py-2.5 bg-white text-orange-600 border border-orange-200 rounded-xl font-bold text-sm hover:bg-orange-600 hover:text-white transition text-center shadow-sm">
            Verifikasi Sekarang
        </a>
    </div>

    <!-- 3. Dua Kolom Utama -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Kolom Kiri: Pending Verifikasi (FR-18) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Pending Verifikasi (FR-18)</h3>
                <a href="{{ route('admin.verification') }}" class="text-sm font-bold text-green-600 hover:underline">Lihat Semua</a>
            </div>
            
            <div class="space-y-3">
                @forelse($pendingApplications as $app)
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between group hover:border-green-200 transition">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-gray-50 flex items-center justify-center font-bold text-gray-400 text-lg">
                            {{ substr($app->user->name, 0, 1) }}
                        </div>
                        <div>
                            <h5 class="font-bold text-gray-900 group-hover:text-green-600 transition">{{ $app->user->name }}</h5>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase {{ $app->user->role === 'mitra' ? 'bg-orange-100 text-orange-600' : 'bg-purple-100 text-purple-600' }}">
                                    {{ $app->user->role }}
                                </span>
                                <span class="text-xs text-gray-400">{{ $app->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('admin.verification') }}" class="px-4 py-2 bg-gray-50 text-gray-700 rounded-lg font-bold text-xs hover:bg-green-600 hover:text-white transition">
                        Review
                    </a>
                </div>
                @empty
                <!-- Dummy data for preview if empty -->
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between group">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center font-bold text-lg">T</div>
                        <div>
                            <h5 class="font-bold text-gray-900">Toko Roti Sejahtera</h5>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase bg-orange-100 text-orange-600">mitra</span>
                                <span class="text-xs text-gray-400">2 jam yang lalu</span>
                            </div>
                        </div>
                    </div>
                    <button class="px-4 py-2 bg-gray-50 text-gray-700 rounded-lg font-bold text-xs hover:bg-green-600 hover:text-white transition">Review</button>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Kolom Kanan: Aktivitas Terbaru -->
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-gray-900">Aktivitas Terbaru</h3>
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <div class="space-y-6">
                    @foreach($recentActivities as $activity)
                    <div class="flex gap-4">
                        <div class="relative">
                            <div class="h-8 w-8 rounded-full bg-{{ $activity['color'] }}-50 flex items-center justify-center text-{{ $activity['color'] }}-600 relative z-10 border-4 border-white">
                                <i data-lucide="{{ $activity['type'] === 'user' ? 'user-plus' : ($activity['type'] === 'order' ? 'shopping-bag' : ($activity['type'] === 'verify' ? 'shield-check' : 'alert-triangle')) }}" class="w-3.5 h-3.5"></i>
                            </div>
                            @if(!$loop->last)
                            <div class="absolute top-8 left-1/2 -translate-x-1/2 w-0.5 h-full bg-gray-50"></div>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 leading-snug">
                                <span class="font-bold text-gray-900">{{ $activity['user'] }}</span> {{ $activity['action'] }}
                            </p>
                            <span class="text-[10px] text-gray-400 font-medium uppercase tracking-tight">{{ $activity['time'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button class="w-full mt-6 py-2 text-sm font-bold text-gray-400 hover:text-gray-600 transition uppercase tracking-widest text-[10px]">Lihat Selengkapnya</button>
            </div>
        </div>
    </div>

    <!-- 4. Dampak Platform (Figma Dark Green Style) -->
    <div class="bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
        <h3 class="text-lg font-extrabold text-gray-900 mb-10 text-center uppercase tracking-[0.2em]">Dampak Platform</h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center space-y-2">
                <div class="text-4xl font-black text-[#174413]">12.500</div>
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Makanan Diselamatkan</div>
            </div>
            <div class="text-center space-y-2 border-l border-gray-100">
                <div class="text-4xl font-black text-[#174413]">31.250</div>
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">CO2 Dikurangi (KG)</div>
            </div>
            <div class="text-center space-y-2 border-l border-gray-100">
                <div class="text-4xl font-black text-[#174413]">5.420</div>
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Transaksi</div>
            </div>
            <div class="text-center space-y-2 border-l border-gray-100">
                <div class="text-4xl font-black text-[#174413]">Rp 189.7M</div>
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">GMV Platform</div>
            </div>
        </div>
    </div>

    <!-- 5. Aksi Cepat (Quick Actions) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pb-8">
        <a href="{{ route('admin.verification') }}" class="p-5 bg-white border border-gray-100 rounded-2xl flex items-center gap-4 hover:border-green-300 hover:bg-green-50 transition group shadow-sm">
            <div class="p-2.5 bg-green-50 text-green-600 rounded-xl group-hover:bg-green-100 transition-colors">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <span class="font-bold text-sm text-gray-800">Verifikasi Akun</span>
        </a>
        <a href="{{ route('admin.users') }}" class="p-5 bg-white border border-gray-100 rounded-2xl flex items-center gap-4 hover:border-blue-300 hover:bg-blue-50 transition group shadow-sm">
            <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl group-hover:bg-blue-100 transition-colors">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <span class="font-bold text-sm text-gray-800">Kelola User</span>
        </a>
        <button class="p-5 bg-white border border-gray-100 rounded-2xl flex items-center gap-4 hover:border-orange-300 hover:bg-orange-50 transition group shadow-sm text-left">
            <div class="p-2.5 bg-orange-50 text-orange-600 rounded-xl group-hover:bg-orange-100 transition-colors">
                <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
            </div>
            <span class="font-bold text-sm text-gray-800">Lihat Laporan</span>
        </button>
        <button class="p-5 bg-white border border-gray-100 rounded-2xl flex items-center gap-4 hover:border-purple-300 hover:bg-purple-50 transition group shadow-sm text-left">
            <div class="p-2.5 bg-purple-50 text-purple-600 rounded-xl group-hover:bg-purple-100 transition-colors">
                <i data-lucide="message-square" class="w-5 h-5"></i>
            </div>
            <span class="font-bold text-sm text-gray-800">Moderasi Konten</span>
        </button>
    </div>
</div>
@endsection
