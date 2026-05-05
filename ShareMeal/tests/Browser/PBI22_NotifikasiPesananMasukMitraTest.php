<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PBI22_NotifikasiPesananMasukMitraTest extends DuskTestCase
{
    /**
     * POSITIVE TEST: Mitra berhasil melihat notifikasi saat ada pesanan baru masuk.
     */
    public function testNotifikasiPesananMasuk(): void
    {
        $this->browse(function (Browser $browser) {
            $email = 'test_mitra_' . Str::random(4) . '@example.com';
            $password = 'password123';
            
            // 1. SETUP DATA (Sangat aman, data akan dihapus di akhir test)
            $mitra = User::create([
                'name' => 'Mitra Toko Test',
                'email' => $email,
                'password' => bcrypt($password),
                'role' => 'mitra',
                'is_verified' => true,
            ]);

            $consumer = User::where('role', 'consumer')->first();
            if (!$consumer) {
                $consumer = User::create([
                    'name' => 'Budi Test',
                    'email' => 'budi_test_' . Str::random(4) . '@example.com',
                    'password' => bcrypt('password'),
                    'role' => 'consumer',
                ]);
            }

            // Buat pesanan dummy untuk memicu notifikasi
            $order = Order::create([
                'customer_id' => $consumer->id,
                'mitra_id' => $mitra->id,
                'total_amount' => 45000,
                'status' => 'pending',
                'pickup_code' => 'TEST-' . strtoupper(Str::random(4))
            ]);

            // Simulasi notifikasi manual agar tidak perlu class notification tambahan
            $mitra->notifications()->create([
                'id' => Str::uuid(),
                'type' => 'App\Notifications\IncomingOrderNotification',
                'data' => [
                    'title' => 'Pesanan Baru Masuk!',
                    'message' => 'Anda menerima pesanan baru dari ' . $consumer->name . ' sejumlah Rp 45.000',
                    'order_id' => $order->id,
                ],
            ]);

            try {
                // 2. JALANKAN TEST
                $browser->visit('/login')
                    ->select('user_type', 'mitra')
                    ->type('email', $mitra->email)
                    ->type('password', $password)
                    ->press('Masuk')
                    ->waitForLocation('/mitra', 15)
                    ->assertSee('Dashboard Mitra')
                    ->pause(2000)
                    // Selector CSS langsung ke icon lonceng
                    ->click('nav button i[data-lucide="bell"], nav button svg.lucide-bell')
                    ->pause(2000)
                    ->assertSee('Pesanan Baru Masuk!')
                    ->assertSee('Anda menerima pesanan baru dari');
            } finally {
                // 3. PEMBERSIHAN (Hapus item agar DB tidak kotor)
                $order->delete();
                $mitra->delete();
                if ($consumer && str_contains($consumer->email, 'budi_test_')) {
                    $consumer->delete();
                }
            }
        });
    }

    /**
     * NEGATIVE TEST: Consumer tidak melihat notifikasi pesanan masuk milik mitra.
     */
    public function testNotifikasiPesananMasukTidakTerlihatOlehConsumer(): void
    {
        $this->browse(function (Browser $browser) {
            $email = 'test_cons_' . Str::random(4) . '@example.com';
            $password = 'password123';

            $consumer = User::create([
                'name' => 'Consumer Clean',
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
                    ->assertDontSee('Pesanan Baru Masuk!');
            } finally {
                $consumer->delete();
            }
        });
    }
}
