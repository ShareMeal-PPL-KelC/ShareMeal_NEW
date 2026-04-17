@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Lembaga</h1>
            <p class="text-gray-600 mt-1">Kelola penyaluran donasi makanan untuk masyarakat</p>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500 bg-white px-4 py-2 rounded-lg border border-gray-100 shadow-sm">
            <i data-lucide="user" class="w-4 h-4"></i>
            <span>{{ $shell['userName'] }}</span>
        </div>
    </div>

    @php
        $userObj = \App\Models\User::query()->where('name', $shell['userName'])->where('role', 'lembaga')->first();
    @endphp

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
            <div x-show="showUpload" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4">
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
    @endif


    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition text-center">
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="heart" class="w-6 h-6 text-blue-600"></i>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $stats->totalDonations }}</div>
            <p class="text-xs font-bold text-gray-400 uppercase mt-1">Total Donasi</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition text-center">
            <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="package" class="w-6 h-6 text-orange-600"></i>
            </div>
            <div class="text-3xl font-bold text-orange-600">{{ $stats->activeDonations }}</div>
            <p class="text-xs font-bold text-gray-400 uppercase mt-1">Aktif</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition text-center">
            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="users" class="w-6 h-6 text-green-600"></i>
            </div>
            <div class="text-3xl font-bold text-green-600">{{ $stats->beneficiaries }}</div>
            <p class="text-xs font-bold text-gray-400 uppercase mt-1">Penerima Manfaat</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition text-center">
            <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="calendar" class="w-6 h-6 text-purple-600"></i>
            </div>
            <div class="text-3xl font-bold text-purple-600">{{ $stats->thisMonth }}</div>
            <p class="text-xs font-bold text-gray-400 uppercase mt-1">Bulan Ini</p>
        </div>
    </div>

    <!-- Recent Donations -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="history" class="w-5 h-5 text-gray-600"></i>
                <h2 class="text-xl font-bold text-gray-900 font-manrope">Donasi Terbaru</h2>
            </div>
            <a href="{{ route('lembaga.donations') }}" class="text-sm font-bold text-blue-600 hover:underline flex items-center gap-1">
                Lihat Semua
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>
        </div>
        <div class="p-6">
            @forelse($recentDonations as $donation)
                 <div class="flex items-center justify-between p-4 border border-gray-50 rounded-xl mb-3 hover:border-blue-200 transition">
                      <div class="flex items-center gap-4">
                           <div class="w-10 h-10 bg-gray-50 rounded-lg flex items-center justify-center text-gray-400">
                                <i data-lucide="package" class="w-5 h-5"></i>
                           </div>
                           <div>
                                <div class="font-bold text-gray-900">{{ $donation['store']['name'] }}</div>
                                <div class="text-xs text-gray-500">{{ count($donation['items']) }} Item makanan</div>
                           </div>
                      </div>
                      <div class="flex items-center gap-4">
                           <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">
                                {{ $donation['status'] }}
                           </span>
                           <a href="{{ route('lembaga.donations') }}" class="text-gray-400 hover:text-blue-600">
                                <i data-lucide="chevron-right" class="w-5 h-5"></i>
                           </a>
                      </div>
                 </div>
            @empty
            <div class="text-center py-12">
                 <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                      <i data-lucide="heart" class="w-8 h-8 text-gray-200"></i>
                 </div>
                 <p class="text-gray-500 font-medium italic">Belum ada donasi yang tersedia saat ini.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
