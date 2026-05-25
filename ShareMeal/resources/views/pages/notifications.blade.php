@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Semua Notifikasi</h1>
            <p class="text-gray-600 mt-1">Riwayat aktivitas dan pemberitahuan sistem Anda</p>
        </div>
        @if(Auth::user()->unreadNotifications->count() > 0)
            <form method="POST" action="{{ route('notifications.markRead') }}">
                @csrf
                <button type="submit" class="bg-white border border-gray-200 text-gray-700 px-6 py-3 rounded-xl font-bold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                    <i data-lucide="check-check" class="w-5 h-5 text-green-600"></i>
                    Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5"></i>
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="divide-y divide-gray-50">
            @forelse($notificationsList as $notification)
                <div class="p-6 hover:bg-gray-50 transition-colors {{ $notification->unread() ? 'bg-blue-50/20' : '' }}">
                    <div class="flex items-start gap-4">
                        <div class="mt-1 flex-shrink-0">
                            @if(($notification->data['status'] ?? '') == 'completed')
                                <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center text-green-600">
                                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                                </div>
                            @elseif(($notification->data['status'] ?? '') == 'cancelled')
                                <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center text-red-600">
                                    <i data-lucide="x-circle" class="w-6 h-6"></i>
                                </div>
                            @elseif(($notification->data['type'] ?? '') == 'warning')
                                <div class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center text-orange-600 animate-pulse">
                                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                                </div>
                            @else
                                <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600">
                                    <i data-lucide="bell" class="w-6 h-6"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-1">
                                <h3 class="font-bold text-gray-900">{{ $notification->data['title'] ?? 'Pemberitahuan Sistem' }}</h3>
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-600 leading-relaxed">{{ $notification->data['message'] ?? '' }}</p>
                            
                            <div class="mt-4 flex items-center gap-3">
                                @if($notification->unread())
                                    <form method="POST" action="{{ route('notifications.markSingleRead', $notification->id) }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold text-green-600 hover:text-green-700 underline underline-offset-4">Tandai Dibaca</button>
                                    </form>
                                @endif
                                
                                @if(isset($notification->data['action_url']))
                                    <a href="{{ $notification->data['action_url'] }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                                        Lihat Detail
                                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-20 text-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-300">
                        <i data-lucide="bell-off" class="w-10 h-10"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Tidak ada notifikasi</h3>
                    <p class="text-gray-500">Semua pemberitahuan Anda akan muncul di sini</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $notificationsList->links() }}
    </div>
</div>
@endsection
