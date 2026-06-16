<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use App\Models\UserProfile;
use App\Notifications\IncomingOrderNotification;

/**
 * PBI-22: Notifikasi Pesanan Masuk Mitra
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi22NotifikasiPesananMasukMitraTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * [PBI 22] Notifikasi Sistem - Mitra
     * Deskripsi: Sebagai mitra, saya ingin menerima notifikasi pesanan masuk agar dapat segera diproses.
     */
    public function test_positive_mitra_menerima_notifikasi_pesanan_masuk(): void
    {
        $this->browse(function (Browser $browser) {
            // --- 1. SETUP DATA (Dibelakang Layar) ---
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Resto Berkah',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Resto Berkah',
                'business_type' => 'Bakery',
                'business_address' => 'Jl. Pahlawan No. 10',
                'business_contact' => '08123456789',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko roti lezat.',
                'phone' => '08123456789',
                'address' => 'Jl. Pahlawan No. 10',
                'opening_hours' => '08:00 - 20:00'
            ]);

            $consumer = User::factory()->create([
                'role' => 'consumer',
                'name' => 'Budi Pelanggan'
            ]);

            $order = Order::create([
                'customer_id' => $consumer->id,
                'mitra_id' => $mitra->id,
                'total_amount' => 75000,
                'status' => 'pending',
                'receiving_method' => 'pickup',
                'pickup_start_time' => '09:00',
                'pickup_end_time' => '10:00'
            ]);

            $mitra->notify(new IncomingOrderNotification($order));

            // --- 2. EXECUTION ---
            
            // Bypass UI Login yang sering error karena animasi JavaScript di landing page
            $browser->loginAs($mitra)
                    ->visitRoute('notifications.index')
                    
                    // Step 6: Tunggu elemen notifikasi muncul
                    // Menunggu teks 'Pesanan Baru Masuk!' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Pesanan Baru Masuk!', 15)
                    
                    // Step 7: Validasi Pesan
                    // Memastikan teks 'Pesanan Baru Masuk!' terlihat pada halaman browser
                    ->assertSee('Pesanan Baru Masuk!')
                    // Memastikan teks 'Anda menerima pesanan baru dari Budi Pelanggan sejumlah Rp 75.000' terlihat pada halaman browser
                    ->assertSee('Anda menerima pesanan baru dari Budi Pelanggan sejumlah Rp 75.000');
        });
    }

    public function test_negative_mitra_tidak_menerima_notifikasi_pesanan_mitra_lain(): void
    {
        $this->browse(function (Browser $browser) {
            $mitraA = User::factory()->create(['role' => 'mitra', 'name' => 'Resto A', 'is_verified' => true]);
            $mitraB = User::factory()->create(['role' => 'mitra', 'name' => 'Resto B', 'is_verified' => true]);
            
            UserProfile::create([
                'user_id' => $mitraB->id,
                'business_name' => 'Resto B',
                'business_type' => 'Bakery',
                'business_address' => 'Jl. Mawar No. 1',
                'business_contact' => '08123456788',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko roti B.',
                'phone' => '08123456788',
                'address' => 'Jl. Mawar No. 1',
                'opening_hours' => '08:00 - 20:00'
            ]);

            $consumer = User::factory()->create(['role' => 'consumer', 'name' => 'Budi Pelanggan']);

            // Buat pesanan untuk Mitra B
            $order = Order::create([
                'customer_id' => $consumer->id,
                'mitra_id' => $mitraB->id,
                'total_amount' => 50000,
                'status' => 'pending',
                'receiving_method' => 'pickup',
                'pickup_start_time' => '09:00',
                'pickup_end_time' => '10:00'
            ]);

            // Kirim notifikasi ke Mitra B saja
            $mitraB->notify(new IncomingOrderNotification($order));

            // Login sebagai Mitra A dan pastikan tidak melihat notifikasi pesanan masuk milik Mitra B
            $browser->loginAs($mitraA)
                    ->visitRoute('notifications.index')
                    // Memastikan teks 'Pesanan Baru Masuk!' TIDAK muncul pada halaman browser
                    ->assertDontSee('Pesanan Baru Masuk!')
                    // Memastikan teks 'Anda menerima pesanan baru dari Budi Pelanggan' TIDAK muncul pada halaman browser
                    ->assertDontSee('Anda menerima pesanan baru dari Budi Pelanggan');
        });
    }
}

