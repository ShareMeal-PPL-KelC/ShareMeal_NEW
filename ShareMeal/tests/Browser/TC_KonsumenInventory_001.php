<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class TC_KonsumenInventory_001 extends DuskTestCase
{
    /**
     * Test the consumer location search flow.
     */
    public function test_cari_lokasi(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/consumer')
                    ->visit('/consumer/search')
                    ->press('Lokasi Saya')
                    ->waitForText('Konfirmasi Lokasi', 5)
                    ->press('Konfirmasi Lokasi')
                    ->waitUntilMissingText('Pilih Lokasi Pengantaran', 5);
        });
    }
}
