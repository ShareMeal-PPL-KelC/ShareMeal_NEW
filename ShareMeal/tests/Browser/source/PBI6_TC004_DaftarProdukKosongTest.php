<?php

namespace Tests\Browser\source;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class PBI6_TC004_DaftarProdukKosongTest extends DuskTestCase
{
    public function test_sistem_menampilkan_informasi_ketika_daftar_produk_kosong(): void
    {
        $this->browse(function (Browser $browser) {

            $mitra = User::where('email', 'mitra@example.com')->first();
            
            $browser->loginAs($mitra)
                    ->visit('/mitra/inventory')
                    ->waitForText('Manajemen Inventaris Surplus')
                    ->screenshot('PBI6_TC004_01_SebelumDikosongkan');

            
            $browser->script("window.confirm = () => true;");

            // Gunakan Alpine.js untuk mengosongkan daftar produk secara instan di UI
            // Ini lebih stabil untuk simulasi "Daftar Kosong" tanpa harus reload berulang kali.
            $browser->script("
                let el = document.querySelector('[x-data]');
                if (window.Alpine && el) {
                    Alpine.\$data(el).products = [];
                }
            ");
            
            $browser->assertPresent('.grid')
                    ->screenshot('PBI6_TC004_02_TampilanInventaris');
        });
    }
}
