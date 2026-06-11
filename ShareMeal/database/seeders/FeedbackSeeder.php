<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $consumer = User::where('email', 'budi@example.com')->first();
        $mitra1 = User::where('email', 'mitra@example.com')->first();
        $mitra2 = User::where('email', 'warmindo@example.com')->first();
        $lembaga = User::where('email', 'lembaga@example.com')->first();

        // Fallback user if seeder runs on clean db without users
        $consumerId = $consumer ? $consumer->id : 1;
        $mitra1Id = $mitra1 ? $mitra1->id : 2;
        $mitra2Id = $mitra2 ? $mitra2->id : 3;
        $lembagaId = $lembaga ? $lembaga->id : 4;

        $feedbacks = [
            [
                'user_id' => $consumerId,
                'category' => 'fitur',
                'subject' => 'Rekomendasi Makanan Berdasarkan Lokasi',
                'description' => 'Saran saya tambahkan fitur rekomendasi toko terdekat di halaman utama secara otomatis menggunakan geolokasi browser untuk menghemat waktu pencarian makanan.',
                'rating' => 4,
                'screenshots' => null,
                'status' => 'pending',
            ],
            [
                'user_id' => $mitra1Id,
                'category' => 'bug',
                'subject' => 'Gagal Update Stok di HP Android Lama',
                'description' => 'Saat saya mencoba mengubah stok roti gandum melalui HP Android versi lama, halamannya macet dan stok tidak tersimpan di database. Mohon kompatibilitas browser diperbaiki.',
                'rating' => 2,
                'screenshots' => null,
                'status' => 'resolved',
            ],
            [
                'user_id' => $lembagaId,
                'category' => 'ui_ux',
                'subject' => 'Kontras Warna Halaman Donasi',
                'description' => 'Warna tombol klaim donasi agak kurang kontras ketika dibaca di bawah sinar matahari langsung, mohon diperjelas atau dipertebal warna hijaunya supaya lebih ramah aksesibilitas.',
                'rating' => 3,
                'screenshots' => null,
                'status' => 'pending',
            ],
            [
                'user_id' => $consumerId,
                'category' => 'other',
                'subject' => 'Layanan Pelanggan Sangat Baik',
                'description' => 'Secara keseluruhan aplikasinya sangat membantu mengurangi food waste di kos saya. Semoga jangkauan mitranya semakin luas ke luar kota Bandung.',
                'rating' => 5,
                'screenshots' => null,
                'status' => 'resolved',
            ],
            [
                'user_id' => $mitra2Id,
                'category' => 'bug',
                'subject' => 'Notifikasi Pesanan Baru Terlambat Masuk',
                'description' => 'Suara notifikasi suara bel pesanan baru kadang terlambat masuk sekitar 5 menit setelah pelanggan melakukan checkout pembayaran di aplikasi.',
                'rating' => 3,
                'screenshots' => null,
                'status' => 'pending',
            ],
            [
                'user_id' => $lembagaId,
                'category' => 'fitur',
                'subject' => 'Ekspor Laporan Riwayat Donasi Bulanan',
                'description' => 'Tolong tambahkan tombol ekspor laporan donasi bulanan ke format Excel atau PDF untuk memudahkan kebutuhan LPJ bulanan yayasan kami.',
                'rating' => 5,
                'screenshots' => null,
                'status' => 'resolved',
            ],
        ];

        foreach ($feedbacks as $feedbackData) {
            Feedback::create($feedbackData);
        }
    }
}
