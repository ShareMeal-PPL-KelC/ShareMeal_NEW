<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\UserProfile;

/**
 * PBI-45: Notifikasi Peringatan Dan Pembatalan
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi45NotifikasiPeringatanDanPembatalanTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * [PBI 45] Notifikasi Peringatan dan Pembatalan
     * Skenario 1: Banner Peringatan (Warned)
     */
    public function test_banner_peringatan_muncul_untuk_user_warned(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create([
                'role' => 'consumer',
                'status' => 'warned'
            ]);

            $browser->loginAs($user)
                    // Mengunjungi halaman '/consumer'
                    ->visit('/consumer')
                    // Memastikan teks 'Peringatan: Akun Anda mendapatkan peringatan karena pelanggaran kebijakan' terlihat pada halaman browser
                    ->assertSee('Peringatan: Akun Anda mendapatkan peringatan karena pelanggaran kebijakan');
        });
    }

    /**
     * Skenario 2: Banner Blokir (Blocked)
     */
    public function test_banner_blokir_muncul_untuk_user_blocked(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create([
                'role' => 'consumer',
                'status' => 'blocked'
            ]);

            $browser->loginAs($user)
                    // Mengunjungi halaman '/consumer'
                    ->visit('/consumer')
                    // Memastikan teks 'AKSES DIBATASI: Akun Anda telah diblokir' terlihat pada halaman browser
                    ->assertSee('AKSES DIBATASI: Akun Anda telah diblokir');
        });
    }

    /**
     * Skenario 3: Notifikasi Pembatalan Pesanan
     */
    public function test_notifikasi_pembatalan_pesanan_beserta_alasannya(): void
    {
        $this->browse(function (Browser $browser) {
            // Setup Mitra & Konsumen
            $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);
            $consumer = User::factory()->create(['role' => 'consumer']);

            $order = Order::create([
                'customer_id' => $consumer->id,
                'mitra_id' => $mitra->id,
                'total_amount' => 50000,
                'status' => 'pending',
                'receiving_method' => 'pickup',
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00'
            ]);

            // Simulasi Mitra membatalkan pesanan via DB
            $reason = 'Stok makanan sudah habis terjual.';
            $order->update([
                'status' => 'cancelled',
                'cancel_reason' => $reason
            ]);

            $browser->loginAs($consumer)
                    // Mengunjungi halaman '/notifications'
                    ->visit('/notifications')
                    // Menunggu teks 'Update Status Pesanan' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Update Status Pesanan', 15)
                    // Memastikan teks 'Mohon maaf, pesanan Anda telah dibatalkan' terlihat pada halaman browser
                    ->assertSee('Mohon maaf, pesanan Anda telah dibatalkan')
                    // Memastikan teks yang diharapkan muncul di layar
                    ->assertSee('Alasan: ' . $reason);
        });
    }

    /**
     * Skenario 4: Tandai Telah Dibaca
     */
    public function test_user_dapat_menandai_notifikasi_telah_dibaca(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create(['role' => 'consumer']);
            $mitra = User::factory()->create(['role' => 'mitra']);
            
            Order::create([
                'customer_id' => $user->id,
                'mitra_id' => $mitra->id,
                'total_amount' => 10000,
                'status' => 'pending',
                'receiving_method' => 'pickup',
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00'
            ]);

            $browser->loginAs($user)
                    // Mengunjungi halaman '/notifications'
                    ->visit('/notifications')
                    // Menunggu teks 'Tandai Dibaca' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Tandai Dibaca', 15)
                    // Gunakan clickAtXPath atau script jika tombol sulit ditekan
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script("document.querySelector('button[type=\"submit\"]').click();");

            // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(2000)
                    // Memastikan teks 'Tandai Dibaca' TIDAK muncul pada halaman browser
                    ->assertDontSee('Tandai Dibaca');
        });
    }

    public function test_negative_banner_tidak_muncul_untuk_user_aktif(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create([
                'role' => 'consumer',
                'status' => 'active'
            ]);

            $browser->loginAs($user)
                    // Mengunjungi halaman '/consumer'
                    ->visit('/consumer')
                    // Memastikan teks 'Peringatan: Akun Anda mendapatkan peringatan' TIDAK muncul pada halaman browser
                    ->assertDontSee('Peringatan: Akun Anda mendapatkan peringatan')
                    // Memastikan teks 'AKSES DIBATASI: Akun Anda telah diblokir' TIDAK muncul pada halaman browser
                    ->assertDontSee('AKSES DIBATASI: Akun Anda telah diblokir');
        });
    }
}

