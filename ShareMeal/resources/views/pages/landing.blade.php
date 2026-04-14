<x-layouts.app title="ShareMeal - Selamatkan Makanan">
    <div class="min-h-screen bg-white">
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
            <div class="container-shell flex h-16 items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#174413] text-lg font-black text-white">S</div>
                    <span class="text-xl font-bold text-[#174413]">ShareMeal</span>
                </a>
                <nav class="hidden items-center gap-6 md:flex">
                    <a href="#eksplorasi" class="text-sm font-medium text-slate-700 hover:text-[#174413]">Eksplorasi</a>
                    <a href="#fitur" class="text-sm font-medium text-slate-700 hover:text-[#174413]">Fitur</a>
                    <a href="#bergabung" class="text-sm font-medium text-slate-700 hover:text-[#174413]">Bergabung</a>
                    <a href="{{ route('consumer.dashboard') }}" class="flex items-center gap-3 border-l border-slate-200 pl-6">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#174413] text-sm font-bold text-white">KS</div>
                        <div>
                            <div class="text-sm font-bold text-slate-900">Konsumen</div>
                            <div class="text-xs text-slate-500">Dashboard</div>
                        </div>
                    </a>
                </nav>
            </div>
        </header>

        <section class="bg-gradient-to-br from-green-50 to-blue-50 py-20">
            <div class="container-shell grid items-center gap-12 md:grid-cols-2">
                <div>
                    <h1 class="text-5xl font-bold text-slate-900">Selamatkan Makanan,<span class="text-green-600"> Selamatkan Bumi</span></h1>
                    <p class="mt-6 max-w-2xl text-xl text-slate-600">ShareMeal menghubungkan bisnis pangan dengan konsumen dan lembaga sosial untuk mengurangi food waste dan membangun ekosistem pangan yang berkelanjutan.</p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a class="btn-primary" href="{{ route('register') }}">Mulai Sekarang</a>
                        <a class="btn-secondary" href="{{ route('consumer.search') }}">Cari Makanan</a>
                    </div>
                </div>
                <div class="overflow-hidden rounded-[2rem] shadow-2xl">
                    <img src="https://images.unsplash.com/photo-1563485571829-7032f428f3ce?auto=format&fit=crop&q=80&w=1200" alt="Fresh food" class="h-[28rem] w-full object-cover">
                </div>
            </div>
        </section>

        <section class="py-16">
            <div class="container-shell grid grid-cols-2 gap-8 text-center md:grid-cols-4">
                <div><div class="text-4xl font-bold text-green-600">500+</div><div class="mt-2 text-slate-600">Mitra Toko</div></div>
                <div><div class="text-4xl font-bold text-green-600">50K+</div><div class="mt-2 text-slate-600">Pengguna Aktif</div></div>
                <div><div class="text-4xl font-bold text-green-600">100K+</div><div class="mt-2 text-slate-600">Makanan Terselamatkan</div></div>
                <div><div class="text-4xl font-bold text-green-600">200+</div><div class="mt-2 text-slate-600">Lembaga Sosial</div></div>
            </div>
        </section>

        <section id="eksplorasi" class="bg-slate-50 py-20">
            <div class="container-shell">
                <div class="mb-16 text-center">
                    <h2 class="text-4xl font-bold text-slate-900">Bagaimana Cara Kerjanya?</h2>
                    <p class="mt-4 text-xl text-slate-600">Platform tiga arah yang menghubungkan semua pihak</p>
                </div>
                <div class="grid gap-8 md:grid-cols-3">
                    @foreach ([['title' => 'Untuk Mitra', 'desc' => 'Pelaku Usaha Pangan', 'items' => ['Kelola inventaris surplus makanan', 'Atur flash sale otomatis', 'Donasikan makanan ke lembaga sosial', 'Kurangi food waste & biaya TPA'], 'route' => route('mitra.dashboard')], ['title' => 'Untuk Konsumen', 'desc' => 'Pembeli Cerdas', 'items' => ['Beli makanan berkualitas dengan harga diskon', 'Cari toko terdekat dengan GPS', 'Notifikasi flash sale real-time', 'Berkontribusi kurangi food waste'], 'route' => route('consumer.dashboard')], ['title' => 'Untuk Lembaga Sosial', 'desc' => 'Penerima Donasi', 'items' => ['Terima donasi makanan layak konsumsi', 'Klaim donasi first-come first-served', 'Tracking logistik real-time', 'Riwayat penerimaan lengkap'], 'route' => route('lembaga.dashboard')]] as $card)
                        <div class="card p-8">
                            <h3 class="text-2xl font-bold text-slate-900">{{ $card['title'] }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ $card['desc'] }}</p>
                            <ul class="mt-6 space-y-3 text-slate-600">
                                @foreach ($card['items'] as $item)
                                    <li class="flex gap-3"><span class="font-bold text-green-600">✓</span><span>{{ $item }}</span></li>
                                @endforeach
                            </ul>
                            <a href="{{ $card['route'] }}" class="btn-secondary mt-6 w-full">Pelajari Lebih Lanjut</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="fitur" class="py-20">
            <div class="container-shell">
                <div class="mb-16 text-center">
                    <h2 class="text-4xl font-bold text-slate-900">Fitur Unggulan</h2>
                    <p class="mt-4 text-xl text-slate-600">Teknologi canggih untuk distribusi pangan berkelanjutan</p>
                </div>
                <div class="grid gap-8 md:grid-cols-3">
                    @foreach ([['Flash Sale Timer', 'Countdown otomatis untuk makanan near-expired dengan diskon bertahap'], ['Location-Based Search', 'Temukan toko terdekat dengan teknologi GPS real-time'], ['Rating & Review', 'Sistem rating transparan dengan upload foto bukti kualitas'], ['Verifikasi Admin', 'Semua mitra dan lembaga terverifikasi untuk keamanan maksimal'], ['Kurangi Food Waste', 'Distribusi otomatis ke Jual atau Donasi berdasarkan kelayakan'], ['Multi-Cabang', 'Kelola inventaris berbagai cabang dalam satu akun induk']] as $feature)
                        <div class="card p-8 text-center">
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-2xl text-green-700">•</div>
                            <h3 class="text-xl font-semibold text-slate-900">{{ $feature[0] }}</h3>
                            <p class="mt-2 text-slate-600">{{ $feature[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="bg-green-50 py-20">
            <div class="container-shell grid items-center gap-12 md:grid-cols-2">
                <div class="overflow-hidden rounded-[2rem] shadow-xl">
                    <img src="https://images.unsplash.com/photo-1593113702251-272b1bc414a9?auto=format&fit=crop&q=80&w=1200" alt="Food donation" class="h-[28rem] w-full object-cover">
                </div>
                <div>
                    <div class="text-sm font-semibold uppercase tracking-[0.3em] text-green-600">Dampak Positif</div>
                    <h2 class="mt-4 text-4xl font-bold text-slate-900">Bersama Membangun Masa Depan Berkelanjutan</h2>
                    <p class="mt-6 text-lg text-slate-600">ShareMeal tidak hanya mengurangi food waste, tetapi juga membantu lembaga sosial mendapatkan akses pangan berkualitas dan memberikan peluang konsumen untuk berkontribusi pada lingkungan.</p>
                    <div class="mt-8 space-y-4">
                        @foreach ([['Kurangi Limbah TPA', 'Mencegah ton makanan berakhir di tempat pemrosesan akhir'], ['Bantu Lembaga Sosial', 'Distribusi makanan layak konsumsi ke panti asuhan dan yayasan'], ['Hemat Biaya Operasional', 'Mitra menghemat biaya pembuangan dan dapatkan tambahan pendapatan']] as $impact)
                            <div class="flex gap-3"><div class="mt-1 flex h-6 w-6 items-center justify-center rounded-full bg-green-600 text-sm font-bold text-white">✓</div><div><div class="font-semibold">{{ $impact[0] }}</div><div class="text-slate-600">{{ $impact[1] }}</div></div></div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section id="bergabung" class="bg-green-600 py-20 text-white">
            <div class="container-shell max-w-4xl text-center">
                <h2 class="text-4xl font-bold">Siap Bergabung dengan ShareMeal?</h2>
                <p class="mt-6 text-xl text-green-100">Jadilah bagian dari gerakan mengurangi food waste di Indonesia</p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="{{ route('register') }}" class="btn bg-white text-green-700 hover:bg-green-100">Daftar Sekarang</a>
                    <a href="{{ route('login') }}" class="btn border border-white text-white hover:bg-green-700">Sudah Punya Akun</a>
                </div>
            </div>
        </section>

        <footer class="bg-slate-900 py-12 text-slate-300">
            <div class="container-shell grid gap-8 md:grid-cols-4">
                <div>
                    <div class="flex items-center gap-3"><div class="flex h-8 w-8 items-center justify-center rounded-xl bg-white text-sm font-black text-[#174413]">S</div><span class="font-bold text-white">ShareMeal</span></div>
                    <p class="mt-4 text-sm">Platform digital untuk mengoptimalkan pemanfaatan surplus pangan dan mengurangi food waste.</p>
                </div>
                <div><h3 class="font-semibold text-white">Untuk Pengguna</h3><ul class="mt-4 space-y-2 text-sm"><li><a href="{{ route('consumer.dashboard') }}" class="hover:text-green-400">Konsumen</a></li><li><a href="{{ route('mitra.dashboard') }}" class="hover:text-green-400">Mitra Toko</a></li><li><a href="{{ route('lembaga.dashboard') }}" class="hover:text-green-400">Lembaga Sosial</a></li></ul></div>
                <div><h3 class="font-semibold text-white">Perusahaan</h3><ul class="mt-4 space-y-2 text-sm"><li><a href="#" class="hover:text-green-400">Tentang Kami</a></li><li><a href="#" class="hover:text-green-400">Blog</a></li><li><a href="#" class="hover:text-green-400">Kontak</a></li></ul></div>
                <div><h3 class="font-semibold text-white">Legal</h3><ul class="mt-4 space-y-2 text-sm"><li><a href="#" class="hover:text-green-400">Kebijakan Privasi</a></li><li><a href="#" class="hover:text-green-400">Syarat & Ketentuan</a></li></ul></div>
            </div>
            <div class="container-shell mt-8 border-t border-slate-800 pt-8 text-center text-sm">&copy; 2026 ShareMeal. All rights reserved.</div>
        </footer>
    </div>
</x-layouts.app>
