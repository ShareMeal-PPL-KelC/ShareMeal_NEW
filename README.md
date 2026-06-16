<p align="center">
  <img src="ShareMeal/public/images/logo.png" alt="ShareMeal Logo" width="200"/>
</p>

# ShareMeal: Platform Penyelamat Food Waste & Penguat Ketahanan Pangan

[![Laravel Version](https://img.shields.io/badge/laravel-%23FF2D20.svg?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![Vite](https://img.shields.io/badge/vite-%23646CFF.svg?style=flat&logo=vite&logoColor=white)](https://vitejs.dev)
[![TailwindCSS](https://img.shields.io/badge/tailwindcss-%2338B2AC.svg?style=flat&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/alpine.js-%238BC0D0.svg?style=flat)](https://alpinejs.dev)

**ShareMeal** adalah platform digital berbasis web yang dirancang khusus untuk mengatasi isu sampah makanan (food waste) sekaligus memperkuat ketahanan pangan (food security). Platform ini menghubungkan bisnis kuliner (restoran, toko roti, kafe) yang memiliki makanan surplus layak konsumsi dengan konsumen yang ingin membelinya dengan harga diskon, serta lembaga sosial (LSM, panti asuhan, yayasan) yang menyalurkan donasi makanan secara transparan dan efisien.

---

## Tampilan Platform

<p align="center">
  <img src="ShareMeal/public/images/LandingPage.png" alt="Landing Page ShareMeal" width="800" style="border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);"/>
</p>

---

## Alur Kerja Sistem (System Roles)

Sistem ini didukung oleh 4 tingkatan peran (role-based access control) yang terintegrasi secara dinamis. Berikut adalah diagram alir komprehensif yang memetakan keterkaitan alur kerja dan aksi dari setiap aktor dalam ekosistem ShareMeal:

```mermaid
graph TD
  %% Style definitions
  classDef admin fill:#ff9f43,stroke:#333,stroke-width:2px;
  classDef mitra fill:#54a0ff,stroke:#333,stroke-width:2px;
  classDef consumer fill:#f368e0,stroke:#333,stroke-width:2px;
  classDef lembaga fill:#10ac84,stroke:#333,stroke-width:2px;
  classDef system fill:#8395a7,stroke:#333,stroke-dasharray: 5 5;

  subgraph Admin ["Peran: Admin"]
    A1[Menerima Dokumen Verifikasi]:::admin --> A2{Tinjau Kelayakan}:::admin
    A2 -->|Disetujui| A3[Aktifkan Akun Mitra/Lembaga]:::admin
    A2 -->|Ditolak| A4[Kirim Alasan Penolakan ke User]:::admin
    A5[Terima Laporan Makanan Basi / Masalah]:::admin --> A6{Investigasi Mitra}:::admin
    A6 -->|Terbukti Melanggar| A7[Sanksi / Bekukan Akun Mitra]:::admin
    A6 -->|Selesai / Clear| A8[Catat Hasil ke Audit Log]:::admin
  end

  subgraph Mitra ["Peran: Mitra"]
    M1[Registrasi & Unggah Legalitas Usaha]:::mitra --> A1
    A3 --> M2[Akun Aktif: Kelola Produk]:::mitra
    M2 --> M3[Unggah Makanan Surplus]:::mitra
    M3 --> M4[Tentukan Diskon, Expired & Pickup Window]:::mitra
    M4 --> M5{Kondisi Waktu}:::mitra
    M5 -->|Ada Pesanan| M6[Siapkan & Kemas Makanan]:::mitra
    M6 --> M7[Update Status Pesanan Ready]:::mitra
    M7 --> M8[Serahkan ke Konsumen/Kurir]:::mitra
  end

  subgraph Consumer ["Peran: Konsumen"]
    C1[Registrasi Akun]:::consumer --> C2[Cari Makanan / Donasi Terdekat]:::consumer
    C2 --> C3[Masukkan Keranjang & Checkout]:::consumer
    C2 --> C4{Pilih Metode Terima}:::consumer
    C4 -->|Ambil Sendiri| C5[Datang ke Toko Mitra]:::consumer
    C4 -->|Pengantaran| C6[Tunggu Pengiriman Kurir]:::consumer
    C5 & C6 --> C7[Periksa Kelayakan Makanan]:::consumer
    C7 --> C8{Makanan Baik?}:::consumer
    C8 -->|Ya| C9[Konfirmasi Selesai & Beri Rating]:::consumer
    C8 -->|Tidak / Basi| C10[Laporkan Masalah]:::consumer --> A5
  end

  subgraph Lembaga ["Peran: Lembaga"]
    L1[Registrasi & Unggah Akta NGO]:::lembaga --> A1
    A3 --> L2[Akun Aktif: Pantau Donasi Masuk]:::lembaga
    L2 --> L3[Klaim Kuota Donasi Skala Besar]:::lembaga
    L3 --> L4[Jemput Donasi ke Lokasi Mitra]:::lembaga
    L4 --> L5[Salurkan ke Penerima Manfaat]:::lembaga
    L5 --> L6[Unggah Laporan Bukti Penyaluran]:::lembaga
  end

  subgraph System ["Otomatisasi Sistem"]
    S1[Laravel Scheduler Mendeteksi 2 Jam Menjelang Expired]:::system --> S2[Ubah Status Produk ke Donasi Gratis]:::system
    S2 --> S3[Setel Harga & Stok Jual ke 0]:::system
    S3 --> S4[Kirim Notifikasi Real-time]:::system
  end

  M5 -->|2 Jam Sebelum Expired & Belum Terjual| S1
  S4 --> C2
  S4 --> L2
  M8 --> C7
  M8 --> L4
```

### 💡 Penjelasan Detail Interaksi Alur Kerja:

1. **Verifikasi Akun & Validasi Bisnis (KYB):**
   * **Mitra/Lembaga** melakukan registrasi akun baru dan mengunggah dokumen legalitas mereka (`M1`/`L1`).
   * **Admin** meninjau data tersebut (`A1` & `A2`). Jika valid, akun akan diaktifkan (`A3`). Jika tidak valid, pengajuan ditolak (`A4`) dengan mencantumkan alasan penolakan agar user dapat memperbaikinya.
2. **Transaksi Penjualan Makanan Surplus:**
   * **Mitra** mengunggah makanan surplus berbayar (`M3`), lalu pembeli (**Konsumen**) menjelajahi produk tersebut (`C2`) dan membelinya.
   * Konsumen mengambil makanan langsung di lokasi toko (`C5`) atau dikirim via kurir (`C6`), lalu melakukan pengecekan kualitas fisik pangan (`C7`).
3. **Penyelamatan Pangan Otomatis (Laravel Scheduler):**
   * Jika produk komersial **Mitra** belum laku terjual hingga **2 jam sebelum kedaluwarsa** (`M5`), scheduler sistem (`S1`) otomatis mengonversi produk tersebut menjadi **Donasi** (`S2` & `S3`) dan memicu notifikasi real-time (`S4`).
   * **Lembaga** (`L2`) atau **Konsumen** (`C2`) terdekat akan segera mendapat pemberitahuan untuk melakukan klaim donasi sebelum makanan basi.
4. **Penyaluran Donasi Skala Besar (Lembaga):**
   * **Lembaga** sosial melakukan klaim paket donasi (`L3`), melakukan penjemputan fisik ke lokasi toko (`L4`), menyalurkannya ke masyarakat binaan (`L5`), dan wajib mengunggah bukti transparansi penyaluran (`L6`).
5. **Pengaduan Kelayakan & Moderasi:**
   * Jika makanan yang diterima dalam keadaan tidak layak/basi (`C8`), **Konsumen/Lembaga** mengajukan laporan masalah (`C10`).
   * **Admin** menginvestigasi laporan (`A5` & `A6`) dan berhak memberikan sanksi/pemblokiran terhadap Mitra yang melanggar aturan pangan (`A7`), serta mencatat setiap tindakan di audit log (`A8`).

---

## Fitur Utama Berdasarkan Peran

### 1. Konsumen (Consumer)
Konsumen dapat menyelamatkan makanan surplus berkualitas dengan harga terjangkau:
*   **Impact Dashboard:** Statistik dampak individu seperti porsi makanan terselamatkan, total uang dihemat, estimasi pengurangan emisi CO2, dan daftar toko favorit.
*   **Critical Alert:** Peringatan langsung di dashboard saat pesanan aktif sedang dikirim (Shipping) untuk dipantau secara real-time.
*   **Filter Pencarian Toko:** Menemukan mitra resto berdasarkan filter khusus: Halal, Bakery, Healthy, dan Indonesian.
*   **Cart Reservation & Stock Lock:** Sistem keranjang yang mengunci stok makanan selama 5 menit untuk menghindari rebutan stok (double ordering).
*   **Sistem Checkout Fleksibel:**
    *   Mendukung opsi Ambil Sendiri (Pickup) atau Diantar (Delivery).
    *   Pemilihan slot waktu pengantaran per 1 jam yang disesuaikan kapasitas pengiriman mitra.
*   **Pembayaran Digital:** Pembayaran simulasi QRIS, GoPay, OVO, dan DANA.
*   **Review & Rating:** Konsumen dapat memberikan ulasan untuk pesanan selesai, dengan aturan ulasan hanya dapat diedit atau dihapus dalam waktu 2 menit setelah dikirim.
*   **Problem Report:** Melaporkan jika menerima makanan bermasalah (kedaluwarsa, kualitas buruk, porsi salah) dengan menyertakan bukti foto langsung ke admin.
*   **Eco-Education:** Membaca artikel pencegahan food waste dengan sistem poin/gamification (Level Eco-Warrior).

### 2. Mitra Usaha (Merchant / Store)
Pemilik usaha makanan yang ingin menekan kerugian dari produk yang tidak terjual:
*   **Dashboard Performa Bisnis:** Menampilkan total produk, total pendapatan, porsi terselamatkan, jumlah donasi terdistribusi, rating ulasan, serta daftar pesanan masuk.
*   **Peringatan Operasional (Critical Alert):**
    *   Notifikasi darurat jika ada produk yang mendekati masa kedaluwarsa (< 4 jam).
    *   Notifikasi stok menipis (< 5 porsi).
*   **Profil Usaha & KYB:** Melengkapi data bisnis, jam buka, biaya kirim, dan mengunggah dokumen legalitas (SIUP, NIB, KTP, Halal) untuk diverifikasi oleh admin.
*   **Manajemen Produk:** Kelola menu dengan penentuan tanggal/jam kedaluwarsa.
*   **Flash Sale & Toggle Donation:**
    *   Mengaktifkan harga diskon instan (Flash Sale).
    *   Mengubah produk sisa komersial menjadi donasi gratis secara cepat (Toggle Donation).
*   **Pengelolaan Pesanan:** Menerima, memproses, mengubah status pesanan, menunda pesanan (delay order) dengan menyertakan alasan operasional, serta memvalidasi kode pengambilan (pickup code).
*   **Pengelolaan Donasi:** Membuat slot donasi makanan manual lengkap dengan kuantitas, unit (pcs/kg/box), jam penjemputan, dan memantau status pengambilan oleh lembaga sosial.

### 3. Lembaga Sosial (Social Organization / NGO)
Pihak terverifikasi yang mengorganisir penyaluran makanan donasi kepada kelompok rentan:
*   **Dashboard Penyaluran:** Menampilkan total donasi terselamatkan, jumlah donasi aktif, penerima manfaat (beneficiaries count), serta donasi yang tersedia untuk diklaim.
*   **Klaim Donasi:** Mengklaim donasi makanan yang tersedia dari mitra resto terdekat dengan menyertakan waktu penjemputan.
*   **Critical Alert:** Peringatan darurat apabila donasi yang diklaim telah berstatus "Siap Diambil" agar segera dijemput.
*   **Laporan Masalah Donasi:** Melaporkan kepada admin jika makanan donasi yang diterima tidak layak konsumsi.

### 4. Administrator (Admin Platform)
Pengelola pusat yang memoderasi platform untuk memastikan keamanan dan integritas ekosistem:
*   **Dashboard Analitik:** Memantau statistik user, total transaksi, GMV platform, pengurangan CO2, dan grafik aktivitas log terbaru.
*   **Verifikasi Akun (KYB Verification):** Meninjau berkas pendaftaran Mitra Usaha dan Lembaga Sosial. Admin dapat menyetujui (Approve) or menolak (Reject) pendaftaran disertai alasan penolakan.
*   **Manajemen Pengguna:** Tindakan penertiban seperti memberikan peringatan resmi (Warnings), membekukan akun (Block), or membuka blokir (Unblock).
*   **Penyelesaian Laporan Masalah (Problem Report Moderation):** Memeriksa laporan masalah dari Konsumen/Lembaga Sosial dengan opsi mengabaikan (Dismiss), memberikan peringatan ke mitra (Warn Mitra), or memblokir mitra (Block Mitra).
*   **Platform Feedback Management:** Mengelola keluhan/saran performa platform dari semua pengguna (ditandai Resolved/Pending).
*   **Ekspor Data:** Eksport data transaksi platform (CSV) dan ekspor laporan penyaluran dampak donasi (Excel & PDF) untuk kebutuhan pelaporan dampak eksternal.
*   **Log Audit Keamanan (Admin Logs):** Semua aksi admin tercatat secara otomatis lengkap dengan detail aksi, target, timestamp, dan IP address untuk kepatuhan keamanan.

---

## Fitur Inovasi Teknologi (Core Tech Innovations)

*   **Sistem Donasi Otomatis (Auto Donation System):** Jika sebuah produk tergolong donatable (dapat didonasikan) dan masa kedaluwarsa tersisa kurang dari 2 jam (expires_at <= now() + 2 jam), sistem secara otomatis memindahkan stok produk dari marketplace komersial ke status donasi, serta mengirimkan notifikasi real-time kepada Lembaga Sosial terdekat untuk diselamatkan.
*   **Cart Reservation & Stock Lock:** Ketika produk dimasukkan ke dalam keranjang belanja, stok asli pada database langsung berkurang dengan batas waktu reservasi selama 5 menit. Jika checkout tidak dilakukan dalam 5 menit, sistem secara otomatis mengembalikan stok ke inventori toko.
*   **Delivery Time Slot & Hourly Capacity Cap:** Pencegahan overload logistik kurir dengan membatasi jumlah pengiriman maksimum yang diperbolehkan per jam operasional restoran.
*   **Sistem Notifikasi Terpusat:** Didukung oleh 23 sistem notifikasi terintegrasi (email & in-app) yang mendeteksi perubahan status pesanan, donasi, laporan, kedaluwarsa produk, dan verifikasi akun.

---

## Teknologi yang Digunakan (Tech Stack)

*   **Backend Framework:** Laravel (PHP 8.2+)
*   **Frontend Library:** TailwindCSS, Alpine.js (Untuk reaktivitas UI instan)
*   **Database:** MySQL / SQLite
*   **Bundler & Assets Compiler:** Vite (versi 8.0.10)
*   **Broadcasting/Real-time Engine:** Laravel Reverb (WebSocket Server)

---

## Cara Menjalankan Proyek (Local Installation)

1.  **Clone Repositori:**
    ```bash
    git clone https://github.com/ShareMeal-PPL-KelC/ShareMeal_NEW.git
    cd ShareMeal_NEW/ShareMeal
    ```

2.  **Instalasi Dependensi PHP:**
    ```bash
    composer install
    ```

3.  **Instalasi Dependensi JavaScript:**
    ```bash
    npm install
    ```

4.  **Konfigurasi Environment:**
    Salin file `.env.example` menjadi `.env` dan konfigurasikan koneksi database serta broadcasting Anda.
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    Pastikan parameter penyiaran di `.env` telah disesuaikan (misalnya `BROADCAST_CONNECTION=reverb`).

5.  **Instalasi Penyiaran (Broadcasting Setup):**
    Jalankan perintah instalasi penyiaran untuk membuat berkas konfigurasi penyiaran dan mengintegrasikan Laravel Echo:
    ```bash
    php artisan install:broadcasting
    ```

6.  **Migrasi & Seed Database:**
    Jalankan migrasi database beserta data dummy seed awal untuk akun tes.
    ```bash
    php artisan migrate --seed
    ```

7.  **Jalankan Server Laravel Reverb (WebSocket):**
    Untuk mengaktifkan fitur real-time broadcasting, jalankan server WebSocket Reverb:
    ```bash
    php artisan reverb:start
    ```

8.  **Jalankan Vite Server (Asset compiler):**
    ```bash
    npm run dev
    ```

9.  **Jalankan Laravel Dev Server:**
    ```bash
    php artisan serve
    ```
    Buka `http://localhost:8000` pada browser Anda.