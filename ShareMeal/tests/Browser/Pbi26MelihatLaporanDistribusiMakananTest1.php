<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi26MelihatLaporanDistribusiMakananTest1 extends DuskTestCase
{
    /**
     * Test admin login and view distribution report page.
     */
    public function testAdminCanViewDistributionReport()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('email', 'admin@sharemeal.id')
                    ->type('password', 'password123')
                    ->press('Masuk')
                    ->assertPathIs('/admin')
                    ->visit('/admin/reports')
                    ->assertPathIs('/admin/reports')
                    ->assertSee('Laporan Distribusi Makanan');
        });
    }
}
?>
