<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class LoginTest extends DuskTestCase
{
    /**
     * TC.Login.001 - PBI #28
     * Menguji fungsionalitas halaman Login - Postive Case
     */
    public function testLoginBerhasil()
    {
        $this->browse(function (Browser $browser) {
            $kya= User::where('email', 'kya@gmail.com')->first();
            $browser->loginAs($kya)
                    ->visit('/consumer');
            });
    }
}