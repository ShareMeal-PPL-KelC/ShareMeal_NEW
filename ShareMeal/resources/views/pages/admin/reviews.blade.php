@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">{{ $shell['title'] }}</h1>
            <p class="text-gray-500 mt-1">{{ $shell['subtitle'] }}</p>
        </div>
    </div>

    <!-- Review Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-50 rounded-xl">
                    <i data-lucide="message-square" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500 font-medium">Total Ulasan</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['total_reviews'] }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-yellow-50 rounded-xl">
                    <i data-lucide="star" class="w-6 h-6 text-yellow-600"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500 font-medium">Rata-rata Rating</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['avg_rating'] }} / 5.0</div>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-50 rounded-xl">
                    <i data-lucide="trending-up" class="w-6 h-6 text-green-600"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500 font-medium">Ulasan Baru (7 Hari)</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['recent_reviews_count'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">Daftar Ulasan Pengguna</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50/50 text-gray-500 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Konsumen</th>
                        <th class="px-6 py-4">Mitra & Produk</th>
                        <th class="px-6 py-4">Rating</th>
                        <th class="px-6 py-4">Komentar</th>
                        <th class="px-6 py-4">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reviews as $review)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $review->customer->name }}</div>
                            <div class="text-xs text-gray-500">{{ $review->customer->email }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $review->mitra->displayName }}</div>
                            <div class="text-xs text-gray-500">
                                @foreach($review->order->items as $item)
                                    {{ $item->product->name }}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1">
                                <span class="font-bold text-gray-900">{{ $review->rating }}</span>
                                <i data-lucide="star" class="w-4 h-4 text-yellow-400 fill-yellow-400"></i>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="max-w-xs truncate text-gray-600" title="{{ $review->comment }}">
                                {{ $review->comment ?: '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-500">
                            {{ $review->created_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                            Belum ada ulasan yang masuk.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reviews->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $reviews->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
