<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC_MitraInventory_004 extends DuskTestCase
{
    /**
     * TC.MitraInventory.004
     * Menguji kondisi daftar produk ketika belum ada data
     */
    public function test_mitra_inventory_empty_state(): void
    {
        $this->browse(function (Browser $browser) {
            $mitraEmpty = User::factory()->create([
                'email' => 'mitra_empty_' . uniqid() . '@example.com',
                'role' => 'mitra',
            ]);

            $browser->loginAs($mitraEmpty)
                    ->visit('/mitra/inventory')
                    ->assertPathIs('/mitra/inventory')
                    ->assertSee('Manajemen Inventaris Surplus')
                    ->pause(1000)
                    ->screenshot('TC_MitraInventory_004');
        });
    }
}
