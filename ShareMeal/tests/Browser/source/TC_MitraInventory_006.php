<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC_MitraInventory_006 extends DuskTestCase
{
    /**
     * TC.MitraInventory.006
     * Menguji validasi update produk dengan data kosong
     */
    public function test_mitra_cannot_update_product_with_empty_data(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::where('email', 'mitra@example.com')->first();
            if (!$mitra) {
                $mitra = User::factory()->create(['email' => 'mitra@example.com', 'role' => 'mitra']);
            }
            
            Product::where('user_id', $mitra->id)->delete();

            // Pastikan produk ada
            Product::create([
                'user_id' => $mitra->id,
                'name' => 'Produk Validasi Update',
                'category' => 'Bakery',
                'price' => 20000,
                'stock' => 10,
                'expires_at' => now()->addHours(24),
                'status' => 'normal'
            ]);

            $browser->loginAs($mitra)
                    ->visit('/mitra/inventory')
                    ->assertSee('Produk Validasi Update')
                    ->script("document.querySelector('[\\\\@click^=\"openEditDialog\"]').click();");
            
            $browser->waitForText('Edit Produk')
                    ->pause(500)
                    
                    ->clear('name')
                    ->clear('price')
                    ->clear('stock')
                    ->press('Simpan Produk')
                    ->pause(1000);

            // Validasi HTML5 mencegah form disubmit
            $validationMessage = $browser->script("return document.querySelector('[name=name]').validationMessage;")[0];
            $this->assertNotEmpty($validationMessage);
            
            $browser->assertSee('Edit Produk') // Modal masih terbuka
                    ->screenshot('TC_MitraInventory_006');
        });
    }
}
