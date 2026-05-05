<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class MitraMenerimaPesananTest extends DuskTestCase
{
    /**
     * TC.Mitra.001 - PBI #12
     * Menguji penerimaan informasi pesanan masuk
     */
    public function testMitraMenerimaPesananBaru()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->select('user_type', 'mitra')
                    ->type('email', 'ayam@gmail.com')
                    ->type('password', 'ayam@gmail.com')
                    ->press('Masuk');

            $browser->visit('/mitra/orders')
                    ->assertSee('Daftar Pesanan Masuk')
                    ->assertSee('Menunggu')
                    ->assertSee('kina')
                    ->assertSee('ayam');
        });
    }
}
