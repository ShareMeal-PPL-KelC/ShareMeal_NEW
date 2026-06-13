<?php

namespace Tests\Browser;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi15RiwayatPesananTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * TC-PBI15-001 - Konsumen dapat melihat riwayat pesanan.
     */
    public function test_konsumen_melihat_riwayat_pesanan(): void
    {
        // Seed database
        $this->seed(DatabaseSeeder::class);

        // Ambil user konsumen
        $consumer = User::where('email', 'budi@example.com')->firstOrFail();

        $this->browse(function (Browser $browser) use ($consumer) {

            $browser->loginAs($consumer)
                ->visit('/consumer/history')
                ->pause(5000)
                ->assertPathIs('/consumer/history')
                ->assertSee('Riwayat')
                ->assertSee('Riwayat Pesanan');
        });
    }
public function test_admin_tidak_dapat_mengakses_riwayat_pesanan_konsumen(): void
{
    $this->seed(DatabaseSeeder::class);

    $admin = User::where('email', 'admin@sharemeal.id')->firstOrFail();

    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit('/consumer/history')
            ->assertDontSee('Riwayat Pesanan');
    });
}
}