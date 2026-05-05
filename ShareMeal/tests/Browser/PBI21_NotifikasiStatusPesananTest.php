<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PBI21_NotifikasiStatusPesananTest extends DuskTestCase
{
    /**
     * POSITIVE TEST: Berhasil melihat notifikasi update status pesanan.
     */
    public function testNotifikasiStatusPesanan(): void
    {
        $this->browse(function (Browser $browser) {
            $email = 'test_cons_' . Str::random(4) . '@example.com';
            $password = 'password123';

            // 1. SETUP DATA (Sangat aman, akan dihapus otomatis)
            $consumer = User::create([
                'name' => 'Consumer Test',
                'email' => $email,
                'password' => bcrypt($password),
                'role' => 'consumer',
            ]);

            $mitra = User::where('role', 'mitra')->first();
            if (!$mitra) {
                $mitra = User::create([
                    'name' => 'Mitra Test',
                    'email' => 'mitra_test_' . Str::random(4) . '@example.com',
                    'password' => bcrypt('password'),
                    'role' => 'mitra',
                    'is_verified' => true,
                ]);
            }

            // Buat pesanan dummy
            $order = Order::create([
                'customer_id' => $consumer->id,
                'mitra_id' => $mitra->id,
                'total_amount' => 25000,
                'status' => 'pending',
                'pickup_code' => 'ORD-' . strtoupper(Str::random(4))
            ]);

            // Simulasi notifikasi manual di database
            $consumer->notifications()->create([
                'id' => Str::uuid(),
                'type' => 'App\Notifications\OrderStatusUpdated',
                'data' => [
                    'title' => 'Update Status Pesanan',
                    'message' => 'Pesanan Anda sedang diproses.',
                    'order_id' => $order->id,
                    'status' => 'pending'
                ],
            ]);

            try {
                // 2. JALANKAN TEST
                $browser->visit('/login')
                    ->select('user_type', 'consumer')
                    ->type('email', $consumer->email)
                    ->type('password', $password)
                    ->press('Masuk')
                    ->waitForLocation('/consumer', 15)
                    ->assertSee('Dashboard Konsumen')
                    ->pause(2000)
                    // Selector CSS langsung ke icon lonceng
                    ->click('nav button i[data-lucide="bell"], nav button svg.lucide-bell')
                    ->pause(2000)
                    ->assertSee('Update Status Pesanan')
                    ->assertSee('Pesanan Anda sedang diproses.');
            } finally {
                // 3. PEMBERSIHAN (Database tetap bersih)
                $order->delete();
                $consumer->delete();
                if ($mitra && str_contains($mitra->email, 'mitra_test_')) {
                    $mitra->delete();
                }
            }
        });
    }

    /**
     * NEGATIVE TEST: Consumer tidak melihat notifikasi update status jika tidak ada pesanan.
     */
    public function testNotifikasiStatusPesananKosong(): void
    {
        $this->browse(function (Browser $browser) {
            $email = 'test_clean_' . Str::random(4) . '@example.com';
            $password = 'password123';

            $consumer = User::create([
                'name' => 'Consumer Bersih',
                'email' => $email,
                'password' => bcrypt($password),
                'role' => 'consumer',
            ]);

            try {
                $browser->visit('/login')
                    ->select('user_type', 'consumer')
                    ->type('email', $consumer->email)
                    ->type('password', $password)
                    ->press('Masuk')
                    ->waitForLocation('/consumer', 15)
                    ->assertSee('Dashboard Konsumen')
                    ->pause(2000)
                    ->click('nav button i[data-lucide="bell"], nav button svg.lucide-bell')
                    ->pause(2000)
                    ->assertDontSee('Update Status Pesanan');
            } finally {
                $consumer->delete();
            }
        });
    }
}
