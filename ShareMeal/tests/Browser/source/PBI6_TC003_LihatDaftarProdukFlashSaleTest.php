<?php

namespace Tests\Browser\source;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class PBI6_TC003_LihatDaftarProdukFlashSaleTest extends DuskTestCase
{
    
    public function test_mitra_dapat_melihat_daftar_produk_flash_sale(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::where('email', 'mitra@example.com')->first();
            
            $browser->loginAs($mitra)
                    ->visit('/mitra/inventory')
                    ->waitForText('Manajemen Inventaris Surplus')
                    
                    ->assertPresent('.grid')
                    ->screenshot('PBI6_TC003_01_DaftarProdukBerhasilDitampilkan');
        });
    }
}
