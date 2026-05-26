<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class KonsumenTambahRestoFavoritTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * TC.Cons.002 - PBI #14
     * Menguji fungsionalitas tambah resto favorit via tombol Kelola
     */
    public function testKonsumenTambahRestoFavorit()
    {
        $this->browse(function (Browser $browser) {
            $kina = User::firstOrCreate(
                ['email' => 'kina@gmail.com'],
                [
                    'name' => 'kina',
                    'password' => bcrypt('password'),
                    'role' => 'consumer',
                    'is_verified' => true
                ]
            );

            $browser->loginAs($kina)
                    ->visit('/consumer')
                    ->assertSee('Toko Favorit')
                    ->press('Kelola')
                    ->pause(500)
                    ->assertSee('Warung Ibu Rina')
                    ->press('Tambah')
                    ->pause(500)
                    ->press('Selesai');
        });
    }
}
