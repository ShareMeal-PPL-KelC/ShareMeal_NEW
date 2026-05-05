<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC_KonsumenInventory_004 extends DuskTestCase
{
    /**
     * Test alur checkout batal otomatis karena waktu habis (countdown).
     */
    public function test_checkout_timeout_redirect(): void
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
                    // Menunggu countdown selesai (saat ini diset 6 detik)
                    ->waitForLocation('/consumer', 10)
                    ->assertPathIs('/consumer');
        });
    }
}
