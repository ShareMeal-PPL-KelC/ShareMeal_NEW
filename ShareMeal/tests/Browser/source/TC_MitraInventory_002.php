<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC_MitraInventory_002 extends DuskTestCase
{
    /**
     * TC.MitraInventory.002
     * Menguji validasi tambah produk flash sale dengan data kosong
     */
    public function test_mitra_cannot_add_product_with_empty_data(): void
    {
        $this->browse(function (Browser $browser) {
            // Ambil user mitra (atau buat jika belum ada)
            $mitra = User::where('email', 'mitra@example.com')->first();
            if (!$mitra) {
                $mitra = User::factory()->create([
                    'email' => 'mitra@example.com',
                    'role' => 'mitra',
                ]);
            }

            $browser->loginAs($mitra)
                    ->visit('/mitra/inventory')
                    ->screenshot('TC_MitraInventory_002_01_halaman_inventaris')
                    ->assertSee('Manajemen Inventaris Surplus')
                    ->press('Tambah Produk')
                    ->waitForText('Tambah Produk Baru')
                    ->screenshot('TC_MitraInventory_002_02_form_kosong')
                    
                    ->clear('name')
                    ->clear('stock')
                    ->clear('price')
                    ->script("document.querySelector('[name=expires_at]').value = '';");

            $browser->press('Simpan Produk')
                    ->pause(1000)
                    ->screenshot('TC_MitraInventory_002_03_validasi_kosong');

            // Karena menggunakan HTML5 validation (required attribute), form tidak akan tersubmit.
            // Kita verifikasi bahwa validasi browser berjalan pada field name.
            $validationMessage = $browser->script("return document.querySelector('[name=name]').validationMessage;")[0];
            $this->assertNotEmpty($validationMessage);
            
            // Verifikasi modal masih terbuka (masih berada di form tambah produk)
            $browser->assertSee('Tambah Produk Baru')
                    ->screenshot('TC_MitraInventory_002_04_modal_masih_terbuka');
        });
    }
}
