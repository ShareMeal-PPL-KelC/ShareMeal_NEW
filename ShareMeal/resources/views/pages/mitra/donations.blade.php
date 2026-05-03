@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="{ isDialogOpen: false }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Riwayat Donasi</h1>
            <p class="text-gray-600 mt-1">Daftar donasi Anda dan informasi lembaga penerima</p>
        </div>
        <button @click="isDialogOpen = true" class="bg-[#174413] hover:bg-[#0f2d0c] text-white px-6 py-3 rounded-xl font-bold transition flex items-center gap-2 shadow-lg shadow-green-900/20">
            <i data-lucide="plus" class="w-5 h-5"></i>
            Tambah Donasi
        </button>
    </div>

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
                            <p class="text-sm text-gray-400 font-medium mt-2">Didaftarkan pada: {{ \Carbon\Carbon::parse($donation->created_at)->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-black text-gray-900">{{ $donation->quantity }} {{ $donation->unit }}</div>
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
                            <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Informasi Lembaga</p>
                            @if($donation->status === 'claimed' || $donation->status === 'completed')
                                @if($donation->lembaga)
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                                <i data-lucide="building" class="w-5 h-5"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-900">{{ $donation->lembaga->name }}</p>
                                                <p class="text-xs text-gray-500">Lembaga Sosial</p>
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-1 text-sm text-gray-600 pl-13">
                                            <div class="flex items-center gap-2">
                                                <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
                                                {{ $donation->lembaga->phone ?: 'Tidak ada nomor telepon' }}
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i data-lucide="mail" class="w-4 h-4 text-gray-400"></i>
                                                {{ $donation->lembaga->email }}
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i data-lucide="clock" class="w-4 h-4 text-gray-400"></i>
                                                Diklaim pada: {{ \Carbon\Carbon::parse($donation->claimed_at)->format('d M Y, H:i') }}
                                            </div>
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
    <div x-show="isDialogOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white w-full max-w-xl rounded-3xl p-8 shadow-2xl space-y-6" @click.away="isDialogOpen = false">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-black text-gray-900">Tambah Donasi Makanan</h3>
                <button type="button" @click="isDialogOpen = false" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form action="{{ route('mitra.donations.store') }}" method="POST" class="space-y-5">
                @csrf
                
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest">Judul/Nama Makanan</label>
                    <input type="text" name="title" required placeholder="Contoh: Roti Sisa Produksi Hari Ini" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest">Jumlah</label>
                        <input type="number" name="quantity" required min="1" placeholder="10" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest">Satuan</label>
                        <select name="unit" required class="w-full bg-gray-50 border border-gray-100 rounded-xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition">
                            <option value="bungkus">Bungkus</option>
                            <option value="porsi">Porsi</option>
                            <option value="pcs">Pcs</option>
                            <option value="kg">Kg</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest">Deskripsi/Catatan (Opsional)</label>
                    <textarea name="description" rows="3" placeholder="Tambahkan catatan khusus untuk lembaga pengambil..." class="w-full bg-gray-50 border border-gray-100 rounded-xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition"></textarea>
                </div>

                <button type="submit" class="w-full bg-[#174413] hover:bg-[#0f2d0c] text-white px-6 py-4 rounded-xl font-bold transition flex items-center justify-center gap-2 shadow-lg shadow-green-900/20">
                    <i data-lucide="check" class="w-5 h-5"></i>
                    Daftarkan Donasi
                </button>
            </form>
        </div>
    </div>
</div>
@endsection