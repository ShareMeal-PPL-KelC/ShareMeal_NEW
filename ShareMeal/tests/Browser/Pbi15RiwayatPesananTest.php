<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

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

            $browser->maximize()
                ->visit('/login')
                ->pause(2000)
                ->script("
                    let select = document.querySelector('select[name=\"user_type\"]');
                    if (select) {
                        select.value = 'consumer';
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                ");
            $browser->type('email', 'budi@example.com')
                ->type('password', 'password')
                ->press('button[type="submit"]')
                ->pause(2000)
                ->visit('/consumer/history');
            
            $this->disableReveal($browser);
            
            $browser->pause(2000)
                ->assertSee('Riwayat')
                ->assertSee('Riwayat Pesanan')
                ->assertSee('Toko Roti Makmur')
                ->assertSee('SELESAI');
        });
    }

    public function test_admin_tidak_dapat_mengakses_riwayat_pesanan_konsumen_secara_langsung(): void
    {
        $this->setupTestData();

        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();

            $browser->maximize()
                ->visit('/login')
                ->pause(2000)
                ->script("
                    let select = document.querySelector('select[name=\"user_type\"]');
                    if (select) {
                        select.value = 'admin';
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                ");
            $browser->type('email', 'admin@sharemeal.id')
                ->type('password', 'password')
                ->press('button[type="submit"]')
                ->pause(2000)
                ->visit('/consumer/history')
                ->assertDontSee('Riwayat Pesanan');
        });
    }
}