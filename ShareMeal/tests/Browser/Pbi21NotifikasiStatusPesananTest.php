<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;

class Pbi21NotifikasiStatusPesananTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * [PBI 21] Notifikasi Sistem - Konsumen
     * Deskripsi: Sebagai konsumen, saya ingin menerima notifikasi status pesanan agar mengetahui perkembangan transaksi.
     */
    public function test_positive_konsumen_menerima_notifikasi_perubahan_status_pesanan(): void
    {
        $this->browse(function (Browser $browser) {
            // --- 1. SETUP DATA (Dibelakang Layar) ---
            $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);
            $store = Store::create([
                'owner_user_id' => $mitra->id,
                'name' => 'Toko Roti Barokah',
                'category' => 'Bakery',
                'address' => 'Jl. Pahlawan No. 1'
            ]);

            $consumer = User::factory()->create([
                'role' => 'consumer',
                'email' => 'konsumen@example.com',
                'password' => bcrypt('password'),
                'name' => 'Budi Konsumen'
            ]);

            // --- 2. EXECUTION ---

            // Simulasi Login Tanpa UI (agar stabil dari JS error di Landing Page)
            $browser->loginAs($consumer);

            // Step 3: Lakukan proses pembuatan pesanan baru (Simulasi Checkout via DB)
            // Ini akan memicu notification trigger 'static::created' di Model Order
            $order = Order::create([
                'customer_id' => $consumer->id,
                'mitra_id' => $mitra->id,
                'total_amount' => 50000,
                'status' => 'pending',
                'receiving_method' => 'pickup',
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00'
            ]);

            // Step 4 & 5: Buka halaman Notifikasi & Tunggu elemen muncul
            $browser->visitRoute('notifications.index')
                    ->waitForText('Update Status Pesanan', 15)
                    // Step 6: Pastikan melihat notifikasi tahap inisiasi
                    ->assertSee('Pesanan Anda sedang menunggu konfirmasi');

            // Step 7: Simulasikan perubahan status menjadi "processing"
            // Ini akan memicu notification trigger 'static::updated' di Model Order
            $order->update(['status' => 'processing']);

            // Step 8 & 9: Muat ulang & Cek Notifikasi Tahap Konfirmasi
            $browser->refresh()
                    ->waitForText('Pesanan Anda sedang diproses', 15)
                    ->assertSee('Pesanan Anda sedang diproses');

            // Step 7 b: Simulasikan perubahan status menjadi "ready"
            $order->update(['status' => 'ready']);

            // Step 8 b & 10: Muat ulang & Cek Notifikasi Tahap Penyelesaian
            $browser->refresh()
                    ->waitForText('Pesanan Anda sudah siap diambil', 15)
                    ->assertSee('Pesanan Anda sudah siap diambil');

            // Step 11: Pastikan semua urutan riwayat perubahan status terlihat jelas
            $browser->assertSee('Pesanan Anda sedang menunggu konfirmasi')
                    ->assertSee('Pesanan Anda sedang diproses')
                    ->assertSee('Pesanan Anda sudah siap diambil');
        });
    }

    public function test_negative_konsumen_tidak_menerima_notifikasi_pesanan_orang_lain(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);
            $consumerA = User::factory()->create(['role' => 'consumer', 'name' => 'Konsumen A']);
            $consumerB = User::factory()->create(['role' => 'consumer', 'name' => 'Konsumen B']);

            // Buat pesanan untuk Konsumen B
            Order::create([
                'customer_id' => $consumerB->id,
                'mitra_id' => $mitra->id,
                'total_amount' => 30000,
                'status' => 'pending',
                'receiving_method' => 'pickup',
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00'
            ]);

            // Login sebagai Konsumen A dan pastikan tidak melihat notifikasi pesanan Konsumen B
            $browser->loginAs($consumerA)
                    ->visitRoute('notifications.index')
                    ->assertDontSee('Update Status Pesanan')
                    ->assertDontSee('Pesanan Anda sedang menunggu konfirmasi');
        });
    }
}

