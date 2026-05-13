<?php

namespace Tests\Browser\source;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class PBI5_TC001_TambahProdukFlashSaleTest extends DuskTestCase
{
    public function test_mitra_dapat_menambahkan_produk_flash_sale_dengan_data_lengkap(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::where('email', 'mitra@example.com')->first();
            
            $browser->loginAs($mitra)
                    ->visit('/mitra/inventory')
                    ->waitForText('Manajemen Inventaris Surplus')
                    ->screenshot('PBI5_TC001_01_HalamanInventaris')
                    ->press('Tambah Produk')
                    ->waitForText('Tambah Produk Baru')
                    ->screenshot('PBI5_TC001_02_ModalTambahProduk')
                    ->type('name', 'Roti Bakar Special Dusk')
                    ->select('category', 'Bakery')
                    ->type('stock', '50')
                    ->type('price', '25000')
                    ->script("document.querySelector('input[name=expires_at]').value = '2026-12-31T23:59';");
            
            $browser->screenshot('PBI5_TC001_03_FormTerisi')
                    ->press('Simpan Produk')
                    ->pause(2000)
                    ->waitForText('Roti Bakar Special Dusk')
                    ->assertSee('Roti Bakar Special Dusk')
                    ->screenshot('PBI5_TC001_04_ProdukBerhasilDitambahkan');
        });
    }
}
