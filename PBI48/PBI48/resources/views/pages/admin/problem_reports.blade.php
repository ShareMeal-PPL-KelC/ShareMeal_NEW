@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Moderasi Laporan Masalah</h1>
        <p class="text-gray-600 mt-1">Tinjau dan ambil tindakan terhadap laporan kualitas makanan dari pengguna</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5"></i>
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Pelapor & Mitra</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Detail Masalah</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($reports as $report)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-6">
                            <div class="space-y-3">
                                <div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-tighter mb-1">Pelapor</p>
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px] font-bold">
                                            {{ substr($report->reporter->name, 0, 1) }}
                                        </div>
                                        <span class="text-sm font-bold text-gray-900">{{ $report->reporter->name }}</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-red-400 uppercase tracking-tighter mb-1">Terlapor (Mitra)</p>
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-[10px] font-bold">
                                            <i data-lucide="store" class="w-3 h-3"></i>
                                        </div>
                                        <span class="text-sm font-bold text-gray-900">{{ $report->mitra->displayName }}</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-1">Warning saat ini: <span class="font-bold text-orange-600">{{ $report->mitra->warnings_count ?? 0 }}</span></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-6 max-w-md">
                            <div class="inline-block px-2 py-0.5 rounded bg-red-50 text-red-600 text-[10px] font-black uppercase mb-2 border border-red-100">
                                {{ $report->issue_label }}
                            </div>
                            <p class="text-sm text-gray-700 leading-relaxed line-clamp-3">{{ $report->description }}</p>
                            
                            @if($report->evidence_image)
                            <div class="mt-3">
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($report->evidence_image) }}" target="_blank" class="text-[10px] font-bold text-blue-600 flex items-center gap-1 hover:underline">
                                    <i data-lucide="image" class="w-3 h-3"></i> Lihat Foto Bukti
                                </a>
                            </div>
                            @endif

                            <div class="mt-3 text-[10px] text-gray-400 font-medium">
                                Melalui: {{ $report->order_id ? 'Pesanan #' . $report->order_id : 'Donasi #' . $report->donation_id }}
                                • {{ $report->created_at->diffForHumans() }}
                            </div>
                        </td>
                        <td class="px-6 py-6">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border
                                {{ $report->status === 'pending' ? 'bg-yellow-100 text-yellow-700 border-yellow-200' : 
                                   ($report->status === 'resolved' ? 'bg-green-100 text-green-700 border-green-200' : 
                                   'bg-gray-100 text-gray-700 border-gray-200') }}">
                                {{ $report->status === 'pending' ? 'Menunggu' : ($report->status === 'resolved' ? 'Ditindak' : 'Diabaikan') }}
                            </span>
                            @if($report->admin_note)
                                <p class="text-[10px] text-gray-500 mt-2 italic">Note: {{ $report->admin_note }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-6 text-right">
                            @if($report->status === 'pending')
                            <div class="flex flex-col gap-2">
                                <form action="{{ route('admin.problem-reports.warn', $report->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full bg-orange-600 text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase hover:bg-orange-700 transition shadow-sm flex items-center justify-center gap-1">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i> Beri Warning
                                    </button>
                                </form>
                                <form action="{{ route('admin.problem-reports.block', $report->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memblokir Mitra ini secara permanen?')">
                                    @csrf
                                    <button type="submit" class="w-full bg-red-600 text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase hover:bg-red-700 transition shadow-sm flex items-center justify-center gap-1">
                                        <i data-lucide="shield-off" class="w-3 h-3"></i> Blokir Mitra
                                    </button>
                                </form>
                                <form action="{{ route('admin.problem-reports.dismiss', $report->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full bg-white border border-gray-200 text-gray-500 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase hover:bg-gray-50 transition flex items-center justify-center gap-1">
                                        <i data-lucide="x-circle" class="w-3 h-3"></i> Abaikan
                                    </button>
                                </form>
                            </div>
                            @else
                                <span class="text-xs text-gray-400 font-bold italic">Tindakan Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-20 text-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="shield-check" class="w-8 h-8 text-gray-300"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Belum ada laporan</h3>
                            <p class="text-gray-500 text-sm">Semua laporan masalah makanan akan muncul di sini</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $reports->links() }}
    </div>
</div>
@endsection
