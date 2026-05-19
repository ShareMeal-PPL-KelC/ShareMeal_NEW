@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Ulasan Konsumen</h1>
        <p class="text-gray-600 mt-1">Lihat feedback dan rating dari pembeli Anda</p>
    </div>

    <!-- Review List -->
    <div class="grid grid-cols-1 gap-6">
        @forelse($reviews as $review)
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-300">
            <div class="p-8">
                <div class="flex flex-col md:flex-row justify-between gap-6">
                    <div class="flex gap-4">
                        <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center text-green-600 flex-shrink-0">
                            <i data-lucide="user" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <div class="font-black text-xl text-gray-900">{{ $review->customer->name }}</div>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                    <i data-lucide="star" class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-200' }}"></i>
                                    @endfor
                                </div>
                                <span class="text-sm font-bold text-gray-400">•</span>
                                <span class="text-sm text-gray-400 font-medium">{{ $review->created_at->format('d M Y, H:i') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">ID Pesanan</div>
                        <div class="font-mono text-sm bg-gray-50 px-3 py-1 rounded-lg border border-gray-100 inline-block">
                            #{{ $review->order->orderId }}
                        </div>
                    </div>
                </div>

                <!-- Review Content -->
                <div class="mt-8 bg-gray-50 rounded-2xl p-6 relative">
                    <i data-lucide="quote" class="w-8 h-8 text-green-100 absolute -top-4 -left-2 transform -rotate-12"></i>
                    @if($review->comment)
                    <p class="text-gray-700 font-medium italic relative z-10 leading-relaxed">
                        "{{ $review->comment }}"
                    </p>
                    @else
                    <p class="text-gray-400 italic relative z-10">Pembeli tidak memberikan komentar.</p>
                    @endif
                </div>

                <!-- Items Purchased -->
                <div class="mt-6 flex flex-wrap gap-2">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest w-full mb-1">Item yang dibeli:</span>
                    @foreach($review->order->items as $item)
                    <span class="bg-white border border-gray-100 px-3 py-1.5 rounded-full text-xs font-bold text-gray-600 shadow-sm">
                        {{ $item->product ? $item->product->name : $item->name }} ({{ $item->quantity }})
                    </span>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
            <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="message-square" class="w-10 h-10 text-gray-300"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900 mb-2">Belum Ada Ulasan</h3>
            <p class="text-gray-500 font-medium">Ulasan dari konsumen akan muncul di sini setelah mereka memberikan rating.</p>
        </div>
        @endforelse

        <!-- Pagination -->
        <div class="mt-6">
            {{ $reviews->links() }}
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
@endsection
