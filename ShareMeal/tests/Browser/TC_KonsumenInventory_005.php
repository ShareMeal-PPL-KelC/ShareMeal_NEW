<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC_KonsumenInventory_005 extends DuskTestCase
{
    /**
     * Test menampilkan alamat resto.
     */
    public function test_menampilkan_alamat_resto(): void
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
                    ->waitForText('LOKASI PENGAMBILAN', 5) // Teks di-render uppercase oleh CSS
                    ->press('Lihat Riwayat')
                    ->waitForLocation('/consumer/history', 5); // Atau lokasi yang sesuai setelah submit
        });
    }
}
