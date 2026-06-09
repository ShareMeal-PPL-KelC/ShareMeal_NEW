@extends('layouts.dashboard')

@php
    $user = Auth::user()?->load('profile');
    $profile = $user?->profile;
    $openingHours = $profile?->business_opening_hours ?? $profile?->opening_hours;
    $defaultStart = '18:00';
    $defaultEnd = '20:00';

    if ($openingHours && str_contains($openingHours, ' - ')) {
        [$opStart, $opEnd] = explode(' - ', $openingHours, 2);
        try {
            $startCarbon = \Carbon\Carbon::createFromFormat('H:i', trim($opStart));
            $defaultStart = $startCarbon->addHour()->format('H:i');
        } catch (\Exception $e) {
            // fallback
        }
        $defaultEnd = trim($opEnd);
    }
@endphp

@section('content')
<div class="space-y-6" x-data="{ isDialogOpen: false, expiresDate: '', expiresTime: '', selectedUnit: 'box' }" x-effect="document.body.style.overflow = isDialogOpen ? 'hidden' : ''">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Riwayat Donasi</h1>
            <p class="text-gray-600 mt-1">Daftar donasi Anda dan informasi lembaga penerima</p>
        </div>
        @if(auth()->user()->is_verified)
        <button @click="isDialogOpen = true" class="bg-[#174413] hover:bg-[#0f2d0c] text-white px-6 py-3 rounded-xl font-bold transition flex items-center gap-2 shadow-lg shadow-green-900/20">
            <i data-lucide="plus" class="w-5 h-5"></i>
            Tambah Donasi
        </button>
        @else
        <button disabled title="Akun Anda belum terverifikasi oleh admin." class="bg-gray-300 text-gray-500 cursor-not-allowed px-6 py-3 rounded-xl font-bold transition flex items-center gap-2 shadow-none opacity-60">
            <i data-lucide="lock" class="w-5 h-5"></i>
            Tambah Donasi
        </button>
        @endif
    </div>

    @if(!auth()->user()->is_verified)
    <div class="bg-amber-50 border border-amber-200 text-amber-800 px-6 py-4 rounded-xl flex items-center gap-3">
        <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600"></i>
        <span>Akun Anda belum terverifikasi oleh admin. Anda tidak dapat menambahkan donasi baru saat ini.</span>
    </div>
    @endif

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5"></i>
        {{ session('success') }}
    </div>
    @endif

    <div class="space-y-6">
        @forelse($donations as $donation)
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-8 space-y-6">
                    <!-- Donation Header -->
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                        <div>
                            <div class="flex items-center gap-3">
                                <h3 class="text-2xl font-black text-gray-900">{{ $donation->title }}</h3>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border
                                    {{ $donation->status === 'claimed' ? 'bg-blue-100 text-blue-700 border-blue-200' : 
                                       ($donation->status === 'completed' ? 'bg-green-100 text-green-700 border-green-200' : 
                                       'bg-yellow-100 text-yellow-700 border-yellow-200') }}">
                                    {{ $donation->status === 'claimed' ? 'Terklaim' : ($donation->status === 'completed' ? 'Selesai' : 'Menunggu Klaim') }}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 mt-2">
                                <p class="text-sm text-gray-400 font-medium">Didaftarkan: {{ \Carbon\Carbon::parse($donation->created_at)->format('d M Y, H:i') }}</p>
                                @if($donation->expires_at)
                                <p class="text-sm text-orange-500 font-medium flex items-center gap-1">
                                    <i data-lucide="clock" class="w-4 h-4"></i> Layak Konsumsi s/d: {{ \Carbon\Carbon::parse($donation->expires_at)->format('d M Y, H:i') }}
                                </p>
                                @endif
                                <p class="text-sm text-green-600 font-bold flex items-center gap-1">
                                    <i data-lucide="calendar" class="w-4 h-4"></i> Jendela Ambil: {{ $donation->pickup_time_window }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <div class="text-2xl font-black text-gray-900">{{ $donation->quantity }} {{ $donation->unit }}</div>
                            <div class="flex gap-2">
                                @if($donation->status === 'pending')
                                    <form action="{{ route('mitra.donations.cancel', $donation->id) }}" method="POST" onsubmit="return confirm('Batalkan donasi ini?')">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-700 flex items-center gap-1 bg-red-50 px-3 py-1.5 rounded-lg border border-red-100 transition">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Batalkan
                                        </button>
                                    </form>
                                @endif

                                @if($donation->status === 'claimed')
                                    <form action="{{ route('mitra.donations.complete', $donation->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold text-green-600 hover:text-green-700 flex items-center gap-1 bg-green-50 px-3 py-1.5 rounded-lg border border-green-100 transition shadow-sm">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Konfirmasi Penyerahan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 rounded-2xl p-6">
                        <div>
                            <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Deskripsi Donasi</p>
                            <p class="text-gray-900 font-medium">{{ $donation->description ?: 'Tidak ada deskripsi' }}</p>
                        </div>

                        <!-- Lembaga Info -->
                        <div>
                            <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Informasi Lembaga & Penjemputan</p>
                            @if($donation->status === 'claimed' || $donation->status === 'completed')
                                @if($donation->lembaga)
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                                <i data-lucide="building" class="w-5 h-5"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-900">{{ $donation->lembaga->name }}</p>
                                                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-tighter">Lembaga Sosial</p>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-white rounded-xl p-4 border border-gray-100 space-y-3">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2 text-xs font-bold text-gray-400">
                                                    <i data-lucide="calendar-check" class="w-3.5 h-3.5"></i> JADWAL JEMPUT
                                                </div>
                                                <div class="text-sm font-black text-[#174413]">
                                                    {{ $donation->pickup_time ? \Carbon\Carbon::parse($donation->pickup_time)->format('H:i') : 'Segera' }}
                                                </div>
                                            </div>
                                            <div class="h-px bg-gray-50"></div>
                                            <div class="flex flex-col gap-2 text-sm text-gray-600">
                                                <div class="flex items-center gap-2">
                                                    <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
                                                    {{ $donation->lembaga->phone ?: 'Tidak ada nomor telepon' }}
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <i data-lucide="clock" class="w-4 h-4 text-gray-400"></i>
                                                    @if($donation->status === 'completed')
                                                        Diserahkan pada: {{ \Carbon\Carbon::parse($donation->delivered_at)->format('d M, H:i') }}
                                                    @else
                                                        Diklaim pada: {{ \Carbon\Carbon::parse($donation->claimed_at)->format('d M, H:i') }}
                                                    @endif
                                                </div>
                                            </div>
                                            @if($donation->status === 'claimed')
                                                <a href="https://wa.me/{{ $donation->lembaga->phone }}" target="_blank" class="flex items-center justify-center gap-2 w-full py-2 bg-green-500 text-white rounded-lg text-xs font-bold hover:bg-green-600 transition mt-2">
                                                    <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> Hubungi Lembaga
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="text-gray-500 italic text-sm py-2">Data lembaga penerima tidak ditemukan.</div>
                                @endif
                            @else
                                <div class="text-gray-500 italic text-sm py-2">Belum ada lembaga yang mengklaim donasi ini.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white rounded-3xl border border-gray-100">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="heart" class="w-8 h-8 text-gray-300"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Belum ada donasi</h3>
                <p class="text-gray-500">Anda belum pernah memberikan donasi ke lembaga sosial.</p>
            </div>
        @endforelse
    </div>

    <!-- Add Donation Modal -->
    <div x-show="isDialogOpen" class="fixed inset-0 z-[60] flex items-start justify-center overflow-y-auto p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div class="relative my-auto bg-white w-full max-w-lg rounded-3xl p-5 sm:p-6 shadow-2xl space-y-3 sm:space-y-4" @click.away="isDialogOpen = false">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-black text-gray-900">Tambah Donasi Makanan</h3>
                <button type="button" @click="isDialogOpen = false" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form action="{{ route('mitra.donations.store') }}" method="POST" class="space-y-3">
                @csrf
                
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Judul/Nama Makanan</label>
                    <input type="text" name="title" required placeholder="Contoh: Roti Sisa Produksi Hari Ini" class="w-full bg-gray-50 border border-gray-100 rounded-xl py-2 px-3 outline-none focus:ring-2 focus:ring-[#174413] transition text-xs">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Jumlah</label>
                        <input type="number" name="quantity" required min="1" placeholder="10" class="w-full bg-gray-50 border border-gray-100 rounded-xl py-2 px-3 outline-none focus:ring-2 focus:ring-[#174413] transition text-xs">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Satuan</label>
                        <select name="unit" x-model="selectedUnit" required class="w-full bg-gray-50 border border-gray-100 rounded-xl py-2 px-3 outline-none focus:ring-2 focus:ring-[#174413] transition text-xs cursor-pointer">
                            <option value="box">Box</option>
                            <option value="porsi">Porsi</option>
                            <option value="pcs">Pcs</option>
                            <option value="kg">Kg</option>
                        </select>
                    </div>
                </div>

                <!-- Info Satuan Dinamis -->
                <div class="bg-gray-50 border border-gray-100 rounded-xl p-2.5 text-[10px] text-gray-505 leading-relaxed">
                    <div class="font-black text-[#174413] uppercase tracking-wider mb-0.5 flex items-center gap-1">
                        <i data-lucide="info" class="w-3 h-3"></i> Info Satuan:
                    </div>
                    <span x-show="selectedUnit === 'box'">Makanan siap saji yang sudah dikemas dalam box/kotak secara individual (contoh: nasi box, bento box, roti per box). Praktis untuk langsung dibagikan.</span>
                    <span x-show="selectedUnit === 'porsi'">Porsi saji curah/prasmanan (contoh: lauk katering, sayur di tray saji). Perlu piring/wadah tambahan saat didistribusikan.</span>
                    <span x-show="selectedUnit === 'pcs'">Makanan satuan/butir utuh yang dihitung per buah (contoh: donat, roti satuan, buah apel/jeruk).</span>
                    <span x-show="selectedUnit === 'kg'">Bahan makanan mentah atau curah yang diukur dengan timbangan berat (contoh: beras, sayur segar curah, daging mentah).</span>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Batas Waktu Layak Konsumsi</label>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-0.5">
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Tanggal</span>
                            <input type="date" x-model="expiresDate" @change="$el.blur()" required class="w-full bg-gray-50 border border-gray-100 rounded-xl py-2 px-3 outline-none focus:ring-2 focus:ring-[#174413] transition text-xs text-gray-900">
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Jam & Menit</span>
                            <input type="time" x-model="expiresTime" @focus="$el.dataset.inputCount = 0" @input="$el.dataset.inputCount = parseInt($el.dataset.inputCount || 0) + 1; if ($el.value && parseInt($el.dataset.inputCount) >= 2) { $el.blur(); }" required class="w-full bg-gray-50 border border-gray-100 rounded-xl py-2 px-3 outline-none focus:ring-2 focus:ring-[#174413] transition text-xs text-gray-900">
                        </div>
                    </div>
                    <input type="hidden" name="expires_at" :value="expiresDate && expiresTime ? expiresDate + ' ' + expiresTime : ''">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Jam Mulai Ambil</label>
                        <input type="time" name="pickup_start_time" value="{{ $defaultStart }}" @focus="$el.dataset.inputCount = 0" @input="$el.dataset.inputCount = parseInt($el.dataset.inputCount || 0) + 1; if ($el.value && parseInt($el.dataset.inputCount) >= 2) { $el.blur(); }" required class="w-full bg-gray-50 border border-gray-100 rounded-xl py-2 px-3 outline-none focus:ring-2 focus:ring-[#174413] transition text-xs">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Jam Akhir Ambil</label>
                        <input type="time" name="pickup_end_time" value="{{ $defaultEnd }}" @focus="$el.dataset.inputCount = 0" @input="$el.dataset.inputCount = parseInt($el.dataset.inputCount || 0) + 1; if ($el.value && parseInt($el.dataset.inputCount) >= 2) { $el.blur(); }" required class="w-full bg-gray-50 border border-gray-100 rounded-xl py-2 px-3 outline-none focus:ring-2 focus:ring-[#174413] transition text-xs">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Deskripsi/Catatan (Opsional)</label>
                    <textarea name="description" rows="2" placeholder="Tambahkan catatan khusus untuk lembaga..." class="w-full bg-gray-50 border border-gray-100 rounded-xl py-2 px-3 outline-none focus:ring-2 focus:ring-[#174413] transition text-xs resize-none"></textarea>
                </div>

                <button type="submit" class="w-full bg-[#174413] hover:bg-[#0f2d0c] text-white px-6 py-3 rounded-xl font-bold transition flex items-center justify-center gap-2 shadow-lg shadow-green-900/20 text-sm">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Daftarkan Donasi
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
