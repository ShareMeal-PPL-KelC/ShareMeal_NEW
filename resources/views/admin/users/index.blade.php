<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data User - Admin ShareMeal</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#F9FAFB] text-gray-900">
    <div class="min-h-screen flex">
        
        <!-- SIDEBAR: Putih Bersih dengan Indikator Aktif -->
        <aside class="w-72 bg-white border-r border-gray-100 hidden lg:flex flex-col">
            <div class="p-8">
                <div class="flex items-center gap-3 font-bold text-2xl text-green-600">
                    <div class="bg-green-600 p-2 rounded-lg text-white">
                        <i data-lucide="utensils-crosses" class="w-6 h-6"></i>
                    </div>
                    <span>ShareMeal</span>
                </div>
            </div>
            
            <nav class="flex-1 px-4 space-y-2 mt-4">
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:bg-gray-50 rounded-xl transition-all">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 text-gray-400"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:bg-gray-50 rounded-xl transition-all">
                    <i data-lucide="shield-check" class="w-5 h-5 text-gray-400"></i>
                    <span class="font-medium">Verifikasi</span>
                </a>
                <!-- Indikator Aktif: Hijau Muda -->
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-3 bg-green-50 text-green-600 rounded-xl transition-all">
                    <i data-lucide="users" class="w-5 h-5 text-green-600"></i>
                    <span class="font-semibold text-green-700">Kelola User</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:bg-gray-50 rounded-xl transition-all">
                    <i data-lucide="book-open" class="w-5 h-5 text-gray-400"></i>
                    <span class="font-medium">Edukasi</span>
                </a>
            </nav>
            
            <div class="p-6 border-t border-gray-50">
                <div class="flex items-center gap-3 p-2">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-bold uppercase text-sm">AS</div>
                    <div>
                        <p class="text-sm font-bold">Admin ShareMeal</p>
                        <p class="text-xs text-gray-400">Administrator</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 overflow-y-auto">
            <div class="p-10 space-y-8 max-w-7xl mx-auto">
                
                <!-- HEADER TITLE -->
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Manajemen Data User</h1>
                        <p class="text-gray-500 mt-1">Kelola akun & moderasi pelanggaran (FR-20, FR-21)</p>
                    </div>
                </div>

                <!-- 6 SUMMARY CARDS SEJAJAR -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-5">
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Konsumen</p>
                        <p class="text-2xl font-bold text-blue-600 mt-1">{{ $allUsers->where('role', 'konsumen')->count() }}</p>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Mitra</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">{{ $allUsers->where('role', 'mitra')->count() }}</p>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Lembaga</p>
                        <p class="text-2xl font-bold text-purple-600 mt-1">{{ $allUsers->where('role', 'lembaga')->count() }}</p>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Aktif</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">{{ $allUsers->where('status', 'active')->count() }}</p>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Warning</p>
                        <p class="text-2xl font-bold text-orange-600 mt-1">{{ $allUsers->where('status', 'warned')->count() }}</p>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Blocked</p>
                        <p class="text-2xl font-bold text-red-600 mt-1">{{ $allUsers->where('status', 'blocked')->count() }}</p>
                    </div>
                </div>

                <!-- SEARCH & FILTER BAR -->
                <form action="{{ route('users.index') }}" method="GET" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row gap-4 items-center">
                    <div class="relative flex-1 w-full">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="w-full pl-12 pr-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-green-500 outline-none text-sm font-medium">
                    </div>
                    <div class="flex gap-3 w-full md:w-auto">
                        <select name="role" onchange="this.form.submit()" class="flex-1 md:w-44 px-4 py-3 bg-gray-50 border-none rounded-xl text-sm font-bold text-gray-600 outline-none focus:ring-2 focus:ring-green-500 cursor-pointer">
                            <option value="all">Semua Tipe</option>
                            <option value="konsumen" {{ request('role') == 'konsumen' ? 'selected' : '' }}>Konsumen</option>
                            <option value="mitra" {{ request('role') == 'mitra' ? 'selected' : '' }}>Mitra</option>
                            <option value="lembaga" {{ request('role') == 'lembaga' ? 'selected' : '' }}>Lembaga</option>
                        </select>
                        <select class="flex-1 md:w-44 px-4 py-3 bg-gray-50 border-none rounded-xl text-sm font-bold text-gray-600 outline-none focus:ring-2 focus:ring-green-500 cursor-pointer">
                            <option>Semua Status</option>
                        </select>
                    </div>
                </form>

                <!-- LIST KARTU USER: Padding 16px (p-4) -->
                <div class="grid grid-cols-1 gap-5">
                    @forelse($users as $user)
                    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm hover:border-green-200 transition-all group">
                        <div class="flex justify-between items-start">
                            <!-- Profil Section -->
                            <div class="flex gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-green-50 group-hover:text-green-600 transition-colors">
                                    <i data-lucide="user" class="w-7 h-7"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-lg font-bold text-gray-900 tracking-tight">{{ $user->name }}</h3>
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase border 
                                            {{ $user->role == 'konsumen' ? 'bg-blue-50 text-blue-600 border-blue-100' : '' }}
                                            {{ $user->role == 'mitra' ? 'bg-green-50 text-green-600 border-green-100' : '' }}
                                            {{ $user->role == 'lembaga' ? 'bg-purple-50 text-purple-600 border-purple-100' : '' }}">
                                            {{ $user->role }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-y-1 gap-x-6 mt-2 text-xs text-gray-500 font-medium">
                                        <span class="flex items-center gap-2"><i data-lucide="mail" class="w-3.5 h-3.5"></i>{{ $user->email }}</span>
                                        <span class="flex items-center gap-2"><i data-lucide="phone" class="w-3.5 h-3.5"></i>0812-3456-7890</span>
                                        <span class="flex items-center gap-2"><i data-lucide="calendar" class="w-3.5 h-3.5"></i>Bergabung: {{ $user->created_at->format('d M Y') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pojok Kanan Atas: Total Transaksi -->
                            <div class="text-right">
                                <p class="text-4xl font-black text-green-500 leading-none tracking-tighter">24</p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase mt-1 tracking-wider">Total Transaksi</p>
                            </div>
                        </div>

                        <!-- Action Bar -->
                        <div class="mt-6 pt-4 border-t border-gray-50 flex items-center justify-between">
                            <div class="flex gap-3">
                                <form action="{{ route('users.warn', $user->id) }}" method="POST">
                                    @csrf
                                    <button class="px-5 py-2.5 bg-white border border-orange-500 text-orange-500 text-xs font-bold rounded-xl hover:bg-orange-50 transition-colors flex items-center gap-2 shadow-sm">
                                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Beri Peringatan
                                    </button>
                                </form>
                                <form action="{{ route('users.block', $user->id) }}" method="POST">
                                    @csrf
                                    <button class="px-5 py-2.5 bg-white border border-red-500 text-red-500 text-xs font-bold rounded-xl hover:bg-red-50 transition-colors flex items-center gap-2 shadow-sm">
                                        <i data-lucide="ban" class="w-3.5 h-3.5"></i> Blokir Akun
                                    </button>
                                </form>
                            </div>
                            <a href="#" class="text-xs font-bold text-gray-400 hover:text-green-600 transition-colors flex items-center gap-1 uppercase tracking-wider">
                                Lihat Detail <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="bg-white p-20 text-center rounded-3xl border-2 border-dashed border-gray-100">
                        <i data-lucide="users" class="w-12 h-12 text-gray-200 mx-auto mb-4"></i>
                        <p class="text-gray-400 font-medium">Tidak ada data pengguna ditemukan</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </main>
    </div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();
    </script>
</body>
</html>
