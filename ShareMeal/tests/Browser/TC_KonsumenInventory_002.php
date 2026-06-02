<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC_KonsumenInventory_002 extends DuskTestCase
{
    /**
     * Test melihat detail makanan.
     */
    public function test_melihat_detail_makanan(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/consumer')
                    ->waitForText('Cari Makanan', 5)
                    ->clickLink('Cari Makanan')
                    ->waitForLocation('/consumer/search', 5)
                    ->waitForText('Roti Tawar Gandum', 5)
                    ->press('Booking')
                    ->waitForLocation('/consumer/checkout', 5)
                    ->assertQueryStringHas('product_id', '1');
        });
    }
}
