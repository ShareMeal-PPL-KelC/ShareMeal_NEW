<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class KonsumenMelihatRiwayatTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * TC.Cons.003 - PBI #15
     * Menguji akses tampilan riwayat pembelian
     */
    public function testKonsumenMelihatRiwayatPesanan()
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

            $browser->loginAs($kina)
                    ->visit('/consumer/history')
                    ->assertSee('Riwayat Transaksi');
        });
    }
}
