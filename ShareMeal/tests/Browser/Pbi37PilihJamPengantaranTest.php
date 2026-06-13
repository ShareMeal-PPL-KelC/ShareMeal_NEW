<?php

namespace Tests\Browser;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi37PilihJamPengantaranTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * TC-PBI37-001 - Konsumen berhasil memilih waktu pengantaran saat checkout.
     */
    public function test_consumer_can_select_delivery_time_slot(): void
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
                'can_delivery' => true,
                'delivery_fee' => 5000,
                'delivery_slot_limit' => 5,
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
                'email' => 'consumer37@sharemeal.com',
                'password' => bcrypt('password'),
            ]);
            $consumer->profile()->create([
                'phone' => '081234567890',
                'address' => 'Jl. Kebon Jeruk No. 25',
            ]);

            // Run Dusk steps
            $browser->loginAs($consumer)
                    ->visit('/consumer/checkout?product_id=' . $product->id)
                    ->waitForText('Menyelesaikan Pemesanan')
                    // Klik opsi delivery (Kirim ke Lokasi) menggunakan JavaScript karena input tersembunyi (sr-only)
                    ->script("document.querySelector('input[value=\"delivery\"]').click();");

            // Tunggu sampai slot pengantaran muncul dan klik slot pertama yang tersedia
            $browser->pause(1000)
                    ->waitForText('Pilih Waktu Pengantaran')
                    ->script("document.querySelector('button[click*=\"deliveryTimeSlot\"]').click();");

            // Pilih metode pembayaran (QRIS) dan klik Konfirmasi & Bayar
            $browser->script("document.querySelector('input[value=\"qris\"]').click();");
            
            $browser->press('Konfirmasi & Bayar')
                    ->waitForText('Proses Pembayaran QRIS', 10)
                    ->waitForText('Pemesanan Berhasil', 15)
                    ->assertSee('Lunas')
                    ->assertSee('Kirim ke Lokasi');
        });
    }
}
