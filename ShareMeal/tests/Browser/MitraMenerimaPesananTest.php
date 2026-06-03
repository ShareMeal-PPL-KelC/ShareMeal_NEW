<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class MitraMenerimaPesananTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * TC.Mitra.001 - PBI #12
     * Menguji penerimaan informasi pesanan masuk
     */
    public function testMitraMenerimaPesananBaru()
    {
        $this->browse(function (Browser $browser) {
            // Setup data
            $mitra = \App\Models\User::firstOrCreate(
                ['email' => 'ayam@gmail.com'],
                [
                    'name' => 'ayam',
                    'password' => bcrypt('ayam@gmail.com'),
                    'role' => 'mitra',
                    'is_verified' => true
                ]
            );

            $consumer = \App\Models\User::firstOrCreate(
                ['email' => 'kina@gmail.com'],
                [
                    'name' => 'kina',
                    'password' => bcrypt('password'),
                    'role' => 'consumer'
                ]
            );

            $product = \App\Models\Product::firstOrCreate(
                ['user_id' => $mitra->id, 'name' => 'ayam'],
                [
                    'category' => 'Food',
                    'price' => 10000,
                    'stock' => 10,
                    'status' => 'active',
                    'expires_at' => now()->addDays(7)
                ]
            );

            \App\Models\Order::firstOrCreate(
                ['customer_id' => $consumer->id, 'mitra_id' => $mitra->id, 'status' => 'pending'],
                [
                    'total_amount' => 10000,
                    'pickup_code' => 'TEST456',
                    'pickup_time' => now()->addHours(2)
                ]
            );

            $browser->visit('/login')
                    ->waitFor('select[name="user_type"]')
                    ->select('user_type', 'mitra')
                    ->type('email', 'ayam@gmail.com')
                    ->type('password', 'ayam@gmail.com')
                    ->press('Masuk')
                    ->waitForLocation('/mitra')
                    ->visit('/mitra/orders')
                    ->assertSee('Daftar Pesanan Masuk')
                    ->assertSee('Menunggu')
                    ->assertSee('kina')
                    ->assertSee('ayam');
        });
    }
}
