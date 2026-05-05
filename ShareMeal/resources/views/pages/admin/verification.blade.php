@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="{ 
    previewModalOpen: false, 
    previewUrl: '', 
    previewTitle: '',
    rejectModalOpen: false,
    rejectUserId: null,
    rejectUserName: '',
    
    openPreview(url, title) {
        this.previewUrl = url;
        this.previewTitle = title;
        this.previewModalOpen = true;
    },
    
    openReject(id, name) {
        this.rejectUserId = id;
        this.rejectUserName = name;
        this.rejectModalOpen = true;
    }
}">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Verifikasi Dokumen</h1>
            <p class="text-gray-600 mt-1">Review dan validasi berkas legalitas pendaftar baru</p>
        </div>
    </div>

    @if(count($applications) > 0)
        <div class="grid gap-6">
            @foreach($applications as $app)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center text-green-600 font-bold text-lg">
                                    {{ substr($app['name'], 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $app['name'] }}</h3>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $app['type'] === 'mitra' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ ucfirst($app['type']) }}
                                        </span>
                                        <span class="text-sm text-gray-500 flex items-center gap-1">
                                            <i data-lucide="mail" class="w-3 h-3"></i>
                                            {{ $app['email'] }}
                                        </span>
                                        <span class="text-sm text-gray-500 flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            Terdaftar: {{ $app['submitted_at'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <form action="{{ route('admin.verification.approve', $app['id']) }}" method="POST">
                                    @csrf
                                    <button id="btn-approve-{{ $app['id'] }}" type="submit" class="inline-flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-700 transition">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                        Setujui
                                    </button>
                                </form>
                                <button id="btn-reject-{{ $app['id'] }}" @click="openReject({{ $app['id'] }}, '{{ $app['name'] }}')" class="inline-flex items-center gap-2 border border-red-200 text-red-600 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-50 transition">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                    Tolak
                                </button>
                            </div>
                        </div>

                        <div class="mt-8">
                            <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Berkas Dokumen</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                @foreach($app['documents'] as $key => $path)
                                    @if($path)
                                        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50 flex flex-col justify-between group">
                                            <div class="flex items-center gap-3 mb-4">
                                                <div class="p-2 bg-white rounded-lg shadow-sm">
                                                    <i data-lucide="file-text" class="w-5 h-5 text-[#174413]"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-sm font-bold text-gray-900 truncate uppercase">{{ str_replace('_', ' ', $key) }}</div>
                                                    <div class="text-[10px] text-gray-500 truncate">Verifikasi Diperlukan</div>
                                                </div>
                                            </div>
                                            <button id="btn-preview-{{ $app['id'] }}-{{ $key }}" @click="openPreview('{{ asset('storage/' . $path) }}', '{{ strtoupper($key) }} - {{ $app['name'] }}')" 
                                                    class="w-full py-2 bg-white border border-gray-200 rounded-lg text-xs font-bold text-[#174413] hover:bg-green-50 hover:border-green-200 transition">
                                                Preview Dokumen
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center">
            <div class="h-16 w-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                <i data-lucide="shield-check" class="w-8 h-8"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Tidak Ada Antrian Verifikasi</h3>
            <p class="text-gray-500 mt-2">Semua Mitra dan Lembaga Sosial saat ini sudah terverifikasi.</p>
        </div>
    @endif

    <!-- Preview Modal -->
    <div x-show="previewModalOpen" 
         class="fixed inset-0 z-[100] overflow-y-auto" 
         x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/60 transition-opacity" @click="previewModalOpen = false"></div>
            
            <div class="relative bg-white rounded-3xl w-full max-w-5xl shadow-2xl overflow-hidden">
                <div class="p-6 border-b flex items-center justify-between sticky top-0 bg-white z-10">
                    <h3 class="text-xl font-bold text-gray-900" x-text="previewTitle"></h3>
                    <button @click="previewModalOpen = false" class="p-2 hover:bg-gray-100 rounded-full transition">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div class="p-8 bg-gray-100 flex justify-center min-h-[500px]">
                    <template x-if="previewUrl.toLowerCase().endsWith('.pdf')">
                        <iframe :src="previewUrl" class="w-full h-[600px] rounded-xl shadow-lg"></iframe>
                    </template>
                    <template x-if="!previewUrl.toLowerCase().endsWith('.pdf')">
                        <img :src="previewUrl" class="max-w-full h-auto rounded-xl shadow-lg border-4 border-white">
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="rejectModalOpen" 
         class="fixed inset-0 z-[100] overflow-y-auto" 
         x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-black/60 transition-opacity" @click="rejectModalOpen = false"></div>
            
            <div class="relative bg-white rounded-2xl w-full max-w-md p-8 shadow-2xl" @click.stop>
                <div class="h-14 w-14 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="alert-triangle" class="w-8 h-8"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Tolak Verifikasi</h3>
                <p class="text-gray-500 mb-8 font-medium">Berikan alasan mengapa dokumen <span class="text-gray-900 font-bold" x-text="rejectUserName"></span> ditolak agar mereka bisa memperbaikinya.</p>
                
                <form :action="'{{ url('admin/verification') }}/' + rejectUserId + '/reject'" method="POST">
                    @csrf
                    <div class="mb-6">
                        <textarea name="reason" rows="4" required class="w-full rounded-xl border-gray-300 focus:border-red-300 focus:ring-red-200 p-4 text-sm bg-gray-50" placeholder="Contoh: Foto KTP tidak jelas atau SIUP sudah tidak berlaku..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="rejectModalOpen = false" class="flex-1 py-3 border border-gray-300 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition">Batal</button>
                        <button id="btn-confirm-reject" type="submit" class="flex-1 py-3 bg-red-600 rounded-xl text-sm font-bold text-white hover:bg-red-700 transition">Konfirmasi Tolak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
