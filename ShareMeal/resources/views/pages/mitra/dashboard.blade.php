@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <div class="mb-12">
        <h1 class="text-5xl font-serif font-bold text-luxury-forest leading-tight">Business Overview</h1>
        <p class="text-luxury-slate font-medium mt-2 tracking-wide text-center md:text-left">Optimize your surplus inventory and enhance your community impact.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
        <div class="bg-white p-8 rounded-[2rem] luxury-shadow border border-luxury-alabas hover:bg-luxury-forest transition-all duration-500 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-luxury-ivory rounded-xl flex items-center justify-center group-hover:bg-white/10">
                    <i data-lucide="package" class="w-6 h-6 text-luxury-gold"></i>
                </div>
                <div class="text-[10px] font-black text-luxury-gold uppercase tracking-widest">Live Stock</div>
            </div>
            <div class="text-4xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">{{ $stats->totalProducts }} Items</div>
            <p class="text-[10px] text-luxury-slate group-hover:text-white/60 mt-3 font-bold uppercase tracking-wider italic">Within active inventory</p>
        </div>

        <div class="bg-white p-8 rounded-[2rem] luxury-shadow border border-luxury-alabas hover:bg-luxury-emerald transition-all duration-500 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-luxury-ivory rounded-xl flex items-center justify-center group-hover:bg-white/10">
                    <i data-lucide="dollar-sign" class="w-6 h-6 text-luxury-gold"></i>
                </div>
                <div class="text-[10px] font-black text-luxury-gold uppercase tracking-widest">Revenue</div>
            </div>
            <div class="text-3xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">Rp {{ number_format($stats->totalRevenue / 1000, 0) }}k</div>
            <p class="text-[10px] text-luxury-emerald group-hover:text-white mt-3 font-black uppercase tracking-wider bg-luxury-emerald/10 group-hover:bg-white/10 px-3 py-1 rounded-full inline-block">
                +12.5% MTM
            </p>
        </div>

        <div class="bg-white p-8 rounded-[2rem] luxury-shadow border border-luxury-alabas hover:bg-luxury-gold transition-all duration-500 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-luxury-ivory rounded-xl flex items-center justify-center group-hover:bg-white/10">
                    <i data-lucide="star" class="w-6 h-6 text-luxury-forest"></i>
                </div>
                <div class="text-[10px] font-black text-luxury-forest group-hover:text-white uppercase tracking-widest">Appreciation</div>
            </div>
            <div class="text-4xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">{{ $stats->averageRating }} <span class="text-sm opacity-40">/ 5.0</span></div>
            <p class="text-[10px] text-luxury-slate group-hover:text-white/60 mt-3 font-bold uppercase tracking-wider italic">From {{ $stats->totalReviews }} curators</p>
        </div>

        <div class="bg-white p-8 rounded-[2rem] luxury-shadow border border-luxury-alabas hover:bg-luxury-forest transition-all duration-500 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-luxury-ivory rounded-xl flex items-center justify-center group-hover:bg-white/10">
                    <i data-lucide="leaf" class="w-6 h-6 text-luxury-gold"></i>
                </div>
                <div class="text-[10px] font-black text-luxury-gold uppercase tracking-widest">Impact</div>
            </div>
            <div class="text-4xl font-serif font-bold text-luxury-forest group-hover:text-white transition-colors">{{ $stats->foodSaved }}kg</div>
            <p class="text-[10px] text-luxury-slate group-hover:text-white/60 mt-3 font-bold uppercase tracking-wider italic">Food waste diverted</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-16">
        <!-- Expiring Items Alert -->
        <div class="bg-white rounded-[2.5rem] border border-luxury-alabas luxury-shadow overflow-hidden flex flex-col">
            <div class="p-8 border-b border-luxury-alabas flex items-center justify-between bg-luxury-ivory/30">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></div>
                    <h2 class="text-xl font-serif font-bold text-luxury-forest">Urgent Inventory</h2>
                </div>
                <a href="{{ route('mitra.inventory') }}" class="text-[10px] font-black uppercase tracking-[0.2em] text-luxury-gold hover:text-luxury-forest transition-colors">Manage</a>
            </div>
            <div class="p-8 space-y-4 flex-1">
                @forelse($expiringItems as $item)
                <div class="flex items-center justify-between p-6 bg-luxury-ivory/50 rounded-2xl border border-luxury-alabas hover:bg-white hover:luxury-shadow transition-all duration-300 group">
                    <div>
                        <div class="font-bold text-luxury-forest group-hover:text-luxury-gold transition-colors">{{ $item->name }}</div>
                        <div class="text-[10px] text-luxury-slate font-black uppercase tracking-widest mt-1">Stock: {{ $item->stock }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-black text-orange-600 uppercase tracking-widest mb-1">{{ $item->expires_at->diffForHumans() }}</div>
                        <div class="w-16 h-1 bg-luxury-alabas rounded-full overflow-hidden">
                            <div class="h-full bg-orange-400 w-2/3"></div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <i data-lucide="check-circle" class="w-10 h-10 text-luxury-emerald/30 mx-auto mb-4"></i>
                    <p class="text-luxury-slate font-serif italic text-lg">Inventory is optimized.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Reviews -->
        <div class="bg-white rounded-[2.5rem] border border-luxury-alabas luxury-shadow overflow-hidden flex flex-col">
            <div class="p-8 border-b border-luxury-alabas flex items-center justify-between bg-luxury-ivory/30">
                <div class="flex items-center gap-3">
                    <i data-lucide="star" class="w-5 h-5 text-luxury-gold"></i>
                    <h2 class="text-xl font-serif font-bold text-luxury-forest">Latest Appreciation</h2>
                </div>
                <a href="{{ route('mitra.reviews') }}" class="text-[10px] font-black uppercase tracking-[0.2em] text-luxury-gold hover:text-luxury-forest transition-colors">View All</a>
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
                    <p class="text-xs text-luxury-slate italic">Silent appreciation</p>
                    @endif
                    <div class="text-[9px] text-luxury-gold font-black uppercase tracking-widest mt-4">{{ $review->created_at->diffForHumans() }}</div>
                </div>
                @empty
                <div class="text-center py-12">
                    <i data-lucide="message-square" class="w-10 h-10 text-luxury-alabas/30 mx-auto mb-4"></i>
                    <p class="text-luxury-slate font-serif italic text-lg">Awaiting feedback.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded-[3rem] border border-luxury-alabas luxury-shadow overflow-hidden mb-12">
        <div class="p-10 border-b border-luxury-alabas flex items-center justify-between bg-luxury-ivory/20">
            <div>
                <h2 class="text-3xl font-serif font-bold text-luxury-forest">Recent Acquisitions</h2>
                <p class="text-[10px] text-luxury-gold font-black uppercase tracking-[0.2em] mt-1">Pantau pesanan terbaru dari komunitas Anda</p>
            </div>
            <a href="{{ route('mitra.orders') }}" class="px-8 py-4 rounded-2xl bg-white border border-luxury-alabas text-[10px] font-black uppercase tracking-[0.2em] text-luxury-forest hover:bg-luxury-forest hover:text-white transition-all duration-500 luxury-shadow">
                Full Log
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
                        <div class="text-[9px] text-luxury-gold font-black uppercase tracking-widest mt-3">{{ $order->time }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-10">
                    <div class="text-right">
                        <div class="text-2xl font-serif font-black text-luxury-forest">Rp {{ number_format($order->amount, 0, ',', '.') }}</div>
                        <div class="mt-2">
                            <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-lg {{ $order->status === 'Selesai' ? 'bg-luxury-emerald/10 text-luxury-emerald' : 'bg-orange-50 text-orange-600' }}">
                                {{ $order->status }}
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
                <p class="text-luxury-slate font-serif text-xl italic">Waiting for the first acquisition.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
@endsection
