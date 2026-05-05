<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC_MitraInventory_005 extends DuskTestCase
{
    /**
     * TC.MitraInventory.005
     * Menguji fungsionalitas update informasi produk flash sale
     */
    public function test_mitra_can_update_product(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::where('email', 'mitra@example.com')->first();
            if (!$mitra) {
                $mitra = User::factory()->create(['email' => 'mitra@example.com', 'role' => 'mitra']);
            }
            
            // Hapus produk lama untuk tes ini agar selector lebih mudah
            Product::where('user_id', $mitra->id)->delete();

            // Buat produk untuk diupdate
            $product = Product::create([
                'user_id' => $mitra->id,
                'name' => 'Produk Untuk Diupdate',
                'category' => 'Bakery',
                'price' => 10000,
                'stock' => 5,
                'expires_at' => now()->addHours(24),
                'status' => 'normal'
            ]);

            $browser->loginAs($mitra)
                    ->visit('/mitra/inventory')
                    ->assertSee('Produk Untuk Diupdate')
                    // Klik tombol edit (menggunakan selector attribute Alpine.js)
                    ->script("document.querySelector('[\\\\@click^=\"openEditDialog\"]').click();");
            
            $browser->waitForText('Edit Produk')
                    ->pause(500)
                    ->clear('name')
                    ->type('name', 'Produk Sudah Diupdate')
                    ->clear('price')
                    ->type('price', '15000')
                    ->press('Simpan Produk')
                    ->pause(1500)
                    ->assertSee('Produk Sudah Diupdate')
                    ->assertSee('15.000')
                    ->screenshot('TC_MitraInventory_005');
        });
    }
}
