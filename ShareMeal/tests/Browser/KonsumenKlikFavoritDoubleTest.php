<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class KonsumenKlikFavoritDoubleTest extends DuskTestCase
{
    /**
     * TC.Cons.002 - PBI #14
     * Menguji fungsionalitas tambah resto favorit via tombol Kelola
     */
    public function testKonsumenKlikFavoritDouble()
    {
        $this->browse(function (Browser $browser) {
            $kina = User::where('email', 'kina@gmail.com')->first();

            $browser->loginAs($kina)
                    ->visit('/consumer')
                    ->assertSee('Toko Favorit')
                    ->press('Kelola')
                    ->pause(500)
                    ->assertSee('Warung Ibu Rina')
                    ->press('Tambah')
                    ->pause(500)
                    ->press('Tambah')
                    ->pause(500)
                    ->press('Selesai');
        });
    }
}
