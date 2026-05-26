<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;

class KonsumenWaktuKelayakanKonsumsiTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * TC.Cons.004 - PBI #13
     * Memeriksa waktu kelayakan konsumsi pada pesanan yang sudah expired
     */
    public function testWaktuKelayakanKonsumsi()
    {
        $this->browse(function (Browser $browser) {
            $kina = User::firstOrCreate(
                ['email' => 'kina@gmail.com'],
                [
                    'name' => 'kina',
                    'password' => bcrypt('password'),
                    'role' => 'consumer',
                    'is_verified' => true
                ]
            );

            // Create a pending order for time check
            \App\Models\Order::firstOrCreate(
                ['customer_id' => $kina->id, 'status' => 'pending'],
                [
                    'mitra_id' => 1,
                    'total_amount' => 10000,
                    'pickup_code' => 'TEST123',
                    'pickup_time' => now()->addHours(2)
                ]
            );

            $browser->loginAs($kina)
                    ->visit('/consumer/history')
                    ->assertSee('Sisa waktu sebelum layak konsumsi:');
        });
    }
}
