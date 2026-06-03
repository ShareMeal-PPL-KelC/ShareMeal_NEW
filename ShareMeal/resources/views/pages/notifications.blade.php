@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Back Navigation Buttons -->
    <div class="reveal flex flex-wrap gap-3">
        <a href="javascript:history.back()" class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-xl bg-white/80 border border-luxury-alabas/85 text-[10px] font-black uppercase tracking-widest text-luxury-forest hover:bg-[#174413] hover:text-white transition-all duration-300 shadow-sm hover:shadow-md hover:shadow-emerald-950/5 group cursor-pointer">
            <i data-lucide="arrow-left" class="w-4.5 h-4.5 transition-transform group-hover:-translate-x-1 stroke-[2.5]"></i>
            Kembali ke Halaman Sebelumnya
        </a>
        <a href="{{ route(Auth::user()->role . '.dashboard') }}" class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-xl bg-white/80 border border-luxury-alabas/85 text-[10px] font-black uppercase tracking-widest text-luxury-slate hover:bg-luxury-slate hover:text-white transition-all duration-300 shadow-sm hover:shadow-md group">
            <i data-lucide="layout-dashboard" class="w-4.5 h-4.5 stroke-[2.5]"></i>
            Dashboard Utama
        </a>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 reveal delay-100">
        <div>
            <h1 class="text-4xl font-serif font-black text-luxury-forest leading-tight">Semua Notifikasi</h1>
            <p class="text-sm font-medium text-luxury-slate mt-1.5">Riwayat aktivitas dan pemberitahuan sistem Anda</p>
        </div>
        @if(Auth::user()->unreadNotifications->count() > 0)
            <form method="POST" action="{{ route('notifications.markRead') }}">
                @csrf
                <button type="submit" class="px-5 py-3.5 rounded-xl bg-[#174413] text-white border border-[#174413] text-[10px] font-black uppercase tracking-widest hover:bg-transparent hover:text-[#174413] transition-all duration-300 shadow-md shadow-[#174413]/10 flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                    <i data-lucide="check-check" class="w-4 h-4 stroke-[2.5]"></i>
                    Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
    <div class="bg-emerald-50/80 backdrop-blur-md border border-emerald-200/60 text-emerald-800 px-6 py-4 rounded-2xl flex items-center gap-3 reveal">
        <i data-lucide="check-circle" class="w-5 h-5 text-[#10B981]"></i>
        <span class="text-sm font-semibold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="glass-card rounded-[2rem] overflow-hidden reveal delay-200">
        <div class="divide-y divide-luxury-alabas/50">
            @forelse($notificationsList as $notification)
                <div class="p-6 hover:bg-white/40 transition-colors {{ $notification->unread() ? 'bg-emerald-50/15' : '' }}">
                    <div class="flex items-start gap-4">
                        <div class="mt-1 flex-shrink-0">
                            @if(($notification->data['status'] ?? '') == 'completed')
                                <div class="w-12 h-12 bg-emerald-100/70 border border-emerald-200 rounded-2xl flex items-center justify-center text-[#10B981] shadow-sm">
                                    <i data-lucide="check-circle" class="w-6 h-6 stroke-[2]"></i>
                                </div>
                            @elseif(($notification->data['status'] ?? '') == 'cancelled')
                                <div class="w-12 h-12 bg-red-100/70 border border-red-200 rounded-2xl flex items-center justify-center text-red-600 shadow-sm">
                                    <i data-lucide="x-circle" class="w-6 h-6 stroke-[2]"></i>
                                </div>
                            @elseif(($notification->data['type'] ?? '') == 'warning')
                                <div class="w-12 h-12 bg-orange-100/70 border border-orange-200 rounded-2xl flex items-center justify-center text-orange-600 animate-pulse shadow-sm">
                                    <i data-lucide="alert-triangle" class="w-6 h-6 stroke-[2]"></i>
                                </div>
                            @else
                                <div class="w-12 h-12 bg-emerald-50 border border-luxury-alabas rounded-2xl flex items-center justify-center text-luxury-forest shadow-sm">
                                    <i data-lucide="bell" class="w-6 h-6 stroke-[2]"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start mb-1.5">
                                <h3 class="font-bold text-luxury-charcoal text-base leading-snug">{{ $notification->data['title'] ?? 'Pemberitahuan Sistem' }}</h3>
                                <span class="text-[9px] font-black text-luxury-slate/60 uppercase tracking-widest whitespace-nowrap ml-4">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm font-medium text-luxury-slate leading-relaxed">{{ $notification->data['message'] ?? '' }}</p>
                            
                            <div class="mt-4 flex items-center gap-4">
                                @if($notification->unread())
                                    <form method="POST" action="{{ route('notifications.markSingleRead', $notification->id) }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold text-[#10B981] hover:text-[#059669] transition-colors underline underline-offset-4 cursor-pointer">Tandai Dibaca</button>
                                    </form>
                                @endif
                                
                                @if(isset($notification->data['action_url']))
                                    <a href="{{ $notification->data['action_url'] }}" class="text-xs font-black text-luxury-forest hover:text-luxury-gold flex items-center gap-1 transition-colors uppercase tracking-wider">
                                        Lihat Detail
                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 stroke-[2.5]"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-20 text-center">
                    <div class="w-20 h-20 bg-luxury-ivory border border-luxury-alabas/55 rounded-3xl flex items-center justify-center mx-auto mb-6 text-luxury-slate/40 shadow-sm">
                        <i data-lucide="bell-off" class="w-10 h-10 stroke-[1.5]"></i>
                    </div>
                    <h3 class="text-xl font-bold text-luxury-charcoal mb-2">Tidak ada notifikasi</h3>
                    <p class="text-sm font-medium text-luxury-slate">Semua pemberitahuan Anda akan muncul di sini</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center reveal delay-300">
        {{ $notificationsList->links() }}
    </div>
</div>
@endsection
