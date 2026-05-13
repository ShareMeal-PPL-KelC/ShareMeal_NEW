<?php

namespace Tests\Browser\source;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class PBI7_TC005_UpdateProdukFlashSaleTest extends DuskTestCase
{
    
    public function test_mitra_dapat_memperbarui_informasi_produk_flash_sale(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::where('email', 'mitra@example.com')->first();
            
            $browser->loginAs($mitra)
                    ->visit('/mitra/inventory')
                    ->waitForText('Manajemen Inventaris Surplus')
                    ->screenshot('PBI7_TC005_01_SebelumEdit');

            $browser->script("
                let btns = Array.from(document.querySelectorAll('button'));
                let editBtn = btns.find(b => b.getAttribute('@click') && b.getAttribute('@click').includes('openEditDialog') && !b.disabled);
                if(editBtn) editBtn.click();
            ");
            
            $browser->pause(1000)
                    ->waitForText('Edit Produk')
                    ->screenshot('PBI7_TC005_02_ModalEditTerbuka')
                    ->clear('name')
                    ->type('name', 'Produk Diupdate Dusk')
                    ->clear('stock')
                    ->type('stock', '99')
                    ->screenshot('PBI7_TC005_03_FormEditDiubah')
                    ->press('Simpan Produk')
                    ->pause(2000)
                    ->waitForText('Produk Diupdate Dusk')
                    ->assertSee('Produk Diupdate Dusk')
                    ->screenshot('PBI7_TC005_04_BerhasilDiedit');
        });
    }
}
