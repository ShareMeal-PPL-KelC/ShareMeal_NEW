<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-15: Riwayat Pesanan
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi15RiwayatPesananTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function setupTestData(): array
    {
        $consumer = User::factory()->create([
            'role' => 'consumer',
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => Hash::make('password'),
            'is_verified' => true,
        ]);
        $consumer->profile()->create([
            'phone' => '081234567890',
            'address' => 'Kost Orange, Jl. Telekomunikasi No. 12, Sukabirus, Bandung',
        ]);

        $mitra = User::factory()->create([
            'role' => 'mitra',
            'name' => 'Toko Roti Makmur',
            'is_verified' => true,
        ]);
        $mitra->profile()->create([
            'phone' => '089876543210',
            'address' => 'Jl. Sukabirus No. 45, Dayeuhkolot, Bandung',
            'business_name' => 'Toko Roti Makmur',
            'business_type' => 'Bakery',
            'business_address' => 'Jl. Sukabirus No. 45, Dayeuhkolot, Bandung',
            'business_contact' => '089876543210',
            'business_opening_hours' => '08:00 - 20:00',
            'is_verified' => true,
        ]);

        $product = \App\Models\Product::create([
            'user_id' => $mitra->id,
            'name' => 'Roti Coklat Premium',
            'category' => 'Bakery',
            'price' => 10000,
            'discount_price' => 7000,
            'stock' => 10,
            'status' => 'flash-sale',
            'expires_at' => now()->addHours(2),
        ]);

        $order = \App\Models\Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 7000,
            'status' => 'completed',
            'confirmed_by_consumer' => true,
            'pickup_code' => 'PICK-TEST',
            'receiving_method' => 'pickup',
            'payment_method' => 'qris',
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 7000,
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin ShareMeal',
            'email' => 'admin@sharemeal.id',
            'password' => Hash::make('password'),
            'is_verified' => true,
        ]);

        return [$consumer, $admin];
    }

    private function disableReveal(Browser $browser): void
    {
        // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
        $browser->script("
            var style = document.createElement('style');
            style.innerHTML = '.reveal { opacity: 1 !important; transform: none !important; transition: none !important; transition-delay: 0s !important; }';
            document.head.appendChild(style);
        ");
    }

    /**
     * TC-PBI15-001 - Konsumen dapat melihat riwayat pesanan.
     */
    public function test_konsumen_melihat_riwayat_pesanan(): void
    {
        $this->setupTestData();

        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();

            // Memaksimalkan ukuran jendela browser agar tampilan terlihat penuh
            $browser->maximize()
                // Mengunjungi halaman '/login'
                ->visit('/login')
                // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
                ->pause(2000)
                // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                ->script("
                    let select = document.querySelector('select[name=\"user_type\"]');
                    if (select) {
                        select.value = 'consumer';
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                ");
            // Mengisi input 'email' dengan nilai 'budi@example.com'
            $browser->type('email', 'budi@example.com')
                // Mengisi input 'password' dengan nilai 'password'
                ->type('password', 'password')
                // Menekan tombol dengan teks/properti 'tombol terkait'
                ->press('button[type="submit"]')
                // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
                ->pause(2000)
                // Mengunjungi halaman '/consumer/history'
                ->visit('/consumer/history');
            
            $this->disableReveal($browser);
            
            // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(2000)
                // Memastikan teks 'Riwayat' terlihat pada halaman browser
                ->assertSee('Riwayat')
                // Memastikan teks 'Riwayat Pesanan' terlihat pada halaman browser
                ->assertSee('Riwayat Pesanan')
                // Memastikan teks 'Toko Roti Makmur' terlihat pada halaman browser
                ->assertSee('Toko Roti Makmur')
                // Memastikan teks 'SELESAI' terlihat pada halaman browser
                ->assertSee('SELESAI');
        });
    }

    public function test_admin_tidak_dapat_mengakses_riwayat_pesanan_konsumen_secara_langsung(): void
    {
        $this->setupTestData();

        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();

            // Memaksimalkan ukuran jendela browser agar tampilan terlihat penuh
            $browser->maximize()
                // Mengunjungi halaman '/login'
                ->visit('/login')
                // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
                ->pause(2000)
                // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                ->script("
                    let select = document.querySelector('select[name=\"user_type\"]');
                    if (select) {
                        select.value = 'admin';
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                ");
            // Mengisi input 'email' dengan nilai 'admin@sharemeal.id'
            $browser->type('email', 'admin@sharemeal.id')
                // Mengisi input 'password' dengan nilai 'password'
                ->type('password', 'password')
                // Menekan tombol dengan teks/properti 'tombol terkait'
                ->press('button[type="submit"]')
                // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
                ->pause(2000)
                // Mengunjungi halaman '/consumer/history'
                ->visit('/consumer/history')
                // Memastikan teks 'Riwayat Pesanan' TIDAK muncul pada halaman browser
                ->assertDontSee('Riwayat Pesanan');
        });
    }
}