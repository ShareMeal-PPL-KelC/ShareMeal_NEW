<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC_MitraInventory_003 extends DuskTestCase
{
    /**
     * TC.MitraInventory.003
     * Menguji tampilan daftar produk flash sale
     */
    public function test_mitra_can_view_flash_sale_product_list(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::where('email', 'mitra@example.com')->first();
            if (!$mitra) {
                $mitra = User::factory()->create([
                    'email' => 'mitra@example.com',
                    'role' => 'mitra',
                ]);
            }

            
            $productName = 'Bakso Flash Sale';
            $product = Product::updateOrCreate(
                ['name' => $productName, 'user_id' => $mitra->id],
                [
                    'category' => 'Makanan',
                    'price' => 20000,
                    'discount_price' => 10000,
                    'stock' => 10,
                    'expires_at' => now()->addHours(12),
                    'status' => 'active'
                ]
            );

            // 2. Login menggunakan akun mitra yang sudah terdaftar.
            // 3. Buka halaman Dashboard Mitra atau Inventaris.
            // 4. Periksa daftar produk flash sale yang tersedia.
            $browser->loginAs($mitra)
                    ->visit('/mitra/inventory')
                    ->assertPathIs('/mitra/inventory')
                    ->assertSee('Manajemen Inventaris Surplus')
                    // Memastikan produk yang baru dibuat muncul di daftar
                    ->assertSee($productName)
                    ->assertSee($product->stock)
                    ->screenshot('TC_MitraInventory_003');
        });
    }
}
