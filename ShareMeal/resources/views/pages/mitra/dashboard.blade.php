@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Dashboard Mitra</h1>
        <p class="text-gray-600 mt-1">Kelola surplus pangan dan kurangi food waste</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Total Produk</span>
                <i data-lucide="package" class="w-4 h-4 text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats->totalProducts }}</div>
            <p class="text-xs text-gray-500 mt-1">Dalam inventaris</p>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Flash Sale Aktif</span>
                <i data-lucide="trending-down" class="w-4 h-4 text-orange-600"></i>
            </div>
            <div class="text-2xl font-bold text-orange-600">{{ $stats->activeFlashSale }}</div>
            <p class="text-xs text-gray-500 mt-1">Produk dengan diskon</p>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Pesanan Pending</span>
                <i data-lucide="shopping-cart" class="w-4 h-4 text-blue-600"></i>
            </div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats->pendingOrders }}</div>
            <p class="text-xs text-gray-500 mt-1">Menunggu pengambilan</p>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Revenue Bulan Ini</span>
                <i data-lucide="dollar-sign" class="w-4 h-4 text-green-600"></i>
            </div>
            <div class="text-2xl font-bold text-green-600">Rp {{ number_format($stats->totalRevenue, 0, ',', '.') }}</div>
            <p class="text-xs text-green-600 mt-1 flex items-center">
                <i data-lucide="trending-up" class="w-3 h-3 mr-1"></i>
                +12% dari bulan lalu
            </p>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Makanan Diselamatkan</span>
                <i data-lucide="leaf" class="w-4 h-4 text-green-600"></i>
            </div>
            <div class="text-2xl font-bold text-green-600">{{ $stats->foodSaved }}</div>
            <p class="text-xs text-gray-500 mt-1">Estimasi total kg</p>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Donasi Diberikan</span>
                <i data-lucide="heart" class="w-4 h-4 text-purple-600"></i>
            </div>
            <div class="text-2xl font-bold text-purple-600">{{ $stats->donationsGiven }}</div>
            <p class="text-xs text-gray-500 mt-1">Ke lembaga sosial</p>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Rating Toko</span>
                <i data-lucide="star" class="w-4 h-4 text-yellow-500"></i>
            </div>
            <div class="text-2xl font-bold text-yellow-500">{{ $stats->averageRating }} <span class="text-sm text-gray-400 font-normal">/ 5.0</span></div>
            <p class="text-xs text-gray-500 mt-1">Berdasarkan {{ $stats->totalReviews }} ulasan</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Expiring Items Alert -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-orange-600"></i>
                    <h2 class="text-xl font-bold text-gray-900">Mendekati Expired</h2>
                </div>
                <a href="{{ route('mitra.inventory') }}" class="text-sm font-semibold text-[#174413] hover:underline flex items-center gap-1">
                    Kelola
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>
            <div class="p-6 space-y-4">
                @forelse($expiringItems as $item)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                    <div>
                        <div class="font-bold text-gray-900">{{ $item->name }}</div>
                        <div class="text-sm text-gray-500">Stok: {{ $item->stock }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-bold text-orange-600">{{ $item->expires_at->diffForHumans() }}</div>
                        <div class="text-[10px] text-gray-400">sisa waktu</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-gray-500 italic">Tidak ada produk kritis.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Reviews -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="star" class="w-5 h-5 text-yellow-500"></i>
                    <h2 class="text-xl font-bold text-gray-900">Ulasan Terbaru</h2>
                </div>
                <a href="{{ route('mitra.reviews') }}" class="text-sm font-semibold text-[#174413] hover:underline flex items-center gap-1">
                    Semua Ulasan
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>
            <div class="p-6 space-y-4">
                @forelse($recentReviews as $review)
                <div class="p-4 bg-yellow-50/30 rounded-xl border border-yellow-100/50">
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-bold text-sm text-gray-900">{{ $review->customer->name }}</div>
                        <div class="flex gap-0.5">
                            @for($i = 1; $i <= 5; $i++)
                            <i data-lucide="star" class="w-3 h-3 {{ $i <= $review->rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-200' }}"></i>
                            @endfor
                        </div>
                    </div>
                    @if($review->comment)
                    <p class="text-xs text-gray-600 italic line-clamp-2">"{{ $review->comment }}"</p>
                    @else
                    <p class="text-xs text-gray-400 italic">Tidak ada komentar.</p>
                    @endif
                    <div class="text-[10px] text-gray-400 mt-2">{{ $review->created_at->diffForHumans() }}</div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-400 italic">
                    <i data-lucide="message-square" class="w-8 h-8 mx-auto mb-2 opacity-20"></i>
                    Belum ada ulasan.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Pesanan Terbaru</h2>
            <a href="{{ route('mitra.orders') }}" class="text-sm font-semibold text-[#174413] hover:underline flex items-center gap-1">
                Lihat Semua Pesanan
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>
        </div>
        <div class="p-6 space-y-4">
            @forelse($recentOrders as $order)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border border-gray-100 rounded-xl gap-4 hover:border-green-200 transition">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-gray-900">{{ $order->customer->name }}</span>
                        <span class="text-xs text-gray-400 font-mono">#{{ $order->id }}</span>
                    </div>
                    <div class="text-sm text-gray-600 mt-1 line-clamp-1">{{ $order->items_string }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $order->time }}</div>
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-right">
                        <div class="font-bold text-green-700">Rp {{ number_format($order->amount, 0, ',', '.') }}</div>
                        <div class="text-xs mt-1 {{ $order->status === 'Selesai' ? 'text-green-600' : 'text-orange-600 font-bold' }}">
                            {{ $order->status }}
                        </div>
                    </div>
                    <a href="{{ route('mitra.orders') }}" class="bg-gray-50 text-gray-600 px-4 py-2 rounded-lg text-sm font-bold hover:bg-green-50 hover:text-[#174413] transition">
                        Detail
                    </a>
                </div>
            </div>
            @empty
            <div class="text-center py-4 text-gray-500 italic">Belum ada pesanan masuk.</div>
            @endforelse
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
@endsection
