<?php

namespace Tests\Browser;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi12MelihatPesananMasukTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * TC-PBI12-001 - Mitra Toko Roti Makmur dapat melihat pesanan Budi Santoso.
     */
    public function test_mitra_toko_roti_makmur_dapat_melihat_pesanan_budi(): void
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
                ->assertSee('Budi Santoso');
        });
    }

    /**
     * TC-PBI12-002 - Mitra lain tidak boleh melihat pesanan milik Toko Roti Makmur.
     */
    public function test_mitra_lain_tidak_melihat_pesanan_mitra_lain(): void
    {
        $this->seedDatabase();

        $mitraLain = User::query()->where('email', 'warmindo@example.com')->firstOrFail();

        $this->browse(function (Browser $browser) use ($mitraLain) {
            $browser->loginAs($mitraLain)
                ->visit('/mitra/orders')
                ->assertDontSee('Susu Kurma Segar');
        });
    }

    private function seedDatabase(): void
    {
        Artisan::call('db:seed', [
            '--class' => DatabaseSeeder::class,
            '--force' => true,
        ]);
    }
}