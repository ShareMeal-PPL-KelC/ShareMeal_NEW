<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC_MitraInventory_001 extends DuskTestCase
{
    /**
     * TC.MitraInventory.001
     * Menguji fungsionalitas menambahkan produk flash sale dengan data lengkap
     */
    public function test_mitra_can_add_product(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::where('email', 'mitra@example.com')->first();
            if (!$mitra) {
                $mitra = User::factory()->create([
                    'email' => 'mitra@example.com',
                    'role' => 'mitra',
                ]);
            }

            $browser->loginAs($mitra)
                    ->visit('/mitra/inventory')
                    ->screenshot('TC_MitraInventory_001_01_halaman_inventaris')
                    ->assertSee('Manajemen Inventaris Surplus')
                    ->press('Tambah Produk')
                    ->waitForText('Tambah Produk Baru')
                    ->screenshot('TC_MitraInventory_001_02_form_tambah_produk')
                    ->type('name', 'Roti Coklat Lezat')
                    ->select('category', 'Bakery')
                    ->type('stock', '15')
                    ->type('price', '12000')
                    ->script("document.querySelector('[name=expires_at]').value = '2026-12-31T23:59';");

            $browser->screenshot('TC_MitraInventory_001_03_form_terisi')
                    ->press('Simpan Produk')
                    ->pause(2000)
                    ->screenshot('TC_MitraInventory_001_04_setelah_simpan')
                    ->visit('/mitra/inventory')
                    ->waitForText('Roti Coklat Lezat', 10)
                    ->assertSee('Roti Coklat Lezat')
                    ->assertSee('15')
                    ->screenshot('TC_MitraInventory_001_05_produk_berhasil_ditambahkan');
        });
    }

}
