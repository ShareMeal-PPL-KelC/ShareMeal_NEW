@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="{ 
    selectedOrder: null,
    showDetail: false,
    openDetail(order) {
        this.selectedOrder = order;
        this.showDetail = true;
    }
}">
    <!-- Header & Search -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Monitoring Transaksi</h1>
            <p class="text-gray-600 mt-1">Pantau dan kelola seluruh aktivitas transaksi di platform</p>
        </div>
        <form action="{{ route('admin.transactions') }}" method="GET" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <div class="relative flex-1 sm:w-64">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari ID, Konsumen, atau Mitra..." 
                    class="w-full pl-10 pr-4 py-2 rounded-xl border-gray-200 focus:ring-green-500 focus:border-green-500 text-sm">
            </div>
            <select name="status" onchange="this.form.submit()" class="rounded-xl border-gray-200 text-sm focus:ring-green-500 focus:border-green-500">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
            @if($search || $status !== 'all')
                <a href="{{ route('admin.transactions') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 transition text-sm font-medium">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Alert Success/Error -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-xl">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i data-lucide="check-circle" class="h-5 w-5 text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-xl">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i data-lucide="alert-circle" class="h-5 w-5 text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
                <span class="text-sm font-medium text-gray-500">Total Transaksi</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_count']) }}</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 bg-green-50 rounded-lg text-green-600">
                    <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                </div>
                <span class="text-sm font-medium text-gray-500">Volume Transaksi</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">Rp {{ number_format($stats['total_volume'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 bg-orange-50 rounded-lg text-orange-600">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
                <span class="text-sm font-medium text-gray-500">Pending</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['pending_count']) }}</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 bg-green-50 rounded-lg text-green-600">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                </div>
                <span class="text-sm font-medium text-gray-500">Berhasil</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['completed_count']) }}</div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">ID Pesanan</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Konsumen</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Mitra</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-bold text-gray-900">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">
                                    {{ substr($order->customer->name ?? '?', 0, 1) }}
                                </div>
                                <div class="text-sm text-gray-900 font-medium">{{ $order->customer->name ?? 'User Terhapus' }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 font-medium">{{ $order->mitra->name ?? 'Mitra Terhapus' }}</div>
                            <div class="text-xs text-gray-500">{{ $order->mitra->organization_name ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-bold text-gray-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                {{ $order->status === 'completed' ? 'bg-green-100 text-green-700' : 
                                   ($order->status === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700') }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $order->created_at->format('d M Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $order->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <div class="flex justify-end gap-2">
                                <button 
                                    @click="openDetail({
                                        id: '{{ $order->id }}',
                                        order_id_fmt: '#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}',
                                        customer_name: '{{ $order->customer->name ?? 'User Terhapus' }}',
                                        mitra_name: '{{ $order->mitra->name ?? 'Mitra Terhapus' }}',
                                        organization: '{{ $order->mitra->organization_name ?? '' }}',
                                        total_amount: 'Rp {{ number_format($order->total_amount, 0, ',', '.') }}',
                                        status: '{{ $order->status }}',
                                        date: '{{ $order->created_at->format('d M Y, H:i') }}',
                                        pickup_code: '{{ $order->pickup_code ?? '-' }}',
                                        items: [
                                            @foreach($order->items as $item)
                                            {
                                                name: '{{ $item->product->name ?? 'Item Terhapus' }}',
                                                quantity: {{ $item->quantity }},
                                                price: 'Rp {{ number_format($item->price, 0, ',', '.') }}',
                                                subtotal: 'Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}'
                                            },
                                            @endforeach
                                        ]
                                    })"
                                    class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition" title="Lihat Detail">
                                    <i data-lucide="eye" class="w-5 h-5"></i>
                                </button>
                                
                                @if($order->status === 'pending')
                                <form action="{{ route('admin.transactions.cancel', $order->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                                    @csrf
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Batalkan Pesanan">
                                        <i data-lucide="x-circle" class="w-5 h-5"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <i data-lucide="package-search" class="w-12 h-12 mb-4"></i>
                                <p class="text-lg font-medium">Tidak ada transaksi ditemukan</p>
                                <p class="text-sm">Coba sesuaikan filter atau kata kunci pencarian Anda</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detail Modal -->
    <div x-show="showDetail" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
        <div class="bg-white rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl" @click.away="showDetail = false">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Detail Transaksi <span x-text="selectedOrder?.order_id_fmt"></span></h2>
                    <p class="text-sm text-gray-500" x-text="selectedOrder?.date"></p>
                </div>
                <button @click="showDetail = false" class="p-2 hover:bg-gray-200 rounded-full transition">
                    <i data-lucide="x" class="w-6 h-6 text-gray-500"></i>
                </button>
            </div>
            
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase mb-2">Konsumen</h3>
                        <p class="font-bold text-gray-900" x-text="selectedOrder?.customer_name"></p>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase mb-2">Mitra Toko</h3>
                        <p class="font-bold text-gray-900" x-text="selectedOrder?.mitra_name"></p>
                        <p class="text-sm text-gray-500" x-text="selectedOrder?.organization"></p>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase mb-2">Kode Pengambilan</h3>
                        <p class="font-mono font-bold text-green-600 text-lg" x-text="selectedOrder?.pickup_code"></p>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase mb-2">Status</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold capitalize"
                            :class="{
                                'bg-green-100 text-green-700': selectedOrder?.status === 'completed',
                                'bg-orange-100 text-orange-700': selectedOrder?.status === 'pending',
                                'bg-red-100 text-red-700': selectedOrder?.status === 'cancelled'
                            }"
                            x-text="selectedOrder?.status">
                        </span>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <h3 class="text-sm font-bold text-gray-900 mb-4">Item Pesanan</h3>
                    <div class="space-y-4">
                        <template x-for="item in selectedOrder?.items" :key="item.name">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl">
                                <div>
                                    <p class="font-bold text-gray-900" x-text="item.name"></p>
                                    <p class="text-xs text-gray-500" x-text="item.quantity + ' x ' + item.price"></p>
                                </div>
                                <p class="font-bold text-gray-900" x-text="item.subtotal"></p>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100">
                    <div class="flex justify-between items-center text-lg font-bold">
                        <span class="text-gray-900">Total Pembayaran</span>
                        <span class="text-green-600" x-text="selectedOrder?.total_amount"></span>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-gray-50 flex gap-3">
                <button @click="showDetail = false" class="flex-1 py-3 bg-white border border-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-50 transition">
                    Tutup
                </button>
                <template x-if="selectedOrder?.status === 'pending'">
                    <form :action="'{{ url('admin/transactions') }}/' + selectedOrder?.id + '/cancel'" method="POST" class="flex-1" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                        @csrf
                        <button type="submit" class="w-full py-3 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition shadow-lg shadow-red-200">
                            Batalkan Pesanan
                        </button>
                    </form>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
