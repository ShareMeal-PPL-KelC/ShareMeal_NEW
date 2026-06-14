<?php

namespace Tests\Browser;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi39SimulasiPembayaranBerhasilTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * TC-PBI39-002 & TC-PBI39-003 - Menampilkan loading simulasi pembayaran dan berhasil menyelesaikan checkout.
     */
    public function test_consumer_can_checkout_with_payment_simulation(): void
    {
        $this->browse(function (Browser $browser) {
            // Setup Mitra
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Toko Roti Enak',
                'is_verified' => true,
            ]);
            $mitra->profile()->create([
                'business_name' => 'Toko Roti Enak',
                'business_address' => 'Jl. Padi No. 10',
                'can_delivery' => false,
            ]);

            // Setup Product
            $product = Product::factory()->create([
                'user_id' => $mitra->id,
                'name' => 'Roti Manis',
                'price' => 15000,
                'stock' => 10,
                'status' => 'normal',
                'pickup_start_time' => '18:00:00',
                'pickup_end_time' => '20:00:00',
                'expires_at' => now()->addDay(),
            ]);

            // Setup Consumer
            $consumer = User::factory()->create([
                'role' => 'consumer',
                'email' => 'consumer39@sharemeal.com',
                'password' => bcrypt('password'),
            ]);
            $consumer->profile()->create([
                'phone' => '081234567890',
                'address' => 'Jl. Kebon Jeruk No. 25',
            ]);

            // Run Dusk steps
            $browser->loginAs($consumer)
                    ->visit('/consumer/checkout?product_id=' . $product->id)
                    ->waitForText('Menyelesaikan Pemesanan');

            // Pilih metode pembayaran GoPay menggunakan JavaScript (karena radio tersembunyi)
            $browser->script("document.querySelector('input[value=\"gopay\"]').click();");
            
            // Tekan tombol konfirmasi & bayar
            $browser->press('Konfirmasi & Bayar');

            // Cek apakah loading screen GoPay muncul
            $browser->waitForText('Proses Pembayaran GoPay', 5)
                    ->assertSee('Menyambungkan dengan e-wallet GoPay...')
                    
                    // Tunggu hingga simulasi selesai (sekitar 4.5 detik) dan struk digital muncul
                    ->waitForText('Pemesanan Berhasil', 10)
                    ->assertSee('Lunas')
                    ->assertSee('Ambil Sendiri')
                    ->assertSee('gopay');
        });
    }
}
