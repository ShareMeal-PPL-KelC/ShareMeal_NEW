<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class KonsumenMelihatRiwayatTest extends DuskTestCase
{
    /**
     * TC.Cons.003 - PBI #15
     * Menguji akses tampilan riwayat pembelian
     */
    public function testKonsumenMelihatRiwayatPesanan()
    {
        $this->browse(function (Browser $browser) {
            $kina = User::where('email', 'kina@gmail.com')->first();
            $browser->loginAs($kina)
                    ->visit('/consumer/history')
                    ->assertSee('Riwayat Transaksi');
        });
    }
}
