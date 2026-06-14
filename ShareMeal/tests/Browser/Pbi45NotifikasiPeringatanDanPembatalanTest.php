<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\UserProfile;

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
                    ->visit('/consumer')
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
                    ->visit('/consumer')
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
                    ->visit('/notifications')
                    ->waitForText('Update Status Pesanan', 15)
                    ->assertSee('Mohon maaf, pesanan Anda telah dibatalkan')
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
                    ->visit('/notifications')
                    ->waitForText('Tandai Dibaca', 15)
                    // Gunakan clickAtXPath atau script jika tombol sulit ditekan
                    ->script("document.querySelector('button[type=\"submit\"]').click();");

            $browser->pause(2000)
                    ->assertDontSee('Tandai Dibaca');
        });
    }
}
