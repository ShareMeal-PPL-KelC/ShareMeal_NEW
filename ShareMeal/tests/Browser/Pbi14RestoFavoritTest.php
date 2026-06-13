<?php

namespace Tests\Browser;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi14RestoFavoritTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * TC-PBI14-001 - Konsumen dapat melihat toko favorit.
     */
    public function test_konsumen_memilih_toko_favorit(): void
    {
        $this->seed(DatabaseSeeder::class);

        $consumer = User::query()->where('email', 'budi@example.com')->firstOrFail();
           $this->browse(function (Browser $browser) use ($consumer) {
            $browser->loginAs($consumer)
                ->visit('/consumer')
                ->assertSee('Toko Favorit')
                ->press('Kelola')
                ->waitForText('Toko Roti Makmur', 10)
                ->assertSee('Toko Roti Makmur');
        });
    }
}