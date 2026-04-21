@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Dashboard Lembaga Sosial</h1>
        <p class="text-gray-600 mt-1">Kelola penerimaan donasi makanan</p>
    </div>

    @if($userObj && !$userObj->is_verified && $userObj->verification_rejection_reason)
        <!-- Rejection Notice -->
        <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-6" x-data="{ showUpload: false }">
            <div class="flex items-start gap-4">
                <div class="h-12 w-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <i data-lucide="shield-alert" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-red-900 leading-tight">Verifikasi Lembaga Ditolak</h3>
                    <p class="text-red-700 text-sm mt-1">
                        <strong>Alasan:</strong> {{ $userObj->verification_rejection_reason }}
                    </p>
                    <p class="text-red-600 text-[11px] mt-2 italic font-medium">Mohon unggah kembali dokumen legalitas organisasi Anda agar bisa segera mulai mengklaim donasi makanan.</p>
                </div>
            </div>
            <button @click="showUpload = !showUpload" class="bg-red-600 text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-red-700 transition shadow-lg shadow-red-200 flex-shrink-0">
                Lengkapi Dokumen Sekarang
            </button>

            <!-- Re-upload Form -->
            <div x-show="showUpload" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
                <div class="fixed inset-0 bg-black/60" @click="showUpload = false"></div>
                <div class="relative bg-white rounded-3xl w-full max-w-xl p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-bold text-[#174413]">Re-upload Dokumen Lembaga</h3>
                        <button @click="showUpload = false"><i data-lucide="x" class="w-6 h-6"></i></button>
                    </div>
                    <form action="{{ route('lembaga.upload.document') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        @foreach([
                            'document_ktp' => ['label' => 'Dokumen Legalitas Dasar', 'desc' => '(Akta Pendirian, SK Menkumham, dll)'],
                            'document_siup' => ['label' => 'Dokumen Izin Operasional & Registrasi Sosial', 'desc' => '(Izin LKS, Tanda Daftar Yayasan, dll)'],
                            'document_nib' => ['label' => 'Dokumen Identitas & Lokasi', 'desc' => '(KTP Pengurus, Domisili, Foto Lokasi)']
                        ] as $name => $info)
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">{{ $info['label'] }}</label>
                                <p class="text-[10px] text-gray-400 mb-2">{{ $info['desc'] }}</p>
                                <input type="file" name="{{ $name }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-[#174413] file:text-white" required>
                            </div>
                        @endforeach
                        <button type="submit" class="w-full bg-[#174413] text-white py-4 rounded-xl font-bold hover:bg-[#1a5a14] transition">Kirim Dokumen Baru</button>
                    </form>
                </div>
            </div>
        </div>
    @elseif($userObj && !$userObj->is_verified)
        <!-- Pending Info -->
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4 text-center md:text-left">
                <div class="h-10 w-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-blue-900 leading-tight">Akun Sedang Diverifikasi</h3>
                    <p class="text-blue-700 text-sm mt-0.5">Admin sedang mereview dokumen organisasi Anda. Mohon tunggu informasi selanjutnya.</p>
                </div>
            </div>
        </div>
    @else
        <!-- Verification Status Verified -->
        <div class="bg-green-50 border border-green-200 rounded-2xl p-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <i data-lucide="check-circle" class="w-6 h-6 text-white"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-green-900 mb-1">Lembaga Terverifikasi</h3>
                    <p class="text-sm text-green-800 mb-4">Status legalitas Anda telah diverifikasi oleh admin.</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="bg-white text-green-700 border border-green-200 px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="check-circle" class="w-3 h-3"></i>
                            Akta Pendirian
                        </span>
                        <span class="bg-white text-green-700 border border-green-200 px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="check-circle" class="w-3 h-3"></i>
                            SK Kemenkumham
                        </span>
                        <span class="bg-white text-green-700 border border-green-200 px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="check-circle" class="w-3 h-3"></i>
                            NPWP Lembaga
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm text-center">
            <div class="text-3xl font-bold text-purple-600">{{ $stats->totalDonations }}</div>
            <div class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mt-1">Total Donasi</div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm text-center">
            <div class="text-3xl font-bold text-orange-600">{{ $stats->activeDonations }}</div>
            <div class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mt-1">Aktif</div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm text-center">
            <div class="text-3xl font-bold text-blue-600">{{ $stats->beneficiaries }}</div>
            <div class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mt-1">Penerima Manfaat</div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm text-center">
            <div class="text-3xl font-bold text-green-600">{{ $stats->thisMonth }}</div>
            <div class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mt-1">Bulan Ini</div>
        </div>
    </div>

    <!-- Available Donations -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="package" class="w-5 h-5 text-purple-600"></i>
                <h2 class="text-xl font-bold text-gray-900">Donasi Tersedia ({{ count($availableDonations) }})</h2>
            </div>
            <a href="{{ route('lembaga.donations') }}" class="text-sm font-bold text-gray-500 hover:text-gray-900 border px-3 py-1.5 rounded-lg">Lihat Semua</a>
        </div>
        <div class="p-6 space-y-4">
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-start gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                <p class="text-sm text-blue-800">
                    <strong>Sistem First-Come, First-Served:</strong> Klaim donasi tersedia untuk lembaga terverifikasi dengan prinsip siapa cepat dia dapat.
                </p>
            </div>

            @forelse($availableDonations as $d)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 border border-gray-100 rounded-2xl gap-4 hover:border-purple-200 transition">
                <div class="flex-1">
                    <h4 class="font-bold text-gray-900 text-lg">{{ $d['store']['name'] }}</h4>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ collect($d['items'])->map(fn($i) => $i['name'])->join(', ') }} ({{ collect($d['items'])->sum('quantity') }} unit)
                    </p>
                    <div class="flex flex-wrap gap-4 mt-3 text-xs text-gray-500 font-medium uppercase">
                        <span>📍 {{ $d['store']['address'] }}</span>
                        <span>• {{ $d['distance'] }}</span>
                        <span class="text-orange-600 flex items-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3"></i> Sampai {{ $d['available_until'] }}
                        </span>
                    </div>
                </div>
                @if($userObj && $userObj->is_verified)
                    <form action="{{ route('lembaga.donations.claim', $d['id']) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-purple-600 text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-purple-700 transition flex items-center justify-center gap-2 shadow-lg shadow-purple-100">
                            <i data-lucide="heart" class="w-4 h-4 text-white"></i> Klaim Donasi
                        </button>
                    </form>
                @else
                    <button class="bg-gray-200 text-gray-500 px-6 py-3 rounded-xl font-bold text-sm cursor-not-allowed flex items-center justify-center gap-2" title="Akun Anda belum terverifikasi">
                        <i data-lucide="lock" class="w-4 h-4"></i> Klaim Donasi
                    </button>
                @endif
            </div>
            @empty
            <div class="text-center py-12">
                 <p class="text-gray-500 italic">Tidak ada donasi tersedia saat ini.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Recent History -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-50 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Riwayat Penerimaan Donasi</h2>
            <a href="{{ route('lembaga.donations', ['tab' => 'completed']) }}" class="text-sm font-bold text-gray-500 hover:text-gray-900 border px-3 py-1.5 rounded-lg">Lihat Semua</a>
        </div>
        <div class="p-6 space-y-3">
            @forelse($recentDonations as $rd)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-transparent hover:border-gray-200 transition">
                <div class="flex-1">
                    <div class="font-bold text-gray-900">{{ $rd['store']['name'] }}</div>
                    <div class="text-sm text-gray-600">
                        {{ collect($rd['items'])->map(fn($i) => $i['name'])->join(', ') }}
                    </div>
                    <div class="text-[10px] text-gray-400 font-bold uppercase mt-1">{{ $rd['claimed_at'] ?? now()->toDateString() }}</div>
                </div>
                <span class="bg-green-100 text-green-700 px-3 py-1.5 rounded-full text-[10px] font-bold border border-green-200 uppercase flex items-center gap-1">
                    <i data-lucide="check-circle" class="w-3 h-3"></i> 
                    {{ $rd['status'] === 'completed' ? 'Diterima' : 'Diproses' }}
                </span>
            </div>
            @empty
            <div class="text-center py-12">
                 <p class="text-gray-500 font-medium italic">Belum ada riwayat donasi.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Impact Section -->
    <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-2xl p-8 border border-gray-100 shadow-sm">
        <div class="flex items-center gap-2 mb-8">
            <i data-lucide="leaf" class="w-6 h-6 text-green-600"></i>
            <h2 class="text-xl font-bold text-gray-900">Dampak Positif Bulan Ini</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="text-3xl font-black text-green-600">{{ $stats->totalDonations * 12 }}</div>
                <div class="text-xs text-gray-500 font-bold uppercase mt-2">Porsi Tersalurkan</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-black text-blue-600">{{ $stats->totalDonations * 3.5 }} kg</div>
                <div class="text-xs text-gray-500 font-bold uppercase mt-2">CO₂ Terselamatkan</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-black text-purple-600">{{ $stats->beneficiaries }}</div>
                <div class="text-xs text-gray-500 font-bold uppercase mt-2">Penerima Manfaat</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-black text-orange-600">Rp {{ number_format($stats->totalDonations * 25000 / 1000, 0) }}k</div>
                <div class="text-xs text-gray-500 font-bold uppercase mt-2">Nilai Sosial</div>
            </div>
        </div>
    </div>
</div>
@endsection
