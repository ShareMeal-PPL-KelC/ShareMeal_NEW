<?php

namespace Tests\Browser;

use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi13WaktuLayakKonsumsiTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * TC-PBI13-001 - Mitra Toko Roti Makmur dapat melihat waktu layak konsumsi produk.
     */
    public function test_mitra_toko_roti_makmur_dapat_melihat_waktu_layak_konsumsi_produk(): void
    {
        $this->seedDatabase();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $consumer = User::query()->where('email', 'budi@example.com')->firstOrFail();
        $mitra = User::query()->where('email', 'mitra@example.com')->firstOrFail();
        $product = Product::query()->where('user_id', $mitra->id)->where('name', 'Susu Kurma Segar')->firstOrFail();

        $this->actingAs($consumer)->post(route('consumer.checkout.store'), [
            'product_id' => $product->id,
            'mitra_id' => $mitra->id,
            'quantity' => 1,
            'price' => $product->price,
            'receiving_method' => 'pickup',
            'payment_method' => 'qris',
        ])->assertRedirect(route('consumer.orders.active'));
        
        $this->browse(function (Browser $browser) use ($mitra) {
            $browser->loginAs($mitra)
                ->visit('/mitra/orders')
                ->waitForText('Susu Kurma Segar', 10)
                ->assertSee('Susu Kurma Segar')
                ->assertSee('Budi Santoso')
                ->press('Konfirmasi Pembayaran dan Proses Pesanan')
                ->pause(2000)
                ->waitForText('Ya, Lanjutkan')
                ->press('Ya, Lanjutkan') 
                ->press('Konfirmasi Pembayaran dan Proses Pesanan')
                ->assertSee('Ya, Lanjutkan')
                ->press('Ya, Lanjutkan')
                ->assertSee('Pesanan Siap Diambil')
                ->press('Pesanan Siap Diambil')
                ->assertSee('Ya, Lanjutkan')            
                ->press('Ya, Lanjutkan');
        });
    }
}