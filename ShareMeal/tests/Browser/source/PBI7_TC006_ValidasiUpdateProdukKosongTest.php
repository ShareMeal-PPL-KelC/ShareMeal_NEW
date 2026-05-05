<?php

namespace Tests\Browser\source;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class PBI7_TC006_ValidasiUpdateProdukKosongTest extends DuskTestCase
{
    public function test_sistem_menampilkan_validasi_saat_update_produk_dengan_data_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::where('email', 'mitra@example.com')->first();
            
            $browser->loginAs($mitra)
                    ->visit('/mitra/inventory')
                    ->waitForText('Manajemen Inventaris Surplus');

            $browser->click('@edit-button');
            
            $browser->pause(1000)
                    ->waitForText('Edit Produk')
                    // Kosongkan field wajib
                    ->script("document.querySelector('input[name=name]').value = '';");
            
            $browser->screenshot('PBI7_TC006_01_FormEditDikosongkan')
                    ->press('Simpan Produk')
                    ->pause(1000)
                    // Verifikasi modal tetap terbuka karena validasi HTML5
                    ->assertSee('Edit Produk')
                    ->screenshot('PBI7_TC006_02_ValidasiHTML5Berhasil');
        });
    }
}
