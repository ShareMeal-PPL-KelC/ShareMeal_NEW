<?php

namespace Tests\Browser;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi12MelihatPesananMasukTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * [POSITIF] Mitra bisa melihat pesanan yang sudah ada di database.
     */
    public function test_mitra_berhasil_melihat_pesanan_masuk(): void
    {
        $this->browse(function (Browser $browser) {
            // --- LANGKAH 1: SIAPKAN DATA (Tanpa Browser) ---
            // Buat Mitra
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'email' => 'mitra@example.com'
            ]);

            // Buat Konsumen
            $consumer = User::factory()->create([
                'role' => 'consumer',
                'name' => 'Budi Santoso'
            ]);

            // Buat Produk
            $product = Product::factory()->create([
                'user_id' => $mitra->id,
                'name' => 'Roti Gandum'
            ]);

            // Buat Pesanan Langsung di Database
            $order = Order::create([
                'customer_id' => $consumer->id,
                'mitra_id' => $mitra->id,
                'total_amount' => 15000,
                'status' => 'pending',
                'payment_method' => 'qris',
                'receiving_method' => 'pickup'
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => 15000
            ]);

            // --- LANGKAH 2: TEST UI (Pakai Dusk) ---
            $browser->visit('/login')
                ->select('user_type', 'mitra')
                ->type('email', 'mitra@example.com')
                ->type('password', 'password') // pastikan factory passwordnya 'password'
                ->press('Masuk')
                ->waitForLocation('/mitra'); // dashboard mitra

            $browser->visit('/mitra/orders')
                ->waitForText('Daftar Pesanan')
                ->assertSee('Budi Santoso')
                ->assertSee('Roti Gandum')
                ->assertSee('pending');
        });
    }
}