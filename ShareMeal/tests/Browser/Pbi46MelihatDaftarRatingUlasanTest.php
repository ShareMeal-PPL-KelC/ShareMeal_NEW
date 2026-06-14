<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi46MelihatDaftarRatingUlasanTest extends DuskTestCase
{
    /**
     * Menguji bahwa admin dapat melihat daftar rating dan ulasan pengguna.
     */
    public function test_admin_dapat_melihat_daftar_ulasan()
    {
        $this->browse(function (Browser $browser) {
            // 1. Login sebagai admin
            $browser->visit('/login')
                ->waitFor('input[name="email"]')
                ->type('email', 'admin@sharemeal.id')
                ->type('password', 'password123')
                ->select('user_type', 'admin')
                ->press('Masuk')
                ->assertPathIs('/admin');

            // 2. Buka halaman daftar ulasan admin
            $browser->visit('/admin/reviews')
                ->assertPathIs('/admin/reviews');

            // 3. Pastikan elemen-elemen penting yang ada di Blade muncul
            $browser->assertSee('Daftar Ulasan Pengguna')
                ->assertSee('Total Ulasan')
                ->assertSee('Rata-rata Rating');
        });
    }
}
?>
