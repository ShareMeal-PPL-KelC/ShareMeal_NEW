<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi47MelihatLaporanMakananBermasalahTest1 extends DuskTestCase
{
    /**
     * PBI 47 - Melihat Daftar Laporan Makanan Bermasalah dari Konsumen
     * dan Lembaga sebagai Admin.
     */
    public function test_admin_dapat_melihat_laporan_makanan_bermasalah()
    {
        $this->browse(function (Browser $browser) {

            // ================================================
            // STEP 1: Login sebagai Admin
            // ================================================
            $browser->visit('/login')
                ->waitFor('select[name="user_type"]', 15)
                ->select('user_type', 'admin')
                ->type('input[name="email"]', env('TEST_ADMIN_EMAIL'))
                ->type('input[name="password"]', env('TEST_ADMIN_PASSWORD'))
                ->press('Masuk')
                ->waitForLocation('/admin', 15)
                ->assertPathIs('/admin')
                ->assertDontSee('Login gagal');

            // ================================================
            // STEP 2: Buka halaman laporan makanan bermasalah
            // ================================================
            $browser->visit('/admin/problem-reports')

                // Tunggu halaman benar-benar termuat (tunggu body muncul)
                ->waitFor('body', 10)

                // Verifikasi URL sudah benar
                ->assertPathIs('/admin/problem-reports')

                // Verifikasi halaman TIDAK menampilkan error
                ->assertDontSee('403')
                ->assertDontSee('404')
                ->assertDontSee('500')
                ->assertDontSee('Whoops')
                ->assertDontSee('Server Error');
        });
    }
}