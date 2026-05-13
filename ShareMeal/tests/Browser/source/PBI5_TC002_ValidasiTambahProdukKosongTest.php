<?php

namespace Tests\Browser\source;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class PBI5_TC002_ValidasiTambahProdukKosongTest extends DuskTestCase
{
    public function test_sistem_menampilkan_validasi_saat_tambah_produk_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::where('email', 'mitra@example.com')->first();
            
            $browser->loginAs($mitra)
                    ->visit('/mitra/inventory')
                    ->waitForText('Manajemen Inventaris Surplus')
                    ->press('Tambah Produk')
                    ->waitForText('Tambah Produk Baru')
                    ->screenshot('PBI5_TC002_01_ModalKosong')

                    ->press('Simpan Produk')
            
                    ->pause(1000)
                    ->assertSee('Tambah Produk Baru')
                    ->screenshot('PBI5_TC002_02_ValidasiHTML5Berhasil');
        });
    }
}
