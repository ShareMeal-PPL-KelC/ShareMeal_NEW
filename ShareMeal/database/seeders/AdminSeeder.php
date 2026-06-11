<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@sharemeal.id'],
            [
                'name' => 'Admin ShareMeal',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'active',
                'is_verified' => true,
                'joined_at' => now(),
            ]
        );

        // Seed some initial admin logs
        \App\Models\AdminLog::updateOrCreate(
            ['action' => 'verify_approve', 'details' => 'Menyetujui verifikasi berkas akun: Toko Roti Makmur'],
            [
                'admin_id' => $admin->id,
                'target_id' => 1,
                'ip_address' => '192.168.1.10',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ]
        );

        \App\Models\AdminLog::updateOrCreate(
            ['action' => 'education_create', 'details' => 'Membuat artikel edukasi baru: "Cara Mengolah Makanan Sisa Menjadi Kompos"'],
            [
                'admin_id' => $admin->id,
                'target_id' => 1,
                'ip_address' => '192.168.1.10',
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ]
        );

        \App\Models\AdminLog::updateOrCreate(
            ['action' => 'report_warn', 'details' => 'Menindaklanjuti laporan #1 dengan memberi peringatan ke Mitra Warmindo Barokah. Alasan: Makanan basi saat diterima konsumen'],
            [
                'admin_id' => $admin->id,
                'target_id' => 1,
                'ip_address' => '10.0.2.15',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ]
        );

        \App\Models\AdminLog::updateOrCreate(
            ['action' => 'warn_user', 'details' => 'Mengirim peringatan resmi kepada Budi Santoso. Alasan: Terlalu sering membatalkan klaim donasi sepihak'],
            [
                'admin_id' => $admin->id,
                'target_id' => 1,
                'ip_address' => '192.168.1.15',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ]
        );

        \App\Models\AdminLog::updateOrCreate(
            ['action' => 'verify_reject', 'details' => 'Menolak verifikasi berkas akun: Warung Sunda Kang Pipit dengan alasan: Dokumen NIB kadaluarsa'],
            [
                'admin_id' => $admin->id,
                'target_id' => 3,
                'ip_address' => '192.168.1.10',
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ]
        );
    }
}
