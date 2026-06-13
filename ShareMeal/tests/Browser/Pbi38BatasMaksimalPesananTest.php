<?php

namespace Tests\Browser;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi38BatasMaksimalPesananTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * TC-PBI38-002 - Menonaktifkan slot waktu yang sudah penuh di halaman checkout konsumen.
     */
    public function test_checkout_page_shows_full_slots_as_disabled(): void
    {
        $this->browse(function (Browser $browser) {
            // Setup Mitra with slot limit = 1
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
                'delivery_slot_limit' => 1,
            ]);

            // Setup Product (18:00 to 19:00 will produce slot "18:00 - 19:00")
            $product = Product::factory()->create([
                'user_id' => $mitra->id,
                'name' => 'Roti Manis',
                'price' => 15000,
                'stock' => 10,
                'status' => 'normal',
                'pickup_start_time' => '18:00:00',
                'pickup_end_time' => '19:00:00',
                'expires_at' => now()->addDay(),
            ]);

            // Setup Consumer
            $consumer = User::factory()->create([
                'role' => 'consumer',
                'email' => 'consumer38@sharemeal.com',
                'password' => bcrypt('password'),
            ]);
            $consumer->profile()->create([
                'phone' => '081234567890',
                'address' => 'Jl. Kebon Jeruk No. 25',
            ]);

            // Create 1 order to fill up the slot limit
            Order::create([
                'customer_id' => $consumer->id,
                'mitra_id' => $mitra->id,
                'total_amount' => 15000,
                'status' => 'pending',
                'pickup_code' => 'TEST-38',
                'receiving_method' => 'delivery',
                'delivery_time_slot' => '18:00 - 19:00',
                'created_at' => now(),
            ]);

            // Run Dusk steps
            $browser->loginAs($consumer)
                    ->visit('/consumer/checkout?product_id=' . $product->id)
                    ->waitForText('Menyelesaikan Pemesanan')
                    ->script("document.querySelector('input[value=\"delivery\"]').click();");

            // Tunggu sampai slot pengantaran muncul dan cek apakah slot "18:00 - 19:00" dinonaktifkan (Penuh)
            $browser->pause(1000)
                    ->waitForText('Pilih Waktu Pengantaran')
                    ->assertSee('18:00 - 19:00')
                    ->assertSee('(Penuh)')
                    ->assertDisabled('button[disabled]');
        });
    }
}
