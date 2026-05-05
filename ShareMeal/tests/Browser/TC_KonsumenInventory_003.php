<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC_KonsumenInventory_003 extends DuskTestCase
{
    /**
     * Test alur checkout pembayaran.
     */
    public function test_checkout_pembayaran(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/consumer')
                    ->waitForText('Cari Makanan', 5)
                    ->clickLink('Cari Makanan')
                    ->waitForLocation('/consumer/search', 5)
                    ->waitForText('Roti Tawar Gandum', 5)
                    ->press('Booking')
                    ->waitForLocation('/consumer/checkout', 5)
                    ->waitForText('Checkout Pembayaran', 5)
                    ->press('Saya Sudah Bayar')
                    ->waitForText('Pembayaran Berhasil!', 5)
                    ->assertSee('Pembayaran Berhasil!');
        });
    }
}
